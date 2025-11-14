<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;

    public function __construct() {
        $this->loadEnv();
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnv() {
        $envFile = __DIR__ . '/../.env';
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }

        $this->config = [
            'user' => $_ENV['EMAIL_USER'] ?? '',
            'password' => $_ENV['EMAIL_PASSWORD'] ?? '',
            'from' => $_ENV['EMAIL_FROM'] ?? 'Sendana Team <noreply@sendana.com>'
        ];
    }

    /**
     * Send email using PHPMailer (reliable and fast)
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['user'];
            $mail->Password = $this->config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Disable certificate verification for faster sending
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom($this->config['user'], 'Sendana Team');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody ?: strip_tags($htmlBody);

            $mail->send();
            error_log("Email sent successfully to: $to");
            return true;

        } catch (Exception $e) {
            error_log("Email failed to send: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Send email in background (non-blocking)
     */
    public function sendEmailAsync($to, $subject, $htmlBody, $textBody = '') {
        // Create a background process to send the email
        $emailData = json_encode([
            'to' => $to,
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => $textBody
        ]);

        $emailData = escapeshellarg($emailData);
        $cmd = "php " . __DIR__ . "/../scripts/send_email_async.php $emailData > /dev/null 2>&1 &";
        exec($cmd);

        error_log("Email queued for background sending to: $to");
        return true;
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($email, $firstName = null) {
        $name = $firstName ? $firstName : 'there';
        $subject = "You're in! Let's make money move";
        $htmlBody = $this->getWelcomeEmailTemplate($name);

        // Log the email we're about to send
        error_log("Queuing welcome email to: $email (Name: $name)");

        // Create data file for background process
        $emailData = [
            'to' => $email,
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => strip_tags($htmlBody),
            'name' => $name
        ];

        $tmpFile = '/tmp/sendana_email_' . md5($email . time()) . '.json';
        file_put_contents($tmpFile, json_encode($emailData));

        $scriptPath = __DIR__ . "/../scripts/send_email_async.php";

        // Fire and forget - spawn background process
        $cmd = sprintf(
            'php %s %s > /dev/null 2>&1 &',
            escapeshellarg($scriptPath),
            escapeshellarg($tmpFile)
        );

        // Execute in background without waiting
        exec($cmd);

        error_log("Email queued for: $email");
        return true;
    }

    /**
     * Get welcome email HTML template
     */
    public function getWelcomeEmailTemplate($firstName) {
        return '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f5f5f5;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
        }
        .logo {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        .content {
            padding: 40px 30px;
            color: #333333;
        }
        h1 {
            color: #333333;
            font-size: 24px;
            margin: 0 0 20px 0;
            font-weight: 600;
        }
        p {
            color: #666666;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 20px 0;
        }
        .features {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 25px;
            margin: 30px 0;
        }
        .feature {
            margin-bottom: 15px;
            font-size: 16px;
            color: #333333;
        }
        .feature:last-child {
            margin-bottom: 0;
        }
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
        }
        .footer {
            padding: 30px;
            text-align: center;
            background-color: #f9f9f9;
        }
        .footer p {
            color: #999999;
            font-size: 13px;
            margin: 5px 0;
        }
        .disclaimer {
            font-style: italic;
            color: #999999;
            font-size: 12px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1 class="logo">Sendana</h1>
        </div>

        <div class="content">
            <h1>Hi ' . htmlspecialchars($firstName) . ',</h1>

            <p>Welcome to Sendana, your new home for borderless banking. Think of us as the smarter, faster way to send, receive, and spend money across the globe.</p>

            <p><strong>Here\'s what you can do starting today:</strong></p>

            <div class="features">
                <div class="feature">💸 Get paid from anywhere in the world</div>
                <div class="feature">🌎 Transfer funds to family, friends, or your own accounts</div>
                <div class="feature">🔒 Hold your balance in USDC to protect your earnings from devaluation</div>
            </div>

            <p>No complicated steps. No long paperwork. Just money that moves the way you need it to.</p>

            <p style="text-align: center;">
                <a href="http://localhost:8000/frontend/pages/index.html" class="cta-button">Get Started</a>
            </p>

            <p>Welcome aboard,<br>
            The Sendana Team 💜</p>

            <p class="disclaimer">*Sendana is not a bank. Banking services provided by licensed partners.</p>
        </div>

        <div class="footer">
            <p>&copy; 2025 Sendana. All rights reserved.</p>
            <p>Borderless banking for everyone, everywhere.</p>
        </div>
    </div>
</body>
</html>
        ';
    }
}
