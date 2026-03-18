<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$basePath = dirname(__DIR__, 1);
require_once __DIR__ . '/../vendor/autoload.php';

// Determine environment: prefer HTTP host if available, else fallback to hostname/IP
$host = $_SERVER['HTTP_HOST'] ?? gethostname() ?? '';
$isLocal = str_contains($host,'local') || $host === '127.0.0.1';

$envFile = $isLocal ? $basePath . '/mailer_config_uat.env' : $basePath . '/mailer_config.env';

// Load the chosen .env file
$dotenv = Dotenv\Dotenv::createImmutable($basePath, basename($envFile));
$dotenv->safeLoad();

$mail = new PHPMailer(true); // Enable exceptions

// SMTP Configuration
$mail->isSMTP();
$mail->Host = $_ENV['HOST']; // Your SMTP server
$mail->SMTPAuth = true;
$mail->Username = $_SERVER[$_ENV['USERNAME']];
$mail->Password = $_SERVER[$_ENV['PASSWORD']];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = $_ENV['PORT'];
$mail->SMTPDebug = $_ENV['SMTP_DEBUG'];

// Sender and recipient settings
$from = $_ENV['SET_FROM'];
$from = json_decode($from);
$mail->setFrom($from[0], $from[1]);
$cc = $_ENV['CC_ADDRESSES'];
$cc = json_decode($cc);
//$mail->addReplyTo();
foreach ($cc as $c) {
    $mail->addCC($c);
}

// Sending plain text email
$mail->isHTML(false); // Set email format to plain text
$mail->Subject = 'Just Testing phpmailer';
$mail->Body    = 'This is a test.';

// Send the email
if(!$mail->send()){
    echo 'Message could not be sent. Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}

//URL: http://localhost:1313/api/test.php?Full+Name=&Email=&Address=&Zip+Code=&Phone=832-832-8322&attachments%5B%5D=
