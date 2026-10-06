<?php
session_start();
require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}

$user_id = $_SESSION['user_id'] ?? 1;
// Try both id and user_id - will work with ANY table
try{
  $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();
} catch(Exception $e){
  $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$user_id]);
  $user = $stmt->fetch();
}

if($_SERVER['REQUEST_METHOD']=='POST'){
  $full_name = $_POST['full_name'];
  $email = $_POST['email'];
  $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];
  
  try{
    $pdo->prepare("UPDATE users SET full_name=?, email=?, password=? WHERE user_id=?")->execute([$full_name, $email, $password, $user_id]);
  } catch(Exception $e){
    $pdo->prepare("UPDATE users SET full_name=?, email=?, password=? WHERE id=?")->execute([$full_name, $email, $password, $user_id]);
  }
  header("Location:my-profile.php?msg=updated");
  exit;
}
?>
<!DOCTYPE html>
<html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#f4f7fb}.card{border:none;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.05)}</style>
</head><body class="p-4">
<div class="container" style="max-width:600px">
<div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">My Profile</h4><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a></div>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-success rounded-pill">Profile Updated!</div><?php endif; ?>
<div class="card p-4">
<form method="POST">
<label class="fw-bold">Full Name</label>
<input type="text" name="full_name" value="<?=htmlspecialchars($user['full_name'] ?? $user['name'] ?? '')?>" class="form-control mb-3 rounded-pill" required>
<label class="fw-bold">Email / Username</label>
<input type="text" name="email" value="<?=htmlspecialchars($user['email'] ?? $user['username'] ?? '')?>" class="form-control mb-3 rounded-pill" required>
<label class="fw-bold">New Password (leave blank to keep old)</label>
<input type="password" name="password" class="form-control mb-4 rounded-pill" placeholder="••••••">
<button class="btn btn-primary w-100 rounded-pill py-2 fw-bold">Update Profile</button>
</form>
</div>
</div>
</body></html>