<?php
require_once __DIR__ . '/../config/email.php';

class EmailService {
    private $config;

    public function __construct() {
        $this->config = EmailConfig::getConfig();
    }

    /**
     * Send email using PHP mail() with Gmail SMTP
     * This is fast and non-blocking
     */
    public function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        try {
            // If no text body provided, strip HTML from html body
            if (empty($textBody)) {
                $textBody = strip_tags($htmlBody);
            }

            // Boundary for multipart email
            $boundary = md5(time());

            // Headers
            $headers = "From: " . $this->config['from_name'] . " <" . $this->config['from_email'] . ">\r\n";
            $headers .= "Reply-To: " . $this->config['from_email'] . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();

            // Message body
            $message = "--$boundary\r\n";
            $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $textBody . "\r\n";
            $message .= "--$boundary\r\n";
            $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
            $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
            $message .= $htmlBody . "\r\n";
            $message .= "--$boundary--";

            // Additional parameters
            $params = "-f" . $this->config['from_email'];

            // Send email using PHP's mail() function
            // This is fast and doesn't block
            $result = @mail($to, $subject, $message, $headers, $params);

            if ($result) {
                error_log("Email queued successfully to: $to");
            } else {
                error_log("Failed to queue email to: $to");
            }

            return $result;

        } catch (Exception $e) {
            error_log("Email error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email in background (non-blocking)
     * This ensures signup is fast
     */
    public function sendEmailAsync($to, $subject, $htmlBody, $textBody = '') {
        // Create a background process to send the email
        $emailData = json_encode([
            'to' => $to,
            'subject' => $subject,
            'html' => $htmlBody,
            'text' => $textBody
        ]);

        // Escape for shell
        $emailData = escapeshellarg($emailData);

        // Send in background using exec
        $cmd = "php " . __DIR__ . "/../scripts/send_email.php $emailData > /dev/null 2>&1 &";
        exec($cmd);

        error_log("Email queued for background sending to: $to");
        return true;
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($email, $firstName = null) {
        $name = $firstName ? $firstName : 'there';

        $subject = "You're in! Let's make money move 🚀";

        $htmlBody = $this->getWelcomeEmailTemplate($name);

        // Send async so it doesn't block signup
        return $this->sendEmailAsync($email, $subject, $htmlBody);
    }

    /**
     * Get welcome email HTML template
     */
    private function getWelcomeEmailTemplate($firstName) {
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
