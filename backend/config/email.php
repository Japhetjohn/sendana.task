<?php
// Email Configuration for Gmail SMTP

class EmailConfig {
    // Gmail SMTP Settings
    const SMTP_HOST = 'smtp.gmail.com';
    const SMTP_PORT = 587;
    const SMTP_SECURE = 'tls'; // or 'ssl' for port 465

    // Gmail Credentials
    const SMTP_USERNAME = 'japhetjohnk@gmail.com';
    const SMTP_PASSWORD = 'bciz mpkv pjpr mfps'; // App Password (spaces will be removed)

    // Email From Details
    const FROM_EMAIL = 'japhetjohnk@gmail.com';
    const FROM_NAME = 'Sendana Team';

    // Email Settings
    const CHARSET = 'UTF-8';

    public static function getConfig() {
        return [
            'host' => self::SMTP_HOST,
            'port' => self::SMTP_PORT,
            'secure' => self::SMTP_SECURE,
            'username' => self::SMTP_USERNAME,
            'password' => str_replace(' ', '', self::SMTP_PASSWORD), // Remove spaces
            'from_email' => self::FROM_EMAIL,
            'from_name' => self::FROM_NAME,
            'charset' => self::CHARSET
        ];
    }
}
