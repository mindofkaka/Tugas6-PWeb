CREATE DATABASE IF NOT EXISTS todo_app;
USE todo_app;

CREATE TABLE IF NOT EXISTS todos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('personal', 'shared') NOT NULL DEFAULT 'personal',
    status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending',
    attachment VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO todos (title, description, type, status) VALUES
('Study SQL Database', 'Learn syntax query and others', 'personal', 'completed'),
('KPPL Project Team', 'Discuss about Miro project with teamgroup', 'shared', 'pending'),
('Building PHP Web', 'Migrate HTML-based web to PHP-based', 'personal', 'pending');


