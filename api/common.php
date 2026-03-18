<?php
declare(strict_types=1);

// Disable error display, enable error logging
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

global $basePath;
$basePath = dirname(__DIR__, 1);

require_once $basePath . '/api/security.php';
require $basePath . '/vendor/autoload.php';
$env = '';
//function determineEnvironment(): void
//{
//    // Determine environment: prefer HTTP host if available, else fallback to hostname/IP
//    $host = $_SERVER['HTTP_HOST'] ?? gethostname() ?? '';
//    $isLocal = str_contains($host, 'local') || str_contains($host, '127.0.0.1');
//    $envFile = '';
//    if($isLocal) {
//        $envFile = $basePath . '/mailer_config_uat.env' ;
//    } else{
//        $envFile = $basePath . '/api/mailer_config.env' ;
//    }
//
//    if(!empty($envFile)) {
//        $dotenv = Dotenv\Dotenv::createImmutable($basePath, basename($envFile));
//        $dotenv->safeLoad();
//    }
//}


//Log errors to destination file
function logError($message, $destination): void
{
    error_log($message, 3, $destination);
}

function exitOnError($errors): void
{
    if ($errors) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'errors' => $errors]);
        exit;
    }
}