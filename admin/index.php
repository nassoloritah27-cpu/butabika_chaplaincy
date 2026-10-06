<?php
session_start();
require_once __DIR__.'/../config/database.php';
if(empty($_SESSION['logged_in'])){ header("Location:../login.php"); exit; }

function getCount($pdo, $table){
  try{ return (int)$pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn(); }
  catch(Exception $e){ return 0; }
}
function getSum($pdo, $table, $col){
  try{ return $pdo->query("SELECT COALESCE(SUM($col),0) FROM $table")->fetchColumn(); }
  catch(Exception $e){ return 0; }
}

$board_count = getCount($pdo, 'board_members');
$sectors_count = getCount($pdo, 'sectors');
$announcements_count = getCount($pdo, 'announcements');
$contributors_count = getCount($pdo, 'contributors');
$pledges_count = getCount($pdo, 'pledges');
$payments_count = getCount($pdo, 'payments');
$verified_total = getSum($pdo, 'payments', 'amount');

try{
  $recent = $pdo->query("SELECT p.*, c.full_name as cname FROM payments p LEFT JOIN contributors c ON p.contributor_id=c.contributor_id ORDER BY p.payment_id DESC LIMIT 5")->fetchAll();
}catch(Exception $e){ $recent = []; }
?>
<!DOCTYPE html>
<html><head>
<meta charset="UTF-8">
<title>Dashboard - Butabika Chaplaincy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#eef2ff;margin:0;font-family:Inter,Segoe UI,sans-serif;}
.sidebar{width:250px;background:linear-gradient(180deg,#2c3ed6,#4a7de8);min-height:100vh;position:fixed;padding:20px 12px;}
.sidebar .brand{color:white;font-weight:900;font-size:22px;}
.sidebar .sm{color:#aab9ff;font-size:12px;line-height:1.4;}
.sidebar .lbl{color:#7f94ff;font-size:10px;font-weight:700;margin:22px 0 6px 8px;letter-spacing:1px;}
.sidebar a{display:flex;align-items:center;justify-content:space-between;color:#d0d9ff;text-decoration:none;padding:11px 14px;border-radius:12px;border:1px solid rgba(255,255,255,.25);margin-bottom:7px;font-size:14px;}
.sidebar a.active,.sidebar a:hover{background:white;color:#2c3ed6;font-weight:600;}
.sidebar a .b{background:white;color:#2c3ed6;border-radius:50px;min-width:26px;text-align:center;font-weight:800;font-size:12px;padding:2px 8px;}
.main{margin-left:250px;padding:28px;}
.cardx{border:none;border-radius:20px;padding:20px;box-shadow:0 8px 24px rgba(0,0,0,.06);background:white;}
.blue{background:#1d6bff;color:white;}
.dark{background:#1a243a;color:white;}
</style>
</head><body>
<div class="sidebar">
<div class="px-2"><div class="brand">❤️ BUTABIKA</div><div class="sm fw-bold">Chaplaincy Admin</div><div class="sm">Secure Board Management</div><div class="sm mt-1" style="font-size:11px">Only approved board members can access this.</div></div>
<div class="lbl">MAIN</div>
<a href="index.php" class="active"><span><i class="bi bi-bar-chart-line-fill me-2"></i>Dashboard</span></a>
<a href="profile.php"><span><i class="bi bi-person-fill me-2"></i>My Profile</span></a>
<div class="lbl">BOARD</div>
<a href="board-members.php"><span><i class="bi bi-people-fill me-2"></i>Board Members</span><span class="b"><?= $board_count ?></span></a>
<a href="sectors.php"><span><i class="bi bi-grid-fill me-2"></i>Sectors</span><span class="b"><?= $sectors_count ?></span></a>
<a href="announcements.php"><span><i class="bi bi-megaphone-fill me-2"></i>Announcements</span><span class="b"><?= $announcements_count ?></span></a>
<div class="lbl">FINANCE</div>
<a href="contributors.php"><span><i class="bi bi-heart-fill me-2"></i>Contributors</span><span class="b"><?= $contributors_count ?></span></a>
<a href="pledges.php"><span><i class="bi bi-piggy-bank-fill me-2"></i>Pledges</span><span class="b"><?= $pledges_count ?></span></a>
<a href="payments.php"><span><i class="bi bi-wallet-fill me-2"></i>Payments</span><span class="b" style="background:#ffbe0b;"><?= $payments_count ?></span></a>
<a href="messages.php"><span><i class="bi bi-envelope me-2"></i>Thank-You Messages</span></a>
</div>

<div class="main">
<h4 class="fw-bold mb-0">Welcome, admin 👋</h4>
<small class="text-muted">Full Control Panel</small>

<div class="row g-3 mt-3">
<div class="col-md-4"><div class="cardx"><small class="text-muted fw-bold" style="font-size:11px">VERIFIED</small><h4 class="fw-bold mb-0">UGX <?= number_format($verified_total) ?></h4></div></div>
<div class="col-md-4"><div class="cardx blue"><small style="font-size:11px;opacity:.9">BOARD MEMBERS</small><h3 class="fw-bold mb-0"><?= $board_count ?></h3><small style="font-size:12px;opacity:.9">Active Members</small></div></div>
<div class="col-md-4"><div class="cardx dark"><small style="font-size:11px;opacity:.7">CONTRIBUTORS</small><h3 class="fw-bold mb-0"><?= $contributors_count ?></h3><small style="font-size:12px;opacity:.7">Total Registered</small></div></div>
</div>

<div class="cardx mt-4">
<h6 class="fw-bold">Recent Payments</h6>
<table class="table table-sm mt-3"><thead><tr><th class="text-muted" style="font-size:13px">Contributor</th><th class="text-muted" style="font-size:13px">Amount</th></tr></thead>
<tbody>
<?php if(empty($recent)): ?><tr><td colspan="2" class="text-center text-muted py-4">No payments yet</td></tr>
<?php else: foreach($recent as $r): ?>
<tr><td><?= htmlspecialchars($r['cname']??'Unknown') ?></td><td class="fw-bold">UGX <?= number_format($r['amount']??0) ?></td></tr>
<?php endforeach; endif; ?>
</tbody></table>
</div>
</div>
</body></html>