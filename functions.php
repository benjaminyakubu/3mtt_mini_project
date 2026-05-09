<?php
require_once 'database.php';

// Generate CSRF token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// PBKDF2 password hashing
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// AES-256-CBC Encryption
function encryptPassword($password, $userId) {
    $key = hash('sha256', $userId . 'secret_salt_3mtt', true);
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($password, 'AES-256-CBC', $key, 0, $iv);
    return [
        'encrypted' => base64_encode($encrypted),
        'iv' => bin2hex($iv)
    ];
}

// AES-256-CBC Decryption
function decryptPassword($encrypted, $iv, $userId) {
    $key = hash('sha256', $userId . 'secret_salt_3mtt', true);
    $encrypted = base64_decode($encrypted);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, hex2bin($iv));
}
?>
