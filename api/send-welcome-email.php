<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid email address is required']);
    exit();
}

$email = $input['email'];
$firstName = $input['firstName'] ?? 'User';

$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = getenv('SMTP_PORT') ?: 587;
$smtpUser = getenv('SMTP_USER');
$smtpPassword = getenv('SMTP_PASSWORD');
$fromEmail = getenv('FROM_EMAIL') ?: 'noreply@sendana.com';
$fromName = getenv('FROM_NAME') ?: 'Sendana';

$subject = "You're in! Let's make money move";

$htmlMessage = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #5f2dc4; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #666; }
        .button { display: inline-block; padding: 12px 24px; background-color: #5f2dc4; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        ul { list-style: none; padding: 0; }
        ul li { padding: 8px 0; }
        ul li:before { content: "✓ "; color: #5f2dc4; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to Sendana</h1>
        </div>
        <div class="content">
            <h2>Hi ' . htmlspecialchars($firstName) . ',</h2>
            <p>Welcome to Sendana! We are excited to have you on board.</p>
            <p>Your account has been successfully created, and you now have access to borderless banking at your fingertips.</p>

            <h3>What you can do with Sendana:</h3>
            <ul>
                <li>Get paid from anywhere in the world</li>
                <li>Transfer funds to family, friends, or your own accounts</li>
                <li>Hold your balance in USDC to protect your earnings from devaluation</li>
                <li>Access your money anytime, anywhere</li>
            </ul>

            <p>Ready to get started? Log in to your dashboard and explore all the features Sendana has to offer.</p>

            <div style="text-align: center; margin: 30px 0;">
                <a href="https://sendana.com/dashboard" class="button">Go to Dashboard</a>
            </div>

            <p>If you have any questions, our support team is always here to help.</p>

            <p>Best regards,<br>The Sendana Team</p>
        </div>
        <div class="footer">
            <p>&copy; 2025 Sendana. All rights reserved.</p>
            <p>Borderless banking for everyone.</p>
        </div>
    </div>
</body>
</html>
';

$textMessage = "Hi $firstName,\n\nWelcome to Sendana!\n\nYour account has been successfully created.\n\nWhat you can do:\n- Get paid from anywhere in the world\n- Transfer funds to family, friends, or your own accounts\n- Hold your balance in USDC\n- Access your money anytime, anywhere\n\nBest regards,\nThe Sendana Team";

if (!$smtpUser || !$smtpPassword) {
    error_log("Welcome email would be sent to: $email");
    echo json_encode([
        'success' => true,
        'message' => 'Email logged (SMTP not configured)',
        'email' => $email
    ]);
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $smtpHost;
    $mail->SMTPAuth = true;
    $mail->Username = $smtpUser;
    $mail->Password = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtpPort;

    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($email, $firstName);

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlMessage;
    $mail->AltBody = $textMessage;

    $mail->send();

    echo json_encode([
        'success' => true,
        'message' => 'Welcome email sent successfully',
        'email' => $email
    ]);
} catch (Exception $e) {
    error_log("Email sending failed: " . $mail->ErrorInfo);
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to send email',
        'details' => $mail->ErrorInfo
    ]);
}
?>
