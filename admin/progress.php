<?php
session_start(); header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); header("Pragma: no-cache");
require_once __DIR__ . '/../config/database.php';
if(empty($_SESSION['logged_in'])){header("Location:../login.php");exit;}
if(isset($_GET['delete'])){$pdo->prepare("DELETE FROM construction_progress WHERE progress_id=?")->execute([$_GET['delete']]); header("Location:progress.php"); exit;}
if($_SERVER['REQUEST_METHOD']=='POST'){
 $photo=''; if(!empty($_FILES['photo']['name'])){ $photo=time().'_'.$_FILES['photo']['name']; move_uploaded_file($_FILES['photo']['tmp_name'], __DIR__.'/../uploads/'.$photo); }
 $pdo->prepare("INSERT INTO construction_progress (title, description, percentage, photo, report_date, created_by) VALUES (?,?,?,?,?,?)")->execute([$_POST['title'], $_POST['desc'], $_POST['percent'], $photo, $_POST['date'], $_SESSION['user_id']]);
 header("Location:progress.php?msg=added"); exit;
}
$progress=$pdo->query("SELECT * FROM construction_progress ORDER BY progress_id DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><style>body{background:#f4f7fb}.card{border:none;border-radius:16px}</style></head><body class="p-4">
<div class="container-fluid"><div class="d-flex justify-content-between mb-3"><h4 class="fw-bold">Construction Progress</h4><a href="index.php" class="btn btn-dark rounded-pill">Dashboard</a></div>
<div class="row"><div class="col-md-4"><div class="card p-4"><h6>Add Update</h6><form method="POST" enctype="multipart/form-data"><input name="title" placeholder="Title e.g Foundation Done" class="form-control mb-2 rounded-pill" required><textarea name="desc" placeholder="Description" class="form-control mb-2 rounded-4"></textarea><input type="number" name="percent" min="0" max="100" placeholder="% Complete" class="form-control mb-2 rounded-pill" required><input type="date" name="date" class="form-control mb-2 rounded-pill"><input type="file" name="photo" class="form-control mb-3 rounded-pill"><button class="btn btn-primary w-100 rounded-pill">Post Update</button></form></div></div>
<div class="col-md-8"><div class="row g-3"><?php foreach($progress as $pr):?><div class="col-md-6"><div class="card p-3"><h6 class="fw-bold"><?=$pr['title']?></h6><div class="progress mb-2"><div class="progress-bar bg-success" style="width:<?=$pr['percentage']?>%"><?=$pr['percentage']?>%</div></div><small><?=$pr['description']?><br><?=$pr['report_date']?></small><br><a href="?delete=<?=$pr['progress_id']?>" class="btn btn-sm btn-outline-danger rounded-pill mt-2">Delete</a></div></div><?php endforeach;?></div></div></div></div></body></html>