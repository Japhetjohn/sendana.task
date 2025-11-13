<?php
/**
 * Test Email Sending
 * Run this to verify email configuration works
 */

require_once __DIR__ . '/backend/services/EmailService.php';

echo "Testing Sendana Welcome Email...\n\n";

// Get email from command line or use default
$testEmail = $argv[1] ?? 'japhetjohnk@gmail.com';
$testName = $argv[2] ?? 'Japhet';

echo "Sending test welcome email to: $testEmail\n";
echo "Using name: $testName\n\n";

$emailService = new EmailService();
$result = $emailService->sendWelcomeEmail($testEmail, $testName);

if ($result) {
    echo "✅ Email queued successfully!\n";
    echo "Check your inbox at: $testEmail\n";
    echo "Note: It may take a few seconds to arrive.\n";
} else {
    echo "❌ Failed to queue email.\n";
    echo "Check the error logs for details.\n";
}

echo "\n";
