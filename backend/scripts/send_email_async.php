<?php
/**
 * Background Email Sender Script using PHPMailer
 * Reads email data from JSON file and sends via Gmail SMTP
 */

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get JSON file path from command line
if ($argc < 2) {
    exit(1);
}

$jsonFile = $argv[1];

// Read email data from JSON file
if (!file_exists($jsonFile)) {
    exit(1);
}

$emailData = json_decode(file_get_contents($jsonFile), true);

if (!$emailData || !isset($emailData['to']) || !isset($emailData['subject'])) {
    exit(1);
}

// Load .env configuration
$envFile = __DIR__ . '/../.env';
$config = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $config[trim($key)] = trim($value);
    }
}

$to = $emailData['to'];
$subject = $emailData['subject'];
$htmlBody = $emailData['html'];
$textBody = $emailData['text'] ?? strip_tags($htmlBody);

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $config['EMAIL_USER'] ?? '';
    $mail->Password = $config['EMAIL_PASSWORD'] ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Disable SSL verification
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom($config['EMAIL_USER'] ?? 'noreply@sendana.com', 'Sendana Team');
    $mail->addAddress($to);

    // Content
    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $htmlBody;
    $mail->AltBody = $textBody;

    $mail->send();
    error_log("✅ Welcome email sent to: $to");

    // Clean up temp file
    if (file_exists($jsonFile)) {
        unlink($jsonFile);
    }
    exit(0);

} catch (Exception $e) {
    error_log("❌ Email failed to $to: {$mail->ErrorInfo}");

    // Clean up temp file even on failure
    if (file_exists($jsonFile)) {
        unlink($jsonFile);
    }
    exit(1);
}
