-- Create database
CREATE DATABASE IF NOT EXISTS event_system;
USE event_system;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    location VARCHAR(100) NOT NULL
);

-- Ticket types table
CREATE TABLE IF NOT EXISTS ticket_types (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id)
);

-- Attendees table
CREATE TABLE IF NOT EXISTS attendees (
    attendee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20)
);

-- Registrations table
CREATE TABLE IF NOT EXISTS registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    attendee_id INT NOT NULL,
    event_id INT NOT NULL,
    ticket_id INT NOT NULL,
    quantity INT NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (attendee_id) REFERENCES attendees(attendee_id),
    FOREIGN KEY (event_id) REFERENCES events(event_id),
    FOREIGN KEY (ticket_id) REFERENCES ticket_types(ticket_id)
);

-- Payments table (simulated)
CREATE TABLE IF NOT EXISTS payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status ENUM('Pending','Completed','Failed') DEFAULT 'Pending',
    FOREIGN KEY (registration_id) REFERENCES registrations(registration_id)
);

-- Sample Events
INSERT INTO events (name, description, date, location) VALUES
('Tech Conference 2026', 'A conference on emerging technologies.', '2026-04-15', 'Nairobi'),
('Music Festival', 'Outdoor live music performances.', '2026-05-10', 'Mombasa'),
('Startup Pitch Day', 'Entrepreneurs pitch their ideas.', '2026-06-01', 'Kisumu'),
('Healthcare Summit', 'Discussions on healthcare innovations.', '2026-07-20', 'Nakuru'),
('AI Workshop', 'Hands-on workshop on AI tools.', '2026-08-05', 'Nairobi');

-- Sample Ticket Types
INSERT INTO ticket_types (event_id, type, price, quantity) VALUES
(1, 'Standard', 50.00, 100),
(1, 'VIP', 150.00, 50),
(2, 'General Admission', 30.00, 200),
(2, 'Backstage Pass', 100.00, 20),
(3, 'Pitch Participant', 20.00, 30),
(3, 'Audience', 10.00, 100),
(4, 'Delegate', 75.00, 80),
(4, 'Student', 25.00, 50),
(5, 'Workshop Seat', 40.00, 60),
(5, 'Premium Seat', 80.00, 30);

-- Sample Attendees
INSERT INTO attendees (name, email, phone) VALUES
('James Mwangi', 'james@example.com', '0712345678'),
('Mary Atieno', 'mary@example.com', '0723456789'),
('John Kamau', 'john@example.com', '0734567890'),
('Alice Wanjiru', 'alice@example.com', '0745678901'),
('Peter Otieno', 'peter@example.com', '0756789012');

-- Sample Registrations
INSERT INTO registrations (attendee_id, event_id, ticket_id, quantity) VALUES
(1, 1, 1, 2),
(2, 2, 3, 1),
(3, 3, 5, 1),
(4, 4, 7, 2),
(5, 5, 9, 1);

-- Sample Payments
INSERT INTO payments (registration_id, amount, status) VALUES
(1, 100.00, 'Completed'),
(2, 30.00, 'Completed'),
(3, 20.00, 'Completed'),
(4, 150.00, 'Pending'),
(5, 40.00, 'Completed');
