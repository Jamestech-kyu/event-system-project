<?php
// =====================================================
// validate_tickets.php - Ticket Quantity Validation
// REQUIREMENT 4: Validate ticket quantities, prevent overselling
// =====================================================

require_once 'db.php';

// -------------------------------------------------------
// validateTicketOrder()
// Core validation function for Requirement 4.
// Checks:
//   (a) quantity > 0
//   (b) quantity <= max_per_order (per-order cap)
//   (c) quantity <= tickets_available (oversell guard)
//
// Returns an associative array:
//   ['valid' => bool, 'errors' => string[], 'line_items' => array]
// -------------------------------------------------------
function validateTicketOrder(array $selections, int $eventId): array {
    $conn   = getDBConnection();
    $errors = [];
    $lineItems = [];

    // Reject entirely empty cart
    $totalQty = array_sum($selections);
    if ($totalQty === 0) {
        $errors[] = 'Please select at least one ticket before proceeding.';
        return ['valid' => false, 'errors' => $errors, 'line_items' => []];
    }

    foreach ($selections as $ticketTypeId => $qty) {
        $qty          = (int) $qty;
        $ticketTypeId = (int) $ticketTypeId;

        if ($qty === 0) continue; // skip unselected types

        // --- (a) Negative / zero quantity check ---
        if ($qty < 0) {
            $errors[] = "Ticket quantity cannot be negative.";
            continue;
        }

        // Fetch live ticket data with a row lock to prevent race conditions
        // (SELECT ... FOR UPDATE inside a transaction prevents overselling
        //  under concurrent requests)
        $stmt = $conn->prepare(
            "SELECT type_name, price, total_capacity, tickets_sold, max_per_order
             FROM ticket_types
             WHERE ticket_type_id = ? AND event_id = ?
             FOR UPDATE"
        );
        $stmt->bind_param('ii', $ticketTypeId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $errors[] = "Ticket type ID $ticketTypeId is invalid or does not belong to this event.";
            $stmt->close();
            continue;
        }

        $ticket    = $result->fetch_assoc();
        $available = $ticket['total_capacity'] - $ticket['tickets_sold'];
        $stmt->close();

        // --- (b) Per-order maximum check ---
        if ($qty > $ticket['max_per_order']) {
            $errors[] = sprintf(
                '"%s" tickets: You requested %d but the maximum per order is %d.',
                $ticket['type_name'], $qty, $ticket['max_per_order']
            );
        }

        // --- (c) Overselling prevention ---
        if ($qty > $available) {
            if ($available === 0) {
                $errors[] = sprintf(
                    '"%s" tickets are SOLD OUT. Please choose a different ticket type.',
                    $ticket['type_name']
                );
            } else {
                $errors[] = sprintf(
                    '"%s" tickets: Only %d ticket(s) remaining but you requested %d.',
                    $ticket['type_name'], $available, $qty
                );
            }
        }

        // Build line item even if there are errors (for UI feedback)
        $lineItems[] = [
            'ticket_type_id' => $ticketTypeId,
            'type_name'      => $ticket['type_name'],
            'qty'            => $qty,
            'unit_price'     => $ticket['price'],
            'subtotal'       => $ticket['price'] * $qty,
            'available'      => $available,
            'max_per_order'  => $ticket['max_per_order'],
        ];
    }

    $conn->close();

    return [
        'valid'      => empty($errors),
        'errors'     => $errors,
        'line_items' => $lineItems,
    ];
}

// -------------------------------------------------------
// validateAttendeeInfo()
// Validates personal details submitted on the booking form.
// Returns ['valid' => bool, 'errors' => string[], 'data' => array]
// -------------------------------------------------------
function validateAttendeeInfo(array $post): array {
    $errors = [];
    $data   = [];

    // Full name
    $name = trim($post['full_name'] ?? '');
    if ($name === '') {
        $errors[] = 'Full name is required.';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Full name must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/', $name)) {
        $errors[] = 'Full name may only contain letters, spaces, hyphens, and apostrophes.';
    } else {
        $data['full_name'] = $name;
    }

    // Email
    $email = trim($post['email'] ?? '');
    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $data['email'] = $email;
    }

    // Phone (Kenyan format, optional but validated if provided)
    $phone = trim($post['phone'] ?? '');
    if ($phone !== '') {
        if (!preg_match('/^(\+254|0)[17]\d{8}$/', $phone)) {
            $errors[] = 'Phone number must be a valid Kenyan number (e.g. 0712345678).';
        } else {
            $data['phone'] = $phone;
        }
    } else {
        $data['phone'] = null;
    }

    // Payment method
    $allowed = ['mpesa', 'card', 'cash'];
    $method  = $post['payment_method'] ?? '';
    if (!in_array($method, $allowed, true)) {
        $errors[] = 'Please select a valid payment method.';
    } else {
        $data['payment_method'] = $method;
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'data'   => $data,
    ];
}

// -------------------------------------------------------
// reserveTickets()
// Atomically reserves tickets inside a transaction.
// Uses SELECT ... FOR UPDATE to prevent race conditions.
// Returns ['success' => bool, 'registration_id' => int|null, 'error' => string]
// -------------------------------------------------------
function reserveTickets(int $eventId, array $lineItems, array $attendeeData, string $paymentMethod): array {
    $conn = getDBConnection();
    $conn->begin_transaction();

    try {
        // 1. Re-validate availability inside the transaction (double-check)
        foreach ($lineItems as $item) {
            $stmt = $conn->prepare(
                "SELECT total_capacity, tickets_sold FROM ticket_types
                 WHERE ticket_type_id = ? FOR UPDATE"
            );
            $stmt->bind_param('i', $item['ticket_type_id']);
            $stmt->execute();
            $row       = $stmt->get_result()->fetch_assoc();
            $available = $row['total_capacity'] - $row['tickets_sold'];
            $stmt->close();

            if ($item['qty'] > $available) {
                throw new RuntimeException(
                    "Sorry, ticket availability changed for \"{$item['type_name']}\". " .
                    "Only $available left. Please adjust your order."
                );
            }
        }

        // 2. Insert or find attendee
        $stmt = $conn->prepare(
            "SELECT attendee_id FROM attendees WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $attendeeData['email']);
        $stmt->execute();
        $existingAttendee = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existingAttendee) {
            $attendeeId = $existingAttendee['attendee_id'];
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO attendees (full_name, email, phone) VALUES (?, ?, ?)"
            );
            $stmt->bind_param('sss',
                $attendeeData['full_name'],
                $attendeeData['email'],
                $attendeeData['phone']
            );
            $stmt->execute();
            $attendeeId = $conn->insert_id;
            $stmt->close();
        }

        // 3. Calculate total
        $total = array_sum(array_column($lineItems, 'subtotal'));

        // 4. Create registration record
        $stmt = $conn->prepare(
            "INSERT INTO registrations (attendee_id, event_id, total_amount, status)
             VALUES (?, ?, ?, 'confirmed')"
        );
        $stmt->bind_param('iid', $attendeeId, $eventId, $total);
        $stmt->execute();
        $registrationId = $conn->insert_id;
        $stmt->close();

        // 5. Insert line items and decrement tickets_sold
        foreach ($lineItems as $item) {
            // Insert item
            $stmt = $conn->prepare(
                "INSERT INTO registration_items (registration_id, ticket_type_id, quantity, unit_price)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param('iiid',
                $registrationId,
                $item['ticket_type_id'],
                $item['qty'],
                $item['unit_price']
            );
            $stmt->execute();
            $stmt->close();

            // Decrement available count
            $stmt = $conn->prepare(
                "UPDATE ticket_types SET tickets_sold = tickets_sold + ?
                 WHERE ticket_type_id = ?"
            );
            $stmt->bind_param('ii', $item['qty'], $item['ticket_type_id']);
            $stmt->execute();
            $stmt->close();
        }

        // 6. Simulate payment record
        $txRef = strtoupper(substr($paymentMethod, 0, 3)) . date('YmdHis') . rand(10, 99);
        $stmt  = $conn->prepare(
            "INSERT INTO payments (registration_id, amount, payment_method, transaction_ref, status)
             VALUES (?, ?, ?, ?, 'completed')"
        );
        $stmt->bind_param('idss', $registrationId, $total, $paymentMethod, $txRef);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $conn->close();

        return ['success' => true, 'registration_id' => $registrationId, 'error' => ''];

    } catch (RuntimeException $e) {
        $conn->rollback();
        $conn->close();
        return ['success' => false, 'registration_id' => null, 'error' => $e->getMessage()];
    }
}
