<?php
session_start(); header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); header("Pragma: no-cache");
require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
if(isset($_GET['delete'])){$pdo->prepare("DELETE FROM sectors WHERE sector_id=?")->execute([$_GET['delete']]); header("Location:sectors.php"); exit;}
if($_SERVER['REQUEST_METHOD']=='POST'){
 $pdo->prepare("INSERT INTO sectors (name, description, target_amount) VALUES (?,?,?)")->execute([$_POST['name'], $_POST['desc'], $_POST['target']]);
 header("Location:sectors.php?msg=added"); exit;
}
$sectors=$pdo->query("SELECT * FROM sectors ORDER BY sector_id DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:16px}</style></head><body class="p-4">
<div class="container-fluid"><div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">Sectors</h4><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a></div>
<div class="row"><div class="col-md-4"><div class="card p-4"><h6>Add Sector</h6><form method="POST"><input name="name" placeholder="Sector Name e.g Construction" class="form-control mb-2 rounded-pill" required><textarea name="desc" placeholder="Description" class="form-control mb-2 rounded-4"></textarea><input type="number" name="target" placeholder="Target Amount" class="form-control mb-3 rounded-pill"><button class="btn btn-primary w-100 rounded-pill">Add Sector</button></form></div></div>
<div class="col-md-8"><div class="card p-3"><table class="table"><thead><tr><th>Name</th><th>Target</th><th>Action</th></tr></thead><tbody><?php foreach($sectors as $s):?><tr><td><b><?=$s['name']?></b><br><small><?=$s['description']?></small></td><td>UGX <?=number_format($s['target_amount'])?></td><td><a href="?delete=<?=$s['sector_id']?>" class="btn btn-sm btn-outline-danger rounded-pill">Delete</a></td></tr><?php endforeach;?></tbody></table></div></div></div></div></body></html>