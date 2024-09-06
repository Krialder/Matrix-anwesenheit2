CREATE DATABASE IF NOT EXISTS kde_test2;

USE kde_test2;

-- categorization Table
CREATE TABLE IF NOT EXISTS categorization 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE
);

-- Users Table
CREATE TABLE IF NOT EXISTS users 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfid VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    classification_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (classification_id) REFERENCES categorization(id)
);

-- Logs Table
CREATE TABLE IF NOT EXISTS logs 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfid VARCHAR(255) NOT NULL,
    login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    logout_time DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rfid) REFERENCES users(rfid)
);

-- login_attempts Table
CREATE TABLE IF NOT EXISTS login_attempts 
(
    id INT AUTO_INCREMENT PRIMARY KEY,
    rfid VARCHAR(255) NOT NULL,
    attempts INT DEFAULT 5,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    block_until TIMESTAMP NULL DEFAULT NULL,
    block_count INT DEFAULT 0,
    FOREIGN KEY (rfid) REFERENCES users(rfid)
);

-- Indexes for performance improvement
CREATE INDEX idx_users_rfid ON users(rfid);
CREATE INDEX idx_logs_rfid ON logs(rfid);

-- Insert sample data into categorization
INSERT INTO categorization (name) VALUES
('BVB'),
('Auszubildende'),
('Ausbilder'),
('Admin');

-- Insert sample data into users
INSERT INTO users (rfid, name, classification_id) VALUES
('1234abcd', 'John Doe', (SELECT id FROM categorization WHERE name = 'BVB')),
('5678efgh', 'Jane Doe', (SELECT id FROM categorization WHERE name = 'Auszubildende')),
('9101ijkl', 'Max Mustermann', (SELECT id FROM categorization WHERE name = 'Ausbilder')),
('1213mnop', 'Erika Mustermann', (SELECT id FROM categorization WHERE name = 'Admin'));