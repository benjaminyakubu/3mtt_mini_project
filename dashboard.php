<?php
require_once 'database.php';
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Handle CRUD operations
if ($_POST && verifyCSRFToken($_POST['csrf_token'])) {
    if (isset($_POST['add_password'])) {
        $website = trim($_POST['website']);
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        $encrypted = encryptPassword($password, $userId);
        $stmt = $pdo->prepare("INSERT INTO encrypted_passwords (user_id, website, username, encrypted_password, iv) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $website, $username, $encrypted['encrypted'], $encrypted['iv']]);
    }
    
    if (isset($_POST['delete_password'])) {
        $stmt = $pdo->prepare("DELETE FROM encrypted_passwords WHERE id = ? AND user_id = ?");
        $stmt->execute([$_POST['password_id'], $userId]);
    }
}

// Fetch user's passwords
$stmt = $pdo->prepare("SELECT * FROM encrypted_passwords WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$passwords = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Secure Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php  'header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-vault"></i> My Passwords</h2>
            <a href="logout.php" class="btn btn-outline-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Add Password Form -->
        <div class="card mb-4 shadow">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Add New Password</h5>
            </div>
            <div class="card-body">
                <form method="POST" id="passwordForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" id="newPassword" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                                    <i class="fas fa-magic"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="add_password" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Passwords List -->
        <div class="card shadow">
            <div class="card-header">
                <h5><i class="fas fa-list"></i> Stored Passwords (<?php echo count($passwords); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($passwords)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-vault fa-3x text-muted mb-3"></i>
                        <h5>No passwords stored yet</h5>
                        <p class="text-muted">Add your first password above!</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Website</th>
                                    <th>Username</th>
                                    <th>Password</th>
                                    <th>Date Added</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($passwords as $password): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-globe"></i>
                                        <?php echo htmlspecialchars($password['website']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($password['username']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="showPassword(<?php echo $password['id']; ?>)">
                                            <i class="fas fa-eye"></i> Show
                                        </button>
                                        <span id="password_<?php echo $password['id']; ?>" class="password-hidden"></span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($password['created_at'])); ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this password?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="password_id" value="<?php echo $password['id']; ?>">
                                            <button type="submit" name="delete_password" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
