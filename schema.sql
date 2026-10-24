CREATE DATABASE IF NOT EXISTS queueless_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE queueless_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('patient','admin') NOT NULL DEFAULT 'patient',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token INT NOT NULL UNIQUE,
  user_id INT NOT NULL,
  patient_name VARCHAR(100) NOT NULL,
  department_doctor VARCHAR(200) NOT NULL,
  priority ENUM('General','Senior','Emergency') NOT NULL DEFAULT 'General',
  appointment_time TIME NOT NULL,
  notes TEXT,
  gps VARCHAR(100),
  status ENUM('Waiting','In Consultation','Completed','Cancelled') NOT NULL DEFAULT 'Waiting',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  rating TINYINT NOT NULL,
  suggestion TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id),
  CHECK (rating BETWEEN 1 AND 5)
);
