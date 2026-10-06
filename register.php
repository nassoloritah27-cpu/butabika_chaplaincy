<?php
session_start();
require_once __DIR__ . '/config/database.php';
$msg = "";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if($password !== $confirm){
        $msg = "Passwords do not match";
    } elseif(strlen($password) < 6){
        $msg = "Password must be at least 6 characters";
    } else {
        try {
            // Check if exists
            $check = $pdo->prepare("SELECT user_id FROM users WHERE username=? OR email=? LIMIT 1");
            $check->execute([$username, $email]);
            if($check->fetch()){
                $msg = "Username or Email already exists";
            } else {
                // Insert matching your exact table: user_id, username, email, password_hash, role, status
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role, status) VALUES (?, ?, ?, 'super_admin', 'approved')");
                $stmt->execute([$username, $email, $password]); // Using plain for now as your table has plain, you can later use password_hash()
                $msg = "SUCCESS! Admin account created. You can now login.";
            }
        } catch(Exception $e){
            $msg = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Create Admin - Butabika Chaplaincy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head><body class="bg-light d-flex justify-content-center align-items-center" style="min-height:100vh">
<div class="card p-4 shadow rounded-4" style="max-width:450px;width:100%">
<h4 class="fw-bold text-center">Create Admin Account</h4>
<p class="small text-muted text-center">Butabika Catholic Chaplaincy System</p>
<?php if($msg): ?>
<div class="alert <?= str_contains($msg,'SUCCESS') ? 'alert-success' : 'alert-danger' ?> small"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>
<form method="POST">
<div class="mb-2"><label class="small fw-bold">Full Name</label><input name="full_name" class="form-control" placeholder="John Doe"></div>
<div class="mb-2"><label class="small fw-bold">Username *</label><input name="username" class="form-control" required placeholder="admin"></div>
<div class="mb-2"><label class="small fw-bold">Email *</label><input name="email" type="email" class="form-control" required placeholder="admin@butabika.com"></div>
<div class="mb-2"><label class="small fw-bold">Phone</label><input name="phone" class="form-control" placeholder="0782743394"></div>
<div class="mb-2"><label class="small fw-bold">Password *</label><input name="password" type="password" class="form-control" required></div>
<div class="mb-3"><label class="small fw-bold">Confirm Password *</label><input name="confirm" type="password" class="form-control" required></div>
<button class="btn btn-primary w-100 rounded-pill">Create Account</button>
</form>
<div class="text-center mt-3 small">
Already have account? <a href="login.php">Login here</a> | <a href="index.php">Home</a>
</div>
<!--<div class="small mt-3 p-2 bg-light rounded">
<b>Official Accounts:</b><br>
MTN: 0782743394 / 51657673<br>
Airtel: 0701743394 / 7050461<br>
Bank: 3266540694
</div>-->
</div></body></html>