<?php
session_start(); require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
$logs=$pdo->query("SELECT * FROM audit_logs ORDER BY log_id DESC LIMIT 200")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:16px}</style></head><body class="p-4">
<div class="container-fluid"><div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">Audit Logs</h4><div><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a> <a href="?clear=1" onclick="return confirm('Clear logs?')" class="btn btn-outline-danger rounded-pill">Clear</a></div></div>
<?php if(isset($_GET['clear'])){$pdo->query("TRUNCATE audit_logs"); header("Location:audit-logs.php"); exit;}?>
<div class="card p-3"><table class="table table-sm"><thead><tr><th>Time</th><th>User</th><th>Action</th><th>Details</th><th>IP</th></tr></thead><tbody><?php foreach($logs as $l):?><tr><td><small><?=$l['created_at']?></small></td><td><?=$l['username']?></td><td><span class="badge bg-dark"><?=$l['action']?></span></td><td><?=$l['details']?></td><td><small><?=$l['ip_address']?></small></td></tr><?php endforeach;?></tbody></table></div></div></body></html>