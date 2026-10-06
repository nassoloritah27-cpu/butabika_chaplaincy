<?php
session_start();
require_once __DIR__.'/config/database.php';
try{
  $sectors = $pdo->query("SELECT * FROM sectors ORDER BY name ASC")->fetchAll();
}catch(Exception $e){ $sectors=[]; }

if($_SERVER['REQUEST_METHOD']=='POST'){
  $full_name = trim($_POST['full_name']);
  $phone = trim($_POST['phone']);
  $email = trim($_POST['email']);
  $sector_id = $_POST['sector_id'] ?: NULL;
  $amount = (int)$_POST['amount'];
  $method = $_POST['method'];

  // FORCE generate unique code
  $code = 'CON-'.time().rand(10,99);
  
  $pdo->beginTransaction();
  // Contributor - check by phone
  $st = $pdo->prepare("SELECT contributor_id FROM contributors WHERE phone=? LIMIT 1");
  $st->execute([$phone]);
  $found = $st->fetch();
  if($found){
    $cid = $found['contributor_id'];
  }else{
    $pdo->prepare("INSERT INTO contributors (contributor_code, full_name, phone, email, sector_id) VALUES (?,?,?,?,?)")
        ->execute([$code, $full_name, $phone, $email, $sector_id]);
    $cid = $pdo->lastInsertId();
  }
  // Pledge first
  $pdo->prepare("INSERT INTO pledges (contributor_id, pledged_amount, status) VALUES (?,'Fulfilled')")
      ->execute([$cid, $amount]);
  $pledge_id = $pdo->lastInsertId();
  // Payment with pledge_id
  $pdo->prepare("INSERT INTO payments (contributor_id, pledge_id, amount, payment_method, payment_date, status) VALUES (?,?,?, ?, NOW(), 'Verified')")
      ->execute([$cid, $pledge_id, $amount, $method]);
  $pay_id = $pdo->lastInsertId();

  // ADMIN ALERT for accountability
  $sector_name = 'General';
  foreach($sectors as $s) if(($s['sector_id']??$s['id'])==$sector_id) $sector_name=$s['name'];
  $pdo->prepare("INSERT INTO notifications (title, message, type, is_read, created_at) VALUES (?,?,?,0,NOW())")
      ->execute(["NEW DONATION UGX ".number_format($amount), "Donor: $full_name | Phone: $phone | Email: $email | Amount: UGX ".number_format($amount)." | Method: $method | Sector: $sector_name | Payment ID: #$pay_id", "donation"]);
  
  // THANK YOU for donor
  $pdo->prepare("INSERT INTO thank_you_messages (contributor_id, message, status, created_at) VALUES (?, ?, 'sent', NOW())")
      ->execute([$cid, "Dear $full_name, Thank you for your generous contribution of UGX ".number_format($amount)." to Butabika Chaplaincy. We have received your $method donation for $sector_name. May God bless you abundantly! Receipt #$pay_id"]);

  $pdo->commit();
  header("Location: thank-you.php?id=$pay_id");
  exit;
}
?>
<!DOCTYPE html><html><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Donate - Butabika Chaplaincy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>body{background:#1a4bff;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:15px}.cardx{background:white;border-radius:20px;max-width:500px;width:100%;overflow:hidden;box-shadow:0 20px 50px rgba(0,0,0,.3)}.header{background:linear-gradient(90deg,#1a4bff,#2c7bff);color:white;padding:25px;text-align:center}.form-control,.form-select{border-radius:50px;padding:12px 18px;margin-bottom:10px}</style>
</head><body>
<div class="cardx">
<div class="header"><h5 class="fw-bold mb-0">❤️ Donate to Chaplaincy</h5><small>Support the work of God at Butabika</small></div>
<div class="p-4">
<form method="POST">
<input name="full_name" class="form-control" placeholder="Full Name *" required>
<div class="row g-2"><div class="col-6"><input name="phone" class="form-control" placeholder="Phone e.g. 0765748390" required></div><div class="col-6"><input name="email" type="email" class="form-control" placeholder="Email (optional)"></div></div>
<select name="sector_id" class="form-select"><option value="">-- Select Sector --</option><?php foreach($sectors as $s): ?><option value="<?= $s['sector_id']??$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?></select>
<input name="amount" type="number" class="form-control" placeholder="Amount UGX e.g. 100000" min="1000" required>
<select name="method" class="form-select"><option>MTN Mobile Money</option><option>Airtel Money</option><option>Cash</option><option>Bank Transfer</option></select>
<button class="btn btn-primary w-100 rounded-pill py-2 fw-bold">❤️ Donate Now</button>
</form>
</div></div></body></html>