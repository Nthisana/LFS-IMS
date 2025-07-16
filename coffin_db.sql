CREATE DATABASE coffin_db;

USE coffin_db;

CREATE TABLE coffins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  region VARCHAR(50),
  branch VARCHAR(50),
  coffin_type VARCHAR(100),
  code VARCHAR(100) UNIQUE,
  storage VARCHAR(50),
  arrival_date DATE,
  status ENUM('In-stock', 'Sold', 'Transfer', 'Write-off', 'Damage') DEFAULT 'In-stock',
  transfer_location VARCHAR(100),
  previous_location VARCHAR(100),
  action_date DATE,
  in_store_duration INT,
  action_duration INT
);
