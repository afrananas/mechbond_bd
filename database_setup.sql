-- Database setup for Car Workshop Appointment System
-- DROP DATABASE IF EXISTS workshop_appointments;
CREATE DATABASE workshop_appointments;
USE workshop_appointments;

-- Mechanics table
CREATE TABLE mechanics (
    mechanic_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    max_appointments INT DEFAULT 4
);

-- Clients table
CREATE TABLE clients (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    car_license VARCHAR(50) NOT NULL,
    car_engine VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    appointment_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    mechanic_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    status ENUM('pending', 'confirmed', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(client_id) ON DELETE CASCADE,
    FOREIGN KEY (mechanic_id) REFERENCES mechanics(mechanic_id) ON DELETE CASCADE
);

-- Admin users table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample mechanics
INSERT INTO mechanics (name, max_appointments) VALUES
('John Smith', 4),
('Sarah Johnson', 4),
('Mike Williams', 4),
('Emily Brown', 4),
('David Lee', 4);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Create indexes for better performance
CREATE INDEX idx_appointment_date ON appointments(appointment_date);
CREATE INDEX idx_mechanic_date ON appointments(mechanic_id, appointment_date);
CREATE INDEX idx_client_phone ON clients(phone);