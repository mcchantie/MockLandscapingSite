<?php
/**
 * Security layer for LiteSpeed/Apache hosting
 */
$basePath = dirname(__DIR__, 1);

require_once $basePath . '/vendor/autoload.php';

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header_remove('X-Powered-By');

// Rate limiting using sessions
session_start();
$ip = $_SERVER['REMOTE_ADDR'];
$time_window = 60; // seconds
$max_requests = 5;

if (!isset($_SESSION['rate_limit'][$ip])) {
    $_SESSION['rate_limit'][$ip] = [];
}

// Clean old requests
$_SESSION['rate_limit'][$ip] = array_filter(
    $_SESSION['rate_limit'][$ip],
    fn($t) => $t > (time() - $time_window)
);

// Check limit
if (count($_SESSION['rate_limit'][$ip]) >= $max_requests) {
    http_response_code(429);
    header('Content-Type: application/json');
    die(json_encode(['error' => 'Too many requests. Please wait.']));
}

$_SESSION['rate_limit'][$ip][] = time();

// Input sanitization helper
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim(stripslashes($data)), ENT_QUOTES, 'UTF-8');
}

// Validate email
function validateEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}

// Honeypot check
function checkHoneypot($field = 'website') {
    if (!empty($_POST[$field])) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid submission']));
    }
}

// Require POST method
function requirePOST() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        die(json_encode(['error' => 'Only POST requests allowed']));
    }
}
