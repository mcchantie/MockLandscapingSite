<?php
declare(strict_types=1);

// Disable error display, enable error logging
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

$basePath = dirname(__DIR__, 1);

require_once $basePath . '/api/security.php';
require_once $basePath . '/api/common.php';
require_once $basePath . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require POST method
requirePOST();

// Set JSON response
header('Content-Type: application/json');

if(empty($_POST)){
    $errors['attachments'] = 'Form data not sent correctly. Please reduce the size of your attachments and try again.';
    exitOnError($errors);
}

// Check honeypot
checkHoneypot();

// Determine environment: prefer HTTP host if available, else fallback to hostname/IP
$host = $_SERVER['HTTP_HOST'] ?? gethostname() ?? '';
$isLocal = str_contains($host, 'local') || str_contains($host, '127.0.0.1');
$envFile = '';
if($isLocal) {
    $envFile = $basePath . '/mailer_config_uat.env' ;
}

if(!empty($envFile)) {
    $dotenv = Dotenv\Dotenv::createImmutable($basePath, basename($envFile));
    $dotenv->safeLoad();
}

try {
    // Get environment variables (from .htaccess SetEnv)
    $smtp_host = getenv('SMTP_HOST') ?: $_SERVER['SMTP_HOST'] ?? '';
    $smtp_port = getenv('SMTP_PORT') ?: $_SERVER['SMTP_PORT'] ?? 587;
    $smtp_user = getenv('TX_TOP_DRESSING_USERNAME') ?: $_SERVER['TX_TOP_DRESSING_USERNAME'] ?? '';
    $smtp_pass = getenv('TX_TOP_DRESSING_PASSWORD') ?: $_SERVER['TX_TOP_DRESSING_PASSWORD'] ?? '';
    $smtp_from = getenv('SMTP_FROM') ?: $_SERVER['SMTP_FROM'] ?? '["no-reply@txtopdressing.com","Texas Top Dressing and Lawn Leveling"]';
    $smtp_cc = getenv('SMTP_CC_ADDRESSES') ?: $_SERVER['SMTP_CC_ADDRESSES'] ?? '["no-reply@txtopdressing.com"]';
    $smtp_debug = (int)(getenv('SMTP_DEBUG') ?: $_SERVER['SMTP_DEBUG'] ?? 0);

    // Validate and sanitize inputs
    $errors = [];

    $first_name = sanitize($_POST['first_name'] ?? '');
    $last_name = sanitize($_POST['last_name'] ?? '');
    $email = validateEmail($_POST['email'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $zip_code = sanitize($_POST['zip_code'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $message = sanitize($_POST['message'] ?? '');
    $contact_preference = sanitize($_POST['contact_preference'] ?? '');
    $attachments = $_POST['attachments'] ?? [];
    $quote = isset($_POST['quote']) && trim($_POST['quote']) == "on";
    $services = $_POST['services'] ?? [];

    /* Always mandatory: First name, last name, and contact preference (email or phone).
    * Phone & Email: If the user prefers to be contacted by phone then the phone input is mandatory. Same for email.
    * Address, Zip Code, and Service: If a user wants a quote (quote needs to be an input) then the address and zip code are mandatory.
    * The user also must select at least one service item.
    * */
    //Always mandatory
    if (empty($first_name)) $errors[] = 'first_name';
    if (empty($last_name)) $errors[] = 'last_name';
    if (empty($contact_preference)) $errors[] = 'contact_preference';
    if (empty($message)) $errors[] = 'message';
    //Phone & Email
    $contact_preference = strtolower($contact_preference);
    if (str_contains($contact_preference, 'phone')) {
        if (empty($phone)) $errors[] = 'phone';
    } else {
        if (empty($email)) $errors[] = 'email';
    }
    //Address, Zip Code, and Service
    if ($quote) {
        if (empty($address)) $errors[] = 'address';
        if (empty($zip_code)) $errors[] = 'zip_code';
        if (empty($services)) $errors[] = 'services';
    }

    exitOnError($errors);

    $mail = new PHPMailer(true); // Enable exceptions

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host = $smtp_host;
    $mail->SMTPAuth = true;
    $mail->Username = $smtp_user;
    $mail->Password = $smtp_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $smtp_port;
    $mail->SMTPDebug = $smtp_debug;

    // Redirect SMTP debug to error log instead of output
    if (isset($_ENV['SMTP_DEBUG']) && $_ENV['SMTP_DEBUG'] > 0) {
        $mail->SMTPDebug = $_ENV['SMTP_DEBUG'];
        $mail->Debugoutput = function ($str, $level) {
            error_log("SMTP Debug [$level]: $str");
        };
    } else {
        $mail->SMTPDebug = 0;
    }

    // Sender and recipients
    $from_data = json_decode($smtp_from, true);
    $mail->setFrom($from_data[0], $from_data[1]);

    $cc_addresses = json_decode($smtp_cc, true);
    foreach ($cc_addresses as $cc) {
        $mail->addAddress($cc);
    }

    if($email) {
        $mail->addReplyTo($email, "$first_name $last_name");
    }

    $mail->isHTML(true); //Remove plain text restriction

    if ($quote) {
        $mail->Subject = $first_name . ' ' . $last_name . ' | Quote Request';
    } else {
        $mail->Subject = $first_name . ' ' . $last_name . ' | Inquiry';
    }

    $mail->Body = "
    <html>
    <head><style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #4CAF50; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #555; }
        </style></head>
    <body>
    <div class='container'>
        <div class='header'><h2>New Contact Form Submission</h2></div>
        <div class='content'>
            <div class='field'><span class='label'>Name: </span>" . htmlspecialchars("$first_name $last_name") . "</div>";

    if (!empty($email)) {
        $mail->Body .= "<div class='field'><span class='label'>Email: </span><a href='mailto:$email'>$email</a></div>";
    }

    if (!empty($phone)) {
        $formatted_phone = preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $phone);
        $mail->Body .= "<div class='field'><span class='label'>Phone: </span><a href='tel:$phone'>$formatted_phone</a></div>";
    }

    if (!empty($contact_preference)) {
        $mail->Body .= "<div class='field'><span class='label'>Preferred Contact: </span>" . ucfirst($contact_preference) . "</div>";
    }

    if (!empty($address)) {
        $mail->Body .= "<div class='field'><span class='label'>Address: </span>$address</div>";
    }

    if (!empty($zip_code)) {
        $mail->Body .= "<div class='field'><span class='label'>ZIP: </span>$zip_code</div>";
    }

    if (!empty($services)) {
        $mail->Body .= "<div class='field'><div class='label'>Services Requested: </div>" . implode(', ', $services) . "</div>";
    }

    if (!empty($message)) {
        $mail->Body .= "<div class='field'><div class='label'>Message:</div>" . nl2br($message) . "</div>";
    }

    $mail->Body .= "
        </div>
    </div>
    </body>
    </html>";

    //Optional
    if (!empty($_FILES['attachments']['name'][0])) {
        $count = count($_FILES['attachments']['name']);
        $maxFileSize = 10 * 1024 * 1024; // 10MB
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'mp4', 'mov', 'avi'];
        $maxUploadSize = 20 * 1024 * 1024; // 20MB

        for ($i = 0; $i < $count; $i++) {
            $name = $_FILES['attachments']['name'][$i];
            $tmp = $_FILES['attachments']['tmp_name'][$i];
            $err = $_FILES['attachments']['error'][$i];
            $size = $_FILES['attachments']['size'][$i];

            if ($err === UPLOAD_ERR_OK) {
                // Validate the temporary file actually exists and is uploaded
                if (!is_uploaded_file($tmp)) {
                    $errors['attachments'] = 'Invalid file upload.';
                    exitOnError($errors);
                }

                // Validate file size
                if ($size > $maxFileSize) {
                    $errors['attachments'] = $name . ' exceeds 10MB limit. Please choose a smaller file.';
                    exitOnError($errors);
                }

                $maxUploadSize -= $size;
                if($maxUploadSize < 0) {
                    $errors['attachments'] = 'Total attachments exceed 20MB. Please remove some files or use smaller ones.';
                    exitOnError($errors);
                }

                // Sanitize and validate filename extension
                $pathInfo = pathinfo($name);
                $extension = strtolower($pathInfo['extension'] ?? '');

                if (!in_array($extension, $allowedExtensions)) {
                    $errors['attachments'] = 'File type not allowed. Allowed extensions: ' . implode(', ', $allowedExtensions) . '.';
                    exitOnError($errors);
                }

                // Sanitize filename to prevent path traversal attacks
                $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $pathInfo['filename']);
                $safeName = $safeName . '.' . $extension;

                // Add attachment with sanitized name
                $mail->addAttachment($tmp, $safeName);
            }
        }
    }

    //Check for errors with attachments
    exitOnError($errors);

    // Plain text alternative
    $mail->AltBody = "Name: $first_name $last_name\nEmail : $email\n" .
        (!empty($phone) ? "Phone: $phone\n" : "") .
        (!empty($contact_preference) ? "Preferred Contact: $contact_preference\n" : "") .
        (!empty($address) ? "Address: $address\n" : "") .
        (!empty($zip_code) ? "ZIP: $zip_code\n" : "") .
        (!empty($services) ? "Services: " . implode(', ', $services) . "\n" : "") .
        (!empty($message) ? "Message: $message\n" : "");

    // Send the email
    if (!$mail->send()) {
        error_log('PHPMailer send failed: ' . $mail->ErrorInfo);
        http_response_code(500);
        echo json_encode(['ok' => false, 'errors' => ['email' => 'Failed to send email. Please try again.']]);
        exit;
    }

    // Success
    echo json_encode(['ok' => true, 'message' => 'Email sent successfully']);
    exit;

} catch (Exception $e) {
    // Log detailed error for debugging
    error_log('PHPMailer Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ' | Line: ' . $e->getLine());

    // Send generic error response
    http_response_code(500);
    echo json_encode(['ok' => false, 'errors' => ['system' => 'An unexpected error occurred. Please try again later.']]);
    exit;
}