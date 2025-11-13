<?php
/**
 * Background Email Sender Script
 * This script is called in the background to send emails without blocking signup
 */

require_once __DIR__ . '/../config/email.php';

// Get email data from command line argument
if ($argc < 2) {
    error_log("Email sender: No email data provided");
    exit(1);
}

$emailData = json_decode($argv[1], true);

if (!$emailData || !isset($emailData['to']) || !isset($emailData['subject'])) {
    error_log("Email sender: Invalid email data");
    exit(1);
}

$to = $emailData['to'];
$subject = $emailData['subject'];
$htmlBody = $emailData['html'];
$textBody = $emailData['text'] ?? strip_tags($htmlBody);

try {
    $config = EmailConfig::getConfig();

    // Create SMTP connection
    $smtp = stream_socket_client(
        "tls://{$config['host']}:{$config['port']}",
        $errno,
        $errstr,
        30,
        STREAM_CLIENT_CONNECT,
        stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ])
    );

    if (!$smtp) {
        error_log("SMTP connection failed: $errstr ($errno)");
        exit(1);
    }

    // Read greeting
    fgets($smtp, 515);

    // Send EHLO
    fputs($smtp, "EHLO {$config['host']}\r\n");
    fgets($smtp, 515);

    // AUTH LOGIN
    fputs($smtp, "AUTH LOGIN\r\n");
    fgets($smtp, 515);

    // Username
    fputs($smtp, base64_encode($config['username']) . "\r\n");
    fgets($smtp, 515);

    // Password
    fputs($smtp, base64_encode($config['password']) . "\r\n");
    $authResponse = fgets($smtp, 515);

    if (strpos($authResponse, '235') === false) {
        error_log("SMTP AUTH failed");
        fclose($smtp);
        exit(1);
    }

    // MAIL FROM
    fputs($smtp, "MAIL FROM: <{$config['from_email']}>\r\n");
    fgets($smtp, 515);

    // RCPT TO
    fputs($smtp, "RCPT TO: <$to>\r\n");
    fgets($smtp, 515);

    // DATA
    fputs($smtp, "DATA\r\n");
    fgets($smtp, 515);

    // Boundary
    $boundary = md5(time());

    // Message
    $message = "From: {$config['from_name']} <{$config['from_email']}>\r\n";
    $message .= "To: $to\r\n";
    $message .= "Subject: $subject\r\n";
    $message .= "MIME-Version: 1.0\r\n";
    $message .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=\"UTF-8\"\r\n\r\n";
    $message .= "$textBody\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=\"UTF-8\"\r\n\r\n";
    $message .= "$htmlBody\r\n";
    $message .= "--$boundary--\r\n";
    $message .= ".\r\n";

    fputs($smtp, $message);
    $sendResponse = fgets($smtp, 515);

    // QUIT
    fputs($smtp, "QUIT\r\n");
    fclose($smtp);

    if (strpos($sendResponse, '250') !== false) {
        error_log("Email sent successfully to: $to");
        exit(0);
    } else {
        error_log("Email send failed: $sendResponse");
        exit(1);
    }

} catch (Exception $e) {
    error_log("Email sender error: " . $e->getMessage());
    exit(1);
}
