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
//   (b) quantity does not exceed available stock
//       available = ticket_types.quantity - SUM of registrations for that ticket
//
// Returns: ['valid' => bool, 'errors' => string[], 'ticket' => array|null]
// -------------------------------------------------------
function validateTicketOrder(int $ticketId, int $qty): array {
    $conn   = getDBConnection();
    $errors = [];

    // --- (a) Zero or negative quantity check ---
    if ($qty <= 0) {
        $errors[] = 'Please select at least 1 ticket.';
        return ['valid' => false, 'errors' => $errors, 'ticket' => null];
    }

    // Fetch ticket type details
    // SELECT ... FOR UPDATE locks the row to prevent concurrent overselling
    $stmt = $conn->prepare(
        "SELECT tt.ticket_id, tt.type, tt.price, tt.quantity, tt.event_id,
                COALESCE(SUM(r.quantity), 0) AS tickets_sold
         FROM ticket_types tt
         LEFT JOIN registrations r ON r.ticket_id = tt.ticket_id
         WHERE tt.ticket_id = ?
         GROUP BY tt.ticket_id
         FOR UPDATE"
    );
    $stmt->bind_param('i', $ticketId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $errors[] = 'Invalid ticket type selected.';
        $stmt->close();
        $conn->close();
        return ['valid' => false, 'errors' => $errors, 'ticket' => null];
    }

    $ticket    = $result->fetch_assoc();
    $available = $ticket['quantity'] - $ticket['tickets_sold'];
    $stmt->close();
    $conn->close();

    // --- (b) Overselling prevention ---
    if ($available <= 0) {
        $errors[] = '"' . $ticket['type'] . '" tickets are SOLD OUT.';
    } elseif ($qty > $available) {
        $errors[] = '"' . $ticket['type'] . '" tickets: Only ' . $available .
                    ' ticket(s) remaining but you requested ' . $qty . '.';
    }

    $ticket['available'] = $available;

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'ticket' => $ticket,
    ];
}

// -------------------------------------------------------
// validateAttendeeInfo()
// Validates personal details submitted on the booking form.
// Returns: ['valid' => bool, 'errors' => string[], 'data' => array]
// -------------------------------------------------------
function validateAttendeeInfo(array $post): array {
    $errors = [];
    $data   = [];

    // Full name
    $name = trim($post['name'] ?? '');
    if ($name === '') {
        $errors[] = 'Full name is required.';
    } elseif (strlen($name) < 3) {
        $errors[] = 'Full name must be at least 3 characters.';
    } elseif (!preg_match('/^[a-zA-Z\s\'\-]+$/', $name)) {
        $errors[] = 'Full name may only contain letters, spaces, hyphens, and apostrophes.';
    } else {
        $data['name'] = $name;
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

    // Phone (optional but validated if provided)
    $phone = trim($post['phone'] ?? '');
    if ($phone !== '') {
        if (!preg_match('/^(\+254|0)[17]\d{8}$/', $phone)) {
            $errors[] = 'Phone must be a valid Kenyan number (e.g. 0712345678).';
        } else {
            $data['phone'] = $phone;
        }
    } else {
        $data['phone'] = null;
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
        'data'   => $data,
    ];
}

// -------------------------------------------------------
// reserveTickets()
// Atomically saves registration + payment inside a transaction.
// Uses SELECT ... FOR UPDATE to prevent concurrent overselling.
// Returns: ['success' => bool, 'registration_id' => int|null, 'error' => string]
// -------------------------------------------------------
function reserveTickets(int $eventId, int $ticketId, int $qty, float $unitPrice, array $attendeeData): array {
    $conn = getDBConnection();
    $conn->begin_transaction();

    try {
        // Re-check availability inside transaction (guards against race conditions)
        $stmt = $conn->prepare(
            "SELECT tt.quantity,
                    COALESCE(SUM(r.quantity), 0) AS tickets_sold
             FROM ticket_types tt
             LEFT JOIN registrations r ON r.ticket_id = tt.ticket_id
             WHERE tt.ticket_id = ?
             GROUP BY tt.ticket_id
             FOR UPDATE"
        );
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $row       = $stmt->get_result()->fetch_assoc();
        $available = $row['quantity'] - $row['tickets_sold'];
        $stmt->close();

        if ($qty > $available) {
            throw new RuntimeException(
                'Sorry, only ' . $available . ' ticket(s) left. Please adjust your order.'
            );
        }

        // Insert or find existing attendee by email
        $stmt = $conn->prepare(
            "SELECT attendee_id FROM attendees WHERE email = ? LIMIT 1"
        );
        $stmt->bind_param('s', $attendeeData['email']);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $attendeeId = $existing['attendee_id'];
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO attendees (name, email, phone) VALUES (?, ?, ?)"
            );
            $stmt->bind_param('sss',
                $attendeeData['name'],
                $attendeeData['email'],
                $attendeeData['phone']
            );
            $stmt->execute();
            $attendeeId = $conn->insert_id;
            $stmt->close();
        }

        // Create registration record
        $stmt = $conn->prepare(
            "INSERT INTO registrations (attendee_id, event_id, ticket_id, quantity)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param('iiii', $attendeeId, $eventId, $ticketId, $qty);
        $stmt->execute();
        $registrationId = $conn->insert_id;
        $stmt->close();

        // Simulate payment record
        $total = $unitPrice * $qty;
        $stmt  = $conn->prepare(
            "INSERT INTO payments (registration_id, amount, status) VALUES (?, ?, 'Completed')"
        );
        $stmt->bind_param('id', $registrationId, $total);
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
