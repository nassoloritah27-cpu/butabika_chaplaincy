<?php
require_once __DIR__ . '/../config/database.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header("Location: ../index.php"); exit; }

$donor_name = trim($_POST['donor_name'] ?? '');
$contact_raw = trim($_POST['contact'] ?? '');
$amount_input = (float)($_POST['amount'] ?? 0);
$amount_paid_input = (float)($_POST['amount_paid'] ?? 0);
$method = trim($_POST['method'] ?? 'MTN MoMo Merchant');
$currency = $_POST['currency'] ?? 'UGX';
$cash_received_by = trim($_POST['cash_received_by'] ?? '');
$rate = 3850;

if($donor_name=='' || $amount_input<=0) die("Invalid amount <a href='../index.php#donate'>Go back</a>");

$clean = preg_replace('/\s+/', '', $contact_raw);
if(!preg_match('/^(\+256\d{9}|0\d{9})$/', $clean)) die("Invalid UG phone! Use 0782743394 or 0701743394 <a href='../index.php#donate'>Go back</a>");
if(substr($clean,0,1)==='0') $clean='+256'.substr($clean,1);
$contact = $clean;

$amount_ugx = ($currency==='USD') ? $amount_input*$rate : $amount_input;
$paid_ugx = $amount_paid_input>0 ? (($currency==='USD')?$amount_paid_input*$rate:$amount_paid_input) : $amount_ugx;
$balance_ugx = $amount_ugx - $paid_ugx;
if($balance_ugx<0) $balance_ugx=0;

try{
    $contributor_code = 'CONT-'.strtoupper(substr(uniqid(),-5)).rand(100,999);
    $ins = $pdo->prepare("INSERT INTO contributors (contributor_code, full_name, phone, email, created_at) VALUES (?,?,?,?,NOW())");
    $ins->execute([$contributor_code, $donor_name, $contact, $contact]);
    $contributor_id = $pdo->lastInsertId();

    $pledge_ref = 'PLG-'.strtoupper(substr(uniqid(),-6));
    $notes = "Method:$method | Pledged:".number_format($amount_ugx)." UGX ($amount_input $currency) | Paid:".number_format($paid_ugx)." | Bal:".number_format($balance_ugx)." | Phone:$contact | CashBy:$cash_received_by | OFFICIAL MTN-MERCH:51657673 MTN-CONTACT:0782743394 AIRTEL-MERCH:7050461 AIRTEL-CONTACT:0701743394 CENTENARY:3266540694";

    $pledge = $pdo->prepare("INSERT INTO pledges (pledge_reference, contributor_id, sector_id, amount_pledged, pledge_date, status, notes, submitted_online, created_at, updated_at) VALUES (?,?,1,?,CURDATE(),'pending',?,1,NOW(),NOW())");
    $pledge->execute([$pledge_ref, $contributor_id, $amount_ugx, $notes]);
    $pledge_id = $pdo->lastInsertId();

    $pay_status = ($balance_ugx==0 || $method==='Cash') ? 'completed' : 'pending';
    try{
        $pdo->prepare("INSERT INTO payments (pledge_id, contributor_id, amount, payment_method, payment_date, status, created_at, notes) VALUES (?,?,?,?,CURDATE(),?,NOW(),?)")
            ->execute([$pledge_id, $contributor_id, $paid_ugx, $method, $pay_status, "Balance:".number_format($balance_ugx)." CashBy:$cash_received_by"]);
    }catch(Exception $e){
        $pdo->prepare("INSERT INTO payments (pledge_id, contributor_id, amount, payment_method, payment_date, status, created_at) VALUES (?,?,?,?,CURDATE(),?,NOW())")
            ->execute([$pledge_id, $contributor_id, $paid_ugx, $method, $pay_status]);
    }

    // Notify Admins & Treasurer
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), message TEXT, type VARCHAR(50), pledge_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $msg = "NEW PLEDGE $pledge_ref | Donor: $donor_name ($contact) | Pledged UGX ".number_format($amount_ugx)." | Paid UGX ".number_format($paid_ugx)." | BALANCE UGX ".number_format($balance_ugx)." | Method: $method | CashBy: $cash_received_by | OFFICIAL: MTN Merch 51657673 (0782743394) Airtel Merch 7050461 (0701743394) Centenary 3266540694";
    $pdo->prepare("INSERT INTO notifications (title, message, type, pledge_id) VALUES (?,?,?,?)")->execute(["New Donation $pledge_ref - Bal UGX ".number_format($balance_ugx), $msg, "treasurer_admin", $pledge_id]);

    $disp = ($currency==='USD')?'$'.number_format($amount_input,2).' USD':'UGX '.number_format($amount_input);

    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'></head><body class='bg-light d-flex justify-content-center align-items-center' style='min-height:100vh'><div class='card p-4 shadow rounded-4 text-center' style='max-width:600px'><h2 class='text-success'>🙏 Thank You!</h2><h5>God Bless You, ".htmlspecialchars($donor_name)."</h5><div class='bg-light p-3 rounded mt-3 text-start'><p class='mb-1'>Ref: <b>$pledge_ref</b></p><p class='mb-1'>Pledged: <b>$disp</b> ≈ UGX ".number_format($amount_ugx)."</p><p class='mb-1'>Paid Now: <b>UGX ".number_format($paid_ugx)."</b></p><p class='mb-1 ".($balance_ugx>0?'text-danger fw-bold':'text-success')."'>Balance: UGX ".number_format($balance_ugx)."</p><p class='mb-0 small'>Method: ".htmlspecialchars($method)."</p></div><div class='alert alert-info small mt-3 text-start'><b>Where your money goes:</b><br>".($method=='MTN MoMo Merchant'?"<b>MTN Merchant Code 51657673</b><br>Dial *165*3# > Code 51657673 > UGX ".number_format($amount_ugx)." > PIN<br>Confirm: 0782743394":"").($method=='Airtel Money Merchant'?"<b>Airtel Merchant Code 7050461</b><br>Dial *185*3*5# > Code 7050461 > UGX ".number_format($amount_ugx)." > PIN<br>Confirm: 0701743394":"").($method=='Bank Centenary'?"<b>Centenary Bank Account 3266540694</b><br>Name: Butabika Chaplaincy Dev't Committee<br>Acc: 3266540694 | SWIFT: CENTUGKA | Ref: $pledge_ref<br>WhatsApp slip to 0782743394 / 0701743394":"").($method=='Cash'?"<b>Cash: UGX ".number_format($paid_ugx)."</b><br>Received by: $cash_received_by<br>Receipt issued. Balance UGX ".number_format($balance_ugx)." recorded for Admins & Treasurer.":"")."</div><p class='small text-muted'>Info sent to Administrators & Treasurer. Balance ".($balance_ugx>0?'communicated as UGX '.number_format($balance_ugx).' still owed':'recorded as fully paid')."</p><a href='../index.php' class='btn btn-primary rounded-pill w-100'>Back Home</a></div></body></html>";

}catch(Exception $e){
    echo "<h3>Error: ".$e->getMessage()."</h3><p><a href='../index.php#donate'>Go back</a></p>";
}
?>