<?php
/**
 * Send welcome emails to all existing users in the database
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../services/EmailService.php';

echo "Starting bulk welcome email send...\n\n";

// Initialize services
$db = new Database();
$emailService = new EmailService();

try {
    // Query all users from the database
    $cursor = $db->executeQuery('users', []);
    $users = $cursor->toArray();

    $totalUsers = count($users);
    echo "Found $totalUsers users in database\n\n";

    if ($totalUsers === 0) {
        echo "No users found in database.\n";
        exit(0);
    }

    $emailsSent = 0;
    $emailsFailed = 0;

    foreach ($users as $user) {
        $user = (array) $user;
        $email = $user['email'] ?? null;

        if (!$email) {
            echo "⚠️  Skipping user with no email\n";
            continue;
        }

        // Extract first name from profile or email
        $firstName = null;
        if (isset($user['profile'])) {
            $profile = is_object($user['profile']) ? (array) $user['profile'] : $user['profile'];
            $name = $profile['name'] ?? null;
            if ($name) {
                $firstName = explode(' ', $name)[0];
            }
        }

        if (!$firstName) {
            $firstName = explode('@', $email)[0];
        }

        $firstName = ucfirst($firstName);

        echo "📧 Sending welcome email to: $email (Name: $firstName)... ";

        try {
            // Send welcome email directly (not async for this bulk send)
            $subject = "You're in! Let's make money move";
            $htmlBody = $emailService->getWelcomeEmailTemplate($firstName);

            $result = $emailService->sendEmail($email, $subject, $htmlBody);

            if ($result) {
                $emailsSent++;
                echo "✅ Sent\n";
            } else {
                $emailsFailed++;
                echo "❌ Failed\n";
            }

            // Small delay to avoid rate limiting
            sleep(1);

        } catch (Exception $e) {
            $emailsFailed++;
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }

    echo "\n";
    echo "==========================================\n";
    echo "Bulk email send complete!\n";
    echo "Total users: $totalUsers\n";
    echo "Emails sent: $emailsSent ✅\n";
    echo "Emails failed: $emailsFailed ❌\n";
    echo "==========================================\n";

} catch (Exception $e) {
    echo "❌ Error querying database: " . $e->getMessage() . "\n";
    exit(1);
}
