<?php
session_start(); require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
if(isset($_GET['delete'])){$pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]); header("Location:users.php"); exit;}
if($_SERVER['REQUEST_METHOD']=='POST'){
 $hash=password_hash($_POST['password'], PASSWORD_DEFAULT);
 $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)")->execute([$_POST['username'], $_POST['email'], $hash]);
 header("Location:users.php?msg=added"); exit;
}
$users=$pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:16px}</style></head><body class="p-4">
<div class="container-fluid"><div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">User Management - Only Approved Board Can Access</h4><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a></div>
<div class="row"><div class="col-md-4"><div class="card p-4"><h6>Add Board User</h6><form method="POST"><input name="username" placeholder="Username" class="form-control mb-2 rounded-pill" required><input name="email" placeholder="Email" class="form-control mb-2 rounded-pill"><input type="password" name="password" placeholder="Password" class="form-control mb-3 rounded-pill" required><button class="btn btn-primary w-100 rounded-pill">Create User</button></form></div></div>
<div class="col-md-8"><div class="card p-3"><table class="table"><thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Action</th></tr></thead><tbody><?php foreach($users as $us):?><tr><td><?=$us['id']?></td><td><?=$us['username']?></td><td><?=$us['email']?></td><td><?php if($us['id']!=$_SESSION['user_id']):?><a href="?delete=<?=$us['id']?>" onclick="return confirm('Delete user?')" class="btn btn-sm btn-outline-danger rounded-pill">Delete</a><?php else:?><span class="badge bg-success">You</span><?php endif;?></td></tr><?php endforeach;?></tbody></table></div></div></div></div></body></html>