<?php
session_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
require_once __DIR__ . '/../config/database.php';
if (empty($_SESSION['logged_in'])) { header("Location: ../login.php"); exit; }
$type = $_GET['type'] ?? '';
if ($type == 'payments') {
  header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="butabika_payments_'.date('Y-m-d').'.csv"');
  $out = fopen('php://output','w'); fputcsv($out, ['Payment ID','Contributor Name','Email','Phone','Amount','Method','Status','Payment Date']);
  foreach($pdo->query("SELECT p.*, c.full_name, c.email, c.phone FROM payments p LEFT JOIN contributors c ON p.contributor_id=c.contributor_id ORDER BY p.payment_id DESC") as $r){ fputcsv($out, [$r['payment_id'], $r['full_name']??'', $r['email']??'', $r['phone']??'', $r['amount'], $r['payment_method'], $r['status'], $r['payment_date']]); } fclose($out); exit;
}
if ($type == 'contributors') {
  header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="butabika_contributors_'.date('Y-m-d').'.csv"');
  $out = fopen('php://output','w'); fputcsv($out, ['ID','Full Name','Email','Phone','Count','Total']);
  foreach($pdo->query("SELECT c.*, COUNT(p.payment_id) as cnt, COALESCE(SUM(p.amount),0) as total FROM contributors c LEFT JOIN payments p ON c.contributor_id=p.contributor_id GROUP BY c.contributor_id") as $r){ fputcsv($out, [$r['contributor_id'], $r['full_name'], $r['email'], $r['phone'], $r['cnt'], $r['total']]); } fclose($out); exit;
}
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:20px}</style></head><body class="p-4"><div class="container" style="max-width:700px">
<a href="index.php" class="btn btn-dark rounded-pill mb-4">Dashboard</a>
<div class="card p-4"><h4 class="fw-bold"><i class="bi bi-download"></i> Export Data</h4><div class="row g-3 mt-2"><div class="col-md-6"><div class="card p-3 bg-success bg-opacity-10"><h6>Payments</h6><a href="export.php?type=payments" class="btn btn-success w-100 rounded-pill">Export Payments CSV</a></div></div><div class="col-md-6"><div class="card p-3 bg-primary bg-opacity-10"><h6>Contributors</h6><a href="export.php?type=contributors" class="btn btn-primary w-100 rounded-pill">Export Contributors CSV</a></div></div></div></div></div></body></html>