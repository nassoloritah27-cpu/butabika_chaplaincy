<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once __DIR__.'/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
$donors=$pdo->query("SELECT c.*, COUNT(p.payment_id) as payments, COALESCE(SUM(p.amount),0) as total FROM contributors c LEFT JOIN payments p ON c.contributor_id=p.contributor_id GROUP BY c.contributor_id ORDER BY total DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:20px}</style></head><body class="p-4"><div class="container-fluid"><div class="d-flex justify-content-between mb-4"><h3 class="fw-bold"><i class="bi bi-people"></i> Contributors</h3><a href="index.php" class="btn btn-dark rounded-pill">Back</a></div><div class="card p-4"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Payments</th><th>Total Given</th></tr></thead><tbody><?php foreach($donors as $d):?><tr><td class="fw-bold"><?=htmlspecialchars($d['full_name'])?></td><td><?=htmlspecialchars($d['email']??'')?></td><td><?=htmlspecialchars($d['phone']??'')?></td><td><span class="badge bg-primary"><?=$d['payments']?></span></td><td class="fw-bold text-success">UGX <?=number_format($d['total'])?></td></tr><?php endforeach;?></tbody></table></div></div></div></body></html>