-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ip_management;
USE ip_management;

-- Branches table
CREATE TABLE `branches` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Device types table
CREATE TABLE `device_types` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Subnets table
CREATE TABLE `subnets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `prefix` TINYINT UNSIGNED NOT NULL,
    `subnet_mask` VARCHAR(255) NOT NULL,
    `total_addresses` BIGINT UNSIGNED NOT NULL,
    `usable_hosts` BIGINT UNSIGNED NOT NULL
);

-- IP addresses table
CREATE TABLE `ips` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(255) NOT NULL,
    `subnet_id` BIGINT UNSIGNED NOT NULL,
    `device_name` VARCHAR(255) NOT NULL,
    `device_type_id` BIGINT UNSIGNED NOT NULL,
    `branch_id` BIGINT UNSIGNED NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `ips_subnet_id_foreign` FOREIGN KEY(`subnet_id`) REFERENCES `subnets`(`id`),
    CONSTRAINT `ips_device_type_id_foreign` FOREIGN KEY(`device_type_id`) REFERENCES `device_types`(`id`),
    CONSTRAINT `ips_branch_id_foreign` FOREIGN KEY(`branch_id`) REFERENCES `branches`(`id`)
);

-- Branches data
INSERT INTO branches (name) VALUES
('Alex'),
('Korba');

-- Device types data
INSERT INTO device_types (name) VALUES
('Router'),
('Firewall'),
('Switch'),
('Access Point'),
('Server'),
('NAS (Network Attached Storage)'),
('Virtual Machine'),
('Desktop PC'),
('Laptop'),
('Thin Client'),
('Mobile Phone'),
('Tablet'),
('IoT Device'),
('Printer'),
('IP Camera'),
('Smart TV'),
('VoIP Phone'),
('Game Console'),
('Smart Home Hub'),
('Load Balancer'),
('Proxy Server'),
('VPN Gateway'),
('IDS/IPS (Intrusion Detection/Prevention System)'),
('Wearable Device'),
('Industrial Controller (PLC/SCADA)'),
('Storage Array'),
('Other');

-- Subnets data
INSERT INTO subnets (`prefix`, subnet_mask, total_addresses, usable_hosts) VALUES
(1, '128.0.0.0', 2147483648, 2147483646),
(2, '192.0.0.0', 1073741824, 1073741822),
(3, '224.0.0.0', 536870912, 536870910),
(4, '240.0.0.0', 268435456, 268435454),
(5, '248.0.0.0', 134217728, 134217726),
(6, '252.0.0.0', 67108864, 67108862),
(7, '254.0.0.0', 33554432, 33554430),
(8, '255.0.0.0', 16777216, 16777214),
(9, '255.128.0.0', 8388608, 8388606),
(10, '255.192.0.0', 4194304, 4194302),
(11, '255.224.0.0', 2097152, 2097150),
(12, '255.240.0.0', 1048576, 1048574),
(13, '255.248.0.0', 524288, 524286),
(14, '255.252.0.0', 262144, 262142),
(15, '255.254.0.0', 131072, 131070),
(16, '255.255.0.0', 65536, 65534),
(17, '255.255.128.0', 32768, 32766),
(18, '255.255.192.0', 16384, 16382),
(19, '255.255.224.0', 8192, 8190),
(20, '255.255.240.0', 4096, 4094),
(21, '255.255.248.0', 2048, 2046),
(22, '255.255.252.0', 1024, 1022),
(23, '255.255.254.0', 512, 510),
(24, '255.255.255.0', 256, 254),
(25, '255.255.255.128', 128, 126),
(26, '255.255.255.192', 64, 62),
(27, '255.255.255.224', 32, 30),
(28, '255.255.255.240', 16, 14),
(29, '255.255.255.248', 8, 6),
(30, '255.255.255.252', 4, 2),
(31, '255.255.255.254', 2, 0),
(32, '255.255.255.255', 1, 0);