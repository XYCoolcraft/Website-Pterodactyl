CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(255) NOT NULL UNIQUE,
  `license_key` VARCHAR(255) NOT NULL UNIQUE,
  `expiry_date` DATE NOT NULL,
  `is_admin` BOOLEAN NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Membuat user admin "unlimited" dengan tanggal kedaluwarsa yang sangat jauh
INSERT INTO `users` (`username`, `license_key`, `expiry_date`, `is_admin`) VALUES
('admin', 'XYZAGEN123', '9999-12-31', 1);