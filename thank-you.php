<?php
require_once __DIR__.'/config/database.php';
$id = $_GET['id'] ?? 0;
$pay = $pdo->prepare("SELECT p.*, c.full_name, c.phone, c.email, s.name as sector_name FROM payments p LEFT JOIN contributors c ON p.contributor_id=c.contributor_id LEFT JOIN sectors s ON c.sector_id=s.sector_id WHERE p.payment_id=?");
$pay->execute([$id]);
$data = $pay->fetch();
if(!$data){ header("Location: donate.php"); exit; }
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Thank You - Butabika Chaplaincy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f0f4ff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:15px}.cardx{background:white;border-radius:24px;max-width:600px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15);overflow:hidden}</style>
</head><body>
<div class="cardx">
<div class="text-center p-4" style="background:linear-gradient(135deg,#1a4bff,#2c7bff);color:white">
<div style="width:90px;height:90px;background:white;border-radius:50%;margin:0 auto 15px;display:flex;align-items:center;justify-content:center"><i class="bi bi-check-lg text-primary" style="font-size:45px"></i></div>
<h3 class="fw-bold">God Bless You, <?= htmlspecialchars($data['full_name']) ?>!</h3>
<p class="mb-0">Your donation has been received</p>
</div>
<div class="p-4">
<div class="bg-light rounded-4 p-3 mb-3">
<h6 class="fw-bold">🧾 Official Receipt</h6>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Receipt No:</span><b>#<?= $data['payment_id'] ?></b></div>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Donor:</span><b><?= htmlspecialchars($data['full_name']) ?></b></div>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Phone:</span><b><?= htmlspecialchars($data['phone']) ?></b></div>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Sector:</span><b><?= htmlspecialchars($data['sector_name']??'General') ?></b></div>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Method:</span><b><?= htmlspecialchars($data['payment_method']) ?></b></div>
<div class="d-flex justify-content-between small py-1"><span class="text-muted">Date:</span><b><?= $data['payment_date'] ?></b></div>
<hr>
<div class="d-flex justify-content-between"><span class="fw-bold">Amount:</span><span class="fw-bold text-primary fs-5">UGX <?= number_format($data['amount']) ?></span></div>
</div>

<div class="alert alert-success rounded-4">
<i class="bi bi-heart-fill"></i> <b>Dear <?= htmlspecialchars($data['full_name']) ?>,</b><br>
Thank you for your generous contribution of <b>UGX <?= number_format($data['amount']) ?></b> to Butabika Chaplaincy. Your gift will support the work of God. May the Lord bless you abundantly!<br><br>
<small class="text-muted">- Chaplaincy Team, Butabika Hospital</small>
</div>

<div class="alert alert-info rounded-4 small">
<i class="bi bi-shield-check"></i> <b>For Accountability:</b> This donation has been automatically recorded and <b>Admin & Treasurer have been notified</b>. Your information is securely stored in the Chaplaincy system for transparency.
</div>

<div class="d-flex gap-2">
<a href="index.php" class="btn btn-primary rounded-pill flex-fill">Home</a>
<a href="donate.php" class="btn btn-outline-primary rounded-pill flex-fill">Donate Again</a>
</div>
</div>
</div>
</body></html>