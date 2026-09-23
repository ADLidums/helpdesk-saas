CREATE DATABASE IF NOT EXISTS helpdesk_testing
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'helpdesk_test'@'%'
    IDENTIFIED BY 'testing_secret';

GRANT ALL PRIVILEGES ON `helpdesk_testing`.*
    TO 'helpdesk_test'@'%';