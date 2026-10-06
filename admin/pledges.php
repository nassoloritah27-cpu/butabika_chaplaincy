<?php
session_start();
require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
if(isset($_GET['fulfill'])){ $pdo->prepare("UPDATE pledges SET status='fulfilled' WHERE pledge_id=?")->execute([$_GET['fulfill']]); header("Location:pledges.php"); exit; }
if(isset($_GET['delete'])){ $pdo->prepare("DELETE FROM pledges WHERE pledge_id=?")->execute([$_GET['delete']]); header("Location:pledges.php"); exit; }
if($_SERVER['REQUEST_METHOD']=='POST'){
 $pdo->prepare("INSERT INTO pledges (contributor_id, sector_id, amount, pledge_date, deadline, status, notes) VALUES (?,?,?,?,?,?,?)")
 ->execute([$_POST['contributor_id'], $_POST['sector_id'], $_POST['amount'], $_POST['pledge_date'], $_POST['deadline'], 'pending', $_POST['notes']]);
 header("Location:pledges.php"); exit;
}
$pledges=$pdo->query("SELECT p.*, c.full_name FROM pledges p LEFT JOIN contributors c ON p.contributor_id=c.contributor_id ORDER BY p.pledge_id DESC")->fetchAll();
$contributors=$pdo->query("SELECT * FROM contributors")->fetchAll();
$sectors=$pdo->query("SELECT * FROM sectors")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:16px}</style></head><body class="p-4">
<div class="container-fluid"><div class="d-flex justify-content-between mb-3"><h4>Pledges</h4><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a></div>
<div class="row"><div class="col-md-4"><div class="card p-4"><form method="POST">
<select name="contributor_id" class="form-control mb-2 rounded-pill" required><option value="">Select Contributor</option><?php foreach($contributors as $c):?><option value="<?=$c['contributor_id']?>"><?=$c['full_name']?></option><?php endforeach;?></select>
<select name="sector_id" class="form-control mb-2 rounded-pill"><option value="">Sector</option><?php foreach($sectors as $s):?><option value="<?=$s['sector_id']?>"><?=htmlspecialchars($s['name'] ?? $s['sector_name'] ?? 'Sector')?></option><?php endforeach;?></select>
<input type="number" name="amount" placeholder="Amount" class="form-control mb-2 rounded-pill" required>
<input type="date" name="pledge_date" class="form-control mb-2 rounded-pill" value="<?=date('Y-m-d')?>"><input type="date" name="deadline" class="form-control mb-2 rounded-pill">
<textarea name="notes" class="form-control mb-3"></textarea><button class="btn btn-primary w-100 rounded-pill">Add Pledge</button></form></div></div>
<div class="col-md-8"><div class="card p-3"><table class="table"><tr><th>Contributor</th><th>Amount</th><th>Status</th><th>Action</th></tr>
<?php foreach($pledges as $pl): $amt = $pl['amount'] ?? 0; ?>
<tr><td><?=htmlspecialchars($pl['full_name'] ?? '')?></td><td>UGX <?=number_format($amt)?></td><td><?=$pl['status']?></td><td><a href="?fulfill=<?=$pl['pledge_id']?>" class="btn btn-sm btn-success rounded-pill">Fulfill</a> <a href="?delete=<?=$pl['pledge_id']?>" class="btn btn-sm btn-danger rounded-pill">X</a></td></tr>
<?php endforeach;?></table></div></div></div></div></body></html>