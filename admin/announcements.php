<?php
session_start();
require_once __DIR__.'/../config/database.php';
if(empty($_SESSION['logged_in'])){ header("Location:../login.php"); exit; }

try{ $cols=$pdo->query("SHOW COLUMNS FROM announcements")->fetchAll(PDO::FETCH_COLUMN,0); }catch(Exception $e){ $cols=[]; }
$id_col = in_array('announcement_id',$cols)?'announcement_id':(in_array('id',$cols)?'id':$cols[0]);
$msg_col = in_array('message',$cols)?'message':(in_array('content',$cols)?'content':(in_array('announcement',$cols)?'announcement':'message'));

if(isset($_GET['delete'])){
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
  $pdo->prepare("DELETE FROM announcements WHERE $id_col=?")->execute([$_GET['delete']]);
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
  header("Location:announcements.php"); exit;
}
if($_SERVER['REQUEST_METHOD']=='POST'){
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
  $t=$_POST['title']??'Announcement'; $m=$_POST['message']??'';
  $f=[];$v=[];$p=[];
  if(in_array('title',$cols)){$f[]='title';$v[]=$t;$p[]='?';}
  if(in_array('message',$cols)){$f[]='message';$v[]=$m;$p[]='?';}
  if(in_array('content',$cols)){$f[]='content';$v[]=$m;$p[]='?';}
  if(in_array('announcement',$cols)){$f[]='announcement';$v[]=$m;$p[]='?';}
  if(in_array('audience',$cols)){$f[]='audience';$v[]='members';$p[]='?';} // FIXED to members only
  if(in_array('created_by',$cols)){$f[]='created_by';$v[]=1;$p[]='?';}
  if(in_array('created_at',$cols)){$f[]='created_at';$v[]=date('Y-m-d H:i:s');$p[]='?';}
  if($f){ $pdo->prepare("INSERT INTO announcements (".implode(',',$f).") VALUES (".implode(',',$p).")")->execute($v); }
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
  header("Location:announcements.php"); exit;
}
$anns=$pdo->query("SELECT * FROM announcements ORDER BY $id_col DESC")->fetchAll();
?>
<!DOCTYPE html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>body{background:#f4f7fb}.card{border:none;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.06)}.btn-pill{border-radius:50px}</style></head>
<body class="p-4"><div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-4"><h3 class="fw-bold"><i class="bi bi-people-fill text-primary"></i> Members Announcements</h3><a href="index.php" class="btn btn-dark btn-pill px-4"><i class="bi bi-arrow-left"></i> Dashboard</a></div>
<div class="row g-4"><div class="col-lg-4"><div class="card p-4"><h6 class="fw-bold mb-3">Post to Members Only</h6>
<div class="alert alert-info rounded-4 small"><i class="bi bi-info-circle"></i> This announcement will only be visible to logged-in members in their dashboard.</div>
<form method="POST"><input name="title" class="form-control rounded-pill mb-3" placeholder="Title (e.g. Members Meeting)" required>
<textarea name="message" class="form-control mb-3" rows="5" placeholder="Message for members..." required></textarea>
<button class="btn btn-primary w-100 btn-pill fw-bold"><i class="bi bi-send"></i> Send to Members</button></form></div></div>
<div class="col-lg-8"><div class="card p-3"><table class="table table-hover align-middle"><thead class="table-light"><tr><th>Title</th><th>Message</th><th>To</th><th>Action</th></tr></thead><tbody>
<?php foreach($anns as $a):?><tr><td class="fw-bold"><?=htmlspecialchars($a['title']??'')?></td>
<td><?=htmlspecialchars(substr($a[$msg_col]??'-',0,80))?></td><td><span class="badge bg-success"><i class="bi bi-people"></i> Members Only</span></td>
<td><a href="?delete=<?=$a[$id_col]?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger btn-pill"><i class="bi bi-trash"></i></a></td></tr>
<?php endforeach; if(empty($anns)) echo "<tr><td colspan=4 class='text-center py-4 text-muted'>No announcements yet</td></tr>";?>
</tbody></table></div></div></div></div></body></html>