<?php
require_once __DIR__ . '/config/database.php';
try {
    $total = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status IN ('completed','approved','verified')")->fetchColumn();
    $goal = 500000000; // 500M UGX goal - you can change
    $percent = $goal>0 ? min(100, round(($total/$goal)*100)) : 0;
    $recent = $pdo->query("SELECT donor_name, amount, created_at FROM payments WHERE status IN ('completed','approved') ORDER BY created_at DESC LIMIT 6")->fetchAll();
} catch(Exception $e){ $total=0; $goal=500000000; $percent=0; $recent=[]; }
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Butabika Catholic Chaplaincy - Build God's House</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
.hero{background:linear-gradient(rgba(0,0,0,0.7),rgba(0,0,0,0.7)),url('https://images.unsplash.com/photo-1436397543931-01c1a5b4d1f5?w=1200') center/cover; color:white; padding:100px 0 80px;}
.card-hover{transition:.3s} .card-hover:hover{transform:translateY(-5px); box-shadow:0 10px 25px rgba(0,0,0,.15)!important}
.progress{height:12px} .icon-box{width:60px;height:60px; display:flex; align-items:center; justify-content:center; border-radius:15px; font-size:28px}
</style>
</head><body>

<nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top py-3">
<div class="container">
<a class="navbar-brand fw-bold fs-4" href="#"><i class="bi bi-building text-primary"></i> Butabika Chaplaincy</a>
<button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="nav">
<ul class="navbar-nav ms-auto align-items-center">
<li class="nav-item"><a class="nav-link fw-semibold" href="#about">About</a></li>
<li class="nav-item"><a class="nav-link fw-semibold" href="#accounts">Accounts</a></li>
<li class="nav-item"><a class="nav-link fw-semibold" href="#donate">Donate</a></li>
<li class="nav-item ms-2"><a href="login.php" class="btn btn-primary rounded-pill px-4"><i class="bi bi-lock"></i> Admin</a></li>
</ul>
</div>
</div>
</nav>

<section class="hero text-center">
<div class="container">
<span class="badge bg-warning text-dark px-3 py-2 rounded-pill mb-3"><i class="bi bi-heart-fill"></i> Building God's House Together</span>
<h1 class="display-4 fw-bold mb-3">Help Us Build The<br>Butabika Catholic Chaplaincy</h1>
<p class="lead mb-4 opacity-75">A sacred place of worship, hope and community for Butabika Hospital and beyond. Your contribution builds faith.</p>
<div class="row justify-content-center mt-5">
<div class="col-md-8">
<div class="bg-white bg-opacity-10 p-4 rounded-4 backdrop-blur">
<div class="d-flex justify-content-between mb-2"><span>UGX <?= number_format($total) ?> raised</span><span>Goal: UGX <?= number_format($goal) ?></span></div>
<div class="progress mb-2"><div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" style="width: <?= $percent ?>%"></div></div>
<div class="d-flex justify-content-between small"><span><?= $percent ?>% Completed</span><span><?= count($recent) ?>+ Donors</span></div>
</div>
</div>
</div>
<a href="#donate" class="btn btn-warning btn-lg rounded-pill px-5 mt-4 fw-bold shadow"><i class="bi bi-gift"></i> Donate Now</a>
<a href="#about" class="btn btn-outline-light btn-lg rounded-pill px-5 mt-4 ms-2">Learn More</a>
</div>
</section>

<section id="about" class="py-5 bg-light">
<div class="container py-4">
<div class="row g-5 align-items-center">
<div class="col-lg-6"><img src="https://images.unsplash.com/photo-1507692049790-de58227181d0?w=600" class="img-fluid rounded-4 shadow" alt="Church"></div>
<div class="col-lg-6">
<h2 class="fw-bold mb-3">Why We Are Building</h2>
<p class="text-muted">Butabika Catholic Chaplaincy serves hundreds of patients, staff, and the surrounding community. Our current structure is too small and needs a permanent, dignified house of prayer.</p>
<div class="row g-3 mt-3">
<div class="col-6"><div class="d-flex"><div class="icon-box bg-primary bg-opacity-10 text-primary me-3"><i class="bi bi-people"></i></div><div><h6 class="fw-bold mb-0">500+</h6><small class="text-muted">Weekly Worshippers</small></div></div></div>
<div class="col-6"><div class="d-flex"><div class="icon-box bg-success bg-opacity-10 text-success me-3"><i class="bi bi-hospital"></i></div><div><h6 class="fw-bold mb-0">Butabika Hospital</h6><small class="text-muted">Main Community</small></div></div></div>
<div class="col-6"><div class="d-flex"><div class="icon-box bg-warning bg-opacity-10 text-warning me-3"><i class="bi bi-bullseye"></i></div><div><h6 class="fw-bold mb-0">UGX 500M</h6><small class="text-muted">Construction Goal</small></div></div></div>
<div class="col-6"><div class="d-flex"><div class="icon-box bg-info bg-opacity-10 text-info me-3"><i class="bi bi-shield-check"></i></div><div><h6 class="fw-bold mb-0">100% Transparent</h6><small class="text-muted">All donations tracked</small></div></div></div>
</div>
</div>
</div>
</div>
</section>

<!-- PAYMENT METHODS - WORKING VERSION -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<div class="container my-4">
<div class="row g-4">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<div class="container my-4">
<div class="row g-4">

<div class="container my-4">
<!-- Your Chaplaincy Icon on top -->
<div class="text-center mb-4">
<img src="images/logo.png" alt="Butabika Chaplaincy" style="width:90px;height:90px;border-radius:50%;box-shadow:0 4px 15px rgba(0,0,0,.2);border:3px solid #FFCC00">
<h5 class="fw-bold mt-2" style="color:#0d4ba0">Butabika Chaplaincy</h5>
<p class="small text-muted">Support God's Work - Choose Payment Method</p>
</div>

<div class="row g-4">
<!-- MTN -->
<div class="col-md-4">
<div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100" style="border-top:6px solid #FFCC00">
<div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:#FFCC00;border-radius:15px;font-weight:900;color:#000;border:2px solid #000">
<span style="border:2px solid #000;border-radius:50px;padding:4px 10px;font-size:12px">MTN</span>
</div>
<h6 class="fw-bold">MTN Mobile Money</h6>
<div class="my-2"><span class="fw-bold px-3 py-2 rounded-pill" style="background:#000;color:#FFCC00">0782743394</span></div>
<p class="small text-muted mb-0 mt-2">Merchant: 51657673</p>
<div class="mt-3 py-2 rounded-pill fw-bold small" style="background:#FFCC00;color:#000">Most Popular</div>
</div>
</div>

<!-- AIRTEL - FIXED NO BROKEN IMAGE -->
<div class="col-md-4">
<div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100" style="border-top:6px solid #E40000">
<div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:#E40000;border-radius:50%;color:white;font-weight:900;font-size:20px">airtel</div>
<h6 class="fw-bold">Airtel Money</h6>
<div class="my-2"><span class="fw-bold px-3 py-2 rounded-pill text-white" style="background:#E40000">0701743394</span></div>
<p class="small text-muted mb-0 mt-2">Merchant: 7050461</p>
<div class="mt-3 py-2 rounded-pill fw-bold small text-white" style="background:#E40000">Fast & Secure</div>
</div>
</div>

<!-- CENTENARY - FIXED GREEN ACCOUNT -->
<div class="col-md-4">
<div class="card border-0 shadow-sm rounded-4 p-4 text-center h-100" style="border-top:6px solid #006A4E">
<div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:70px;height:70px;background:#006A4E;border-radius:50%;color:white;font-size:28px">₵</div>
<h6 class="fw-bold">Centenary Bank</h6>
<div class="my-2"><span class="fw-bold px-3 py-2 rounded-3 d-inline-block" style="background:#E8F5E9;color:#006A4E;border:2px dashed #006A4E">3266540694</span></div>
<p class="small fw-bold mb-0 mt-1" style="color:#006A4E">Butabika Chaplaincy Construction</p>
<div class="mt-3 py-2 rounded-pill fw-bold small text-white" style="background:#006A4E">For Large Donations</div>
</div>
</div>

</div>
</div>
</div>
</div>
</div>
</div>
<section id="donate" class="py-5 bg-dark text-white">
<div class="container py-4">
<div class="row g-5">
<div class="col-lg-5">
<h2 class="fw-bold mb-3">Make Your Contribution Today</h2>
<p class="opacity-75">Your donation, big or small, brings us closer to completing God's house. All donations are acknowledged and prayed for.</p>
<?php if(!empty($recent)): ?><div class="mt-4"><h6 class="fw-bold"><i class="bi bi-clock-history"></i> Recent Blessings</h6><?php foreach($recent as $r): ?><div class="d-flex justify-content-between border-bottom border-secondary py-2 small"><span><?= htmlspecialchars($r['donor_name']??'Anonymous') ?></span><span class="text-warning">UGX <?= number_format($r['amount']) ?></span></div><?php endforeach; ?></div><?php endif; ?>
</div>
<div class="col-lg-7">
<div class="card rounded-4 p-4 shadow-lg">
<h5 class="fw-bold text-dark mb-3"><i class="bi bi-heart text-danger"></i> Donate / Pledge Form</h5>
<form action="donate.php" method="POST">
<div class="row g-2">
<div class="col-md-6"><input name="donor_name" class="form-control mb-3" placeholder="Full Name *" required></div>
<div class="col-md-6"><input name="phone" class="form-control mb-3" placeholder="Phone Number"></div>
</div>
<input name="email" type="email" class="form-control mb-3" placeholder="Email (optional)">
<div class="row g-2">
<div class="col-md-6"><input name="amount" type="number" class="form-control mb-3" placeholder="Amount UGX *" required></div>
<div class="col-md-6"><select name="method" class="form-select mb-3"><option>MTN MoMo - 0782743394</option><option>Airtel Money - 0701743394</option><option>Centenary Bank - 3266540694</option><option>Cash</option></select></div>
</div>
<textarea name="message" class="form-control mb-3" rows="3" placeholder="Prayer intention or message..."></textarea>
<button class="btn btn-primary w-100 rounded-pill py-2 fw-bold"><i class="bi bi-send"></i> Submit Donation - God Bless You</button>
<p class="small text-muted text-center mt-2">After Mobile Money payment, submit form for recording. You will be prayed for!</p>
</form>
</div>
</div>
</div>
</div>
</section>

<footer class="bg-black text-white-50 py-4 text-center small">
<p class="mb-1 fw-bold text-white">Butabika Catholic Chaplaincy Construction Project</p>
<p class="mb-1"><i class="bi bi-geo-alt"></i> Butabika Hospital, Kampala, Uganda | <i class="bi bi-telephone"></i> MTN 0782743394 | Airtel 0701743394 | Bank 3266540694</p>
<p class="mb-0">© 2025 Chaplaincy Finance Committee - 100% Transparent & Accountable</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>