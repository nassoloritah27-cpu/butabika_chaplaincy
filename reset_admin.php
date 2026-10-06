<?php
require_once __DIR__ . '/config/database.php';
$newPass = 'admin123';
$hash = password_hash($newPass, PASSWORD_DEFAULT);

// Reset the only admin
$pdo->query("DELETE FROM users WHERE user_id > 1"); // keep only first if many
$check = $pdo->query("SELECT * FROM users LIMIT 1")->fetch();

if($check){
  $pdo->prepare("UPDATE users SET password_hash=?, status='approved' WHERE user_id=?")->execute([$hash, $check['user_id']]);
  echo "Password reset for user: <b>{$check['username']}</b> to <b>admin123</b><br>Now delete reset_admin.php and go to login.php";
} else {
  $pdo->prepare("INSERT INTO users (username,email,password_hash,role,status) VALUES (?,?,?,?,?)")
      ->execute(['admin','admin@butabika.com',$hash,'super_admin','approved']);
  echo "New admin created<br>Username: <b>admin</b><br>Password: <b>admin123</b><br>Now delete reset_admin.php and login";
}
?>