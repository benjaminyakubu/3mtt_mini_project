<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_POST['action'] === 'decrypt') {
    $passwordId = (int)$_POST['password_id'];
    $masterPass = $_POST['master_pass'];
    
    $stmt = $pdo->prepare("SELECT * FROM encrypted_passwords WHERE id = ? AND user_id = ?");
    $stmt->execute([$passwordId, $_SESSION['user_id']]);
    $password = $stmt->fetch();
    
    if ($password) {
        // In production, you'd verify master password against stored hash
        // For demo, we'll decrypt directly
        $decrypted = decryptPassword($password['encrypted_password'], $password['iv'], $_SESSION['user_id']);
        echo json_encode(['success' => true, 'password' => $decrypted]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>
