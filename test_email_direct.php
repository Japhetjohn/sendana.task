<?php
/**
 * Direct Email Test - Sends immediately (not async) for testing
 */

require_once __DIR__ . '/backend/services/EmailService.php';

echo "Testing Direct Email Send with PHPMailer...\n\n";

$email = $argv[1] ?? 'japhetjohnk@gmail.com';
$name = $argv[2] ?? 'Japhet';

echo "Sending email to: $email\n";
echo "Name: $name\n\n";

$emailService = new EmailService();

// Call sendEmail directly (not async) to see immediate results
$subject = "You're in! Let's make money move 🚀";
$htmlBody = '
<html>
<body style="font-family: Arial, sans-serif;">
    <h2>Hi ' . htmlspecialchars($name) . ',</h2>
    <p>Welcome to Sendana! This is a test email.</p>
    <p>If you received this, the email system is working perfectly! ✅</p>
</body>
</html>
';

echo "Attempting to send email...\n";

$result = $emailService->sendEmail($email, $subject, $htmlBody);

if ($result) {
    echo "\n✅ SUCCESS! Email sent to $email\n";
    echo "Check your inbox!\n";
} else {
    echo "\n❌ FAILED to send email\n";
    echo "Check the error above for details\n";
}
