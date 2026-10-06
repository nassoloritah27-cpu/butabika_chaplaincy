<?php
require_once __DIR__.'/config/database.php';
$sectors = $pdo->query("SELECT * FROM sectors")->fetchAll();

if($_POST){
  $code = 'CON-'.time();
  $pdo->prepare("INSERT INTO contributors (contributor_code, full_name, phone, email, sector_id) VALUES (?,?,?,?,?)")
      ->execute([$code, $_POST['full_name'], $_POST['phone'], $_POST['email'], $_POST['sector_id'] ?: NULL]);
  $cid = $pdo->lastInsertId();
  
  $pdo->prepare("INSERT INTO pledges (contributor_id, pledged_amount, status) VALUES (?,?,'Fulfilled')")->execute([$cid, $_POST['amount']]);
  $pid = $pdo->lastInsertId();
  
  $pdo->prepare("INSERT INTO payments (contributor_id, pledge_id, amount, payment_method, payment_date, status) VALUES (?,?,?, ?, NOW(), 'Verified')")
      ->execute([$cid, $pid, $_POST['amount'], $_POST['method']]);
  $pay_id = $pdo->lastInsertId();

  // ADMIN ALERT
  $pdo->prepare("INSERT INTO notifications (title, message, type, is_read, created_at) VALUES (?,?,?,0,NOW())")
      ->execute(["NEW DONATION UGX ".$_POST['amount'], "Donor: ".$_POST['full_name']." | ".$_POST['phone']." | UGX ".$_POST['amount']." | ".$_POST['method'], "donation"]);
  
  // THANK YOU TO DONOR
  $pdo->prepare("INSERT INTO thank_you_messages (contributor_id, message, status, created_at) VALUES (?,?,'sent',NOW())")
      ->execute([$cid, "Dear ".$_POST['full_name'].", Thank you for UGX ".$_POST['amount'].". God bless!"]);

  header("Location: thank-you.php?id=$pay_id");
  exit;
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Donate</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body style="background:#1a4bff;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:15px">
<div style="background:white;border-radius:20px;max-width:480px;width:100%;overflow:hidden">
<div style="background:#1a4bff;color:white;padding:20px;text-align:center"><h5>❤️ Donate to Chaplaincy</h5></div>
<div class="p-4">
<form method="POST">
<input name="full_name" class="form-control mb-2" placeholder="Full Name * e.g. Rian Kayi" required>
<input name="phone" class="form-control mb-2" placeholder="Phone * e.g. 0765748390" required>
<input name="email" class="form-control mb-2" placeholder="Email (optional)">
<select name="sector_id" class="form-select mb-2"><option value="">-- Select Sector --</option>
<?php foreach($sectors as $s): ?><option value="<?=$s['sector_id']??$s['id']?>"><?=$s['name']?></option><?php endforeach; ?>
</select>
<input name="amount" type="number" class="form-control mb-2" placeholder="Amount * e.g. 100000" required>
<select name="method" class="form-select mb-3"><option>MTN Mobile Money</option><option>Airtel Money</option><option>Cash</option></select>
<button class="btn btn-primary w-100 rounded-pill">❤️ Donate Now</button>
</form></div></div></body></html>