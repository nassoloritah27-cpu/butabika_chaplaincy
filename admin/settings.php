<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['logged_in'])) { header("Location: ../login.php"); exit; }
$msg=""; $err="";
$user = $pdo->prepare("SELECT * FROM users WHERE user_id=?"); $user->execute([$_SESSION['user_id']]); $u=$user->fetch();
if($_SERVER['REQUEST_METHOD']=='POST'){
  if(isset($_POST['change_pass'])){
    $curr=$_POST['current']; $new=$_POST['new']; $confirm=$_POST['confirm'];
    if(!password_verify($curr,$u['password_hash'])){ $err="Current password is wrong!"; }
    elseif($new!==$confirm){ $err="New passwords don't match!"; }
    elseif(strlen($new)<6){ $err="New password must be 6+ characters"; }
    else { $hash=password_hash($new,PASSWORD_DEFAULT); $pdo->prepare("UPDATE users SET password_hash=? WHERE user_id=?")->execute([$hash,$_SESSION['user_id']]); $msg="Password changed! ✅"; }
  }
  if(isset($_POST['update_profile'])){
    $username=$_POST['username']; $email=$_POST['email'];
    $pdo->prepare("UPDATE users SET username=?, email=? WHERE user_id=?")->execute([$username,$email,$_SESSION['user_id']]);
    $_SESSION['username']=$username; $msg="Profile updated!"; $user->execute([$_SESSION['user_id']]); $u=$user->fetch();
  }
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:20px;box-shadow:0 10px 30px rgba(0,0,0,.05)}</style></head><body class="p-4"><div class="container" style="max-width:700px">
<a href="index.php" class="btn btn-outline-dark rounded-pill mb-4"><i class="bi bi-arrow-left"></i> Back</a>
<?php if($msg):?><div class="alert alert-success rounded-4"><?=$msg?></div><?php endif;?><?php if($err):?><div class="alert alert-danger rounded-4"><?=$err?></div><?php endif;?>
<div class="card p-4 mb-4"><h5 class="fw-bold">Admin Profile</h5><form method="POST"><div class="row"><div class="col-md-6 mb-3"><label>Username</label><input name="username" value="<?=htmlspecialchars($u['username'])?>" class="form-control rounded-pill" required></div><div class="col-md-6 mb-3"><label>Email</label><input name="email" type="email" value="<?=htmlspecialchars($u['email'])?>" class="form-control rounded-pill" required></div></div><button name="update_profile" class="btn btn-dark rounded-pill px-4">Update Profile</button></form></div>
<div class="card p-4"><h5 class="fw-bold"><i class="bi bi-key"></i> Change Password</h5><form method="POST" class="mt-3"><label>Current</label><input type="password" name="current" class="form-control rounded-pill mb-3" required><label>New Password</label><input type="password" name="new" class="form-control rounded-pill mb-3" required><label>Confirm</label><input type="password" name="confirm" class="form-control rounded-pill mb-3" required><button name="change_pass" class="btn btn-primary w-100 rounded-pill">Update Password</button></form></div>
</div></body></html>