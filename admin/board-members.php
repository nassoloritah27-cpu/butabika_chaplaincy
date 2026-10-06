<?php
session_start();
require_once __DIR__.'/../config/database.php';
if(empty($_SESSION['logged_in'])){ header("Location:../login.php"); exit; }

$uploadDir = __DIR__.'/../uploads/board/';
if(!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
chmod($uploadDir, 0777);
$uploadError = '';

// AUTO-DETECT board_members columns
try{
  $bcols = $pdo->query("SHOW COLUMNS FROM board_members")->fetchAll(PDO::FETCH_COLUMN,0);
  $bid_col = in_array('board_member_id',$bcols)?'board_member_id':(in_array('id',$bcols)?'id':(in_array('member_id',$bcols)?'member_id':$bcols[0]));
}catch(Exception $e){ $bcols=[]; $bid_col='board_member_id'; }

// AUTO-DETECT users columns
try{
  $ucols = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN,0);
  $uid_col = in_array('id',$ucols)?'id':(in_array('user_id',$ucols)?'user_id':$ucols[0]);
  $uname_col = in_array('username',$ucols)?'username':$ucols[1];
  $uemail_col = in_array('email',$ucols)?'email':$ucols[1];
}catch(Exception $e){ $uid_col='id'; $uname_col='username'; $uemail_col='email'; }

if(isset($_GET['delete'])){
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
  $pdo->prepare("DELETE FROM board_members WHERE $bid_col=?")->execute([$_GET['delete']]);
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
  header("Location:board-members.php"); exit;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
  $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
  $photoName = $_POST['old_photo']?? '';

  // PHOTO UPLOAD WITH ERROR CHECKING
  if(!empty($_FILES['photo']['name']) && $_FILES['photo']['error']==0){
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if(!in_array($ext, $allowed)){
      $uploadError = "Only JPG, PNG, GIF, WEBP allowed! You uploaded: $ext";
    } else if($_FILES['photo']['size'] > 5*1024*1024){
      $uploadError = "Photo too big! Max 5MB. Your file: ".round($_FILES['photo']['size']/1024/1024,2)."MB";
    } else {
      $photoName = 'board_'.time().'_'.rand(100,999).'.'.$ext;
      $target = $uploadDir.$photoName;
      if(!move_uploaded_file($_FILES['photo']['tmp_name'], $target)){
        $uploadError = "FAILED to save to: $target - Create folder manually: C:\\xampp\\htdocs\\butabika_chaplaincy\\uploads\\board\\ and set writable";
        $photoName = $_POST['old_photo']?? '';
      }
    }
  } else if(!empty($_FILES['photo']['name']) && $_FILES['photo']['error']!=0){
    $uploadError = "PHP Upload Error Code: ".$_FILES['photo']['error']." - Check php.ini upload_max_filesize";
  }

  // Only save if no upload error OR user didn't try to upload
  if(empty($uploadError) || empty($_FILES['photo']['name'])){
    $fields=[]; $vals=[];
    $map = ['member_number'=>$_POST['member_number'], 'full_name'=>$_POST['full_name'], 'position'=>$_POST['position'], 'sector'=>$_POST['sector'], 'phone'=>$_POST['phone'], 'email'=>$_POST['email'], 'photo'=>$photoName, 'user_id'=>$_POST['user_id']?:NULL];
    foreach($map as $k=>$v){ if(in_array($k,$bcols)){ $fields[]=$k; $vals[]=$v; } }
    if(!in_array('full_name',$bcols) && in_array('name',$bcols)){ $fields[]='name'; $vals[]=$_POST['full_name']; }
    if(!empty($fields)){
      if(!empty($_POST['board_member_id'])){
        $set = implode('=?,',$fields).'=?';
        $pdo->prepare("UPDATE board_members SET $set WHERE $bid_col=?")->execute([...$vals, $_POST['board_member_id']]);
      } else {
        $pdo->prepare("INSERT INTO board_members (".implode(',',$fields).") VALUES (".rtrim(str_repeat('?,',count($fields)),',').")")->execute($vals);
      }
      $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
      header("Location:board-members.php"); exit;
    }
  }
  $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
}

$members = $pdo->query("SELECT * FROM board_members ORDER BY $bid_col DESC")->fetchAll();
try{ $users = $pdo->query("SELECT $uid_col, $uname_col, $uemail_col FROM users ORDER BY $uname_col")->fetchAll(); }catch(Exception $e){ $users=[]; }

$edit = null;
if(isset($_GET['edit'])){ $stmt=$pdo->prepare("SELECT * FROM board_members WHERE $bid_col=?"); $stmt->execute([$_GET['edit']]); $edit=$stmt->fetch(); }
function getUserName($users,$uid_col,$uname_col,$id){ foreach($users as $u){ if($u[$uid_col]==$id) return $u[$uname_col]; } return null; }
?>
<!DOCTYPE html><html><head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>body{background:#f4f7fb}.card{border:none;border-radius:18px;box-shadow:0 10px 30px rgba(0,0,0,.06)}.btn-pill{border-radius:50px}.avatar{width:50px;height:50px;object-fit:cover;border-radius:50%}</style>
</head><body class="p-4"><div class="container-fluid">
<div class="d-flex justify-content-between align-items-center mb-4"><h3 class="fw-bold"><i class="bi bi-people-fill text-primary"></i> Board Members</h3><a href="index.php" class="btn btn-dark btn-pill">Dashboard</a></div>
<div class="row g-4">
<div class="col-lg-4"><div class="card p-4">
<h6 class="fw-bold mb-3"><?= $edit? 'Edit Board Member' : 'Add Board Member'?></h6>
<?php if(!empty($uploadError)):?><div class="alert alert-danger rounded-4 small"><i class="bi bi-exclamation-triangle"></i> <?= $uploadError?></div><?php endif;?>
<?php if(!is_dir($uploadDir)):?><div class="alert alert-warning rounded-4 small">Folder missing! Create: <b>uploads/board/</b></div><?php endif;?>
<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="board_member_id" value="<?= $edit[$bid_col]?? ''?>">
<input type="hidden" name="old_photo" value="<?= $edit['photo']?? ''?>">
<input name="member_number" class="form-control rounded-pill mb-2" placeholder="Member Number e.g. BM-001" value="<?= $edit['member_number']?? ''?>" required>
<input name="full_name" class="form-control rounded-pill mb-2" placeholder="Full Name" value="<?= $edit['full_name']?? $edit['name']?? ''?>" required>
<input name="position" class="form-control rounded-pill mb-2" placeholder="Position e.g. Chairperson" value="<?= $edit['position']?? ''?>">
<input name="sector" class="form-control rounded-pill mb-2" placeholder="Sector" value="<?= $edit['sector']?? ''?>">
<input name="phone" class="form-control rounded-pill mb-2" placeholder="Phone" value="<?= $edit['phone']?? ''?>">
<input name="email" class="form-control rounded-pill mb-2" placeholder="Email" value="<?= $edit['email']?? ''?>">
<label class="small fw-bold mt-2">Link to System User Account (Optional)</label>
<select name="user_id" class="form-select rounded-pill mb-2">
<option value="">-- No Account --</option>
<?php foreach($users as $u):?><option value="<?= $u[$uid_col]?>" <?= ($edit['user_id']??'')==$u[$uid_col]?'selected':''?>><?= htmlspecialchars($u[$uname_col].' - '.$u[$uemail_col])?></option><?php endforeach;?>
</select>

<label class="small fw-bold">Photo (Max 5MB, JPG/PNG)</label>
<input type="file" name="photo" class="form-control rounded-pill mb-3" accept="image/*">
<?php if(!empty($edit['photo'])):?><img src="../uploads/board/<?= $edit['photo']?>" style="width:80px;border-radius:12px" class="mb-3"><br><?php endif;?>
<button class="btn btn-primary w-100 btn-pill fw-bold"><?= $edit? 'Update' : 'Add'?> Member</button>
<?php if($edit):?><a href="board-members.php" class="btn btn-light w-100 btn-pill mt-2">Cancel</a><?php endif;?>
</form>
</div></div>
<div class="col-lg-8"><div class="card p-3"><div class="table-responsive"><table class="table align-middle table-hover">
<thead class="table-light"><tr><th>Photo</th><th>No.</th><th>Name</th><th>Position</th><th>Phone</th><th>User</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($members as $m): $uname=getUserName($users,$uid_col,$uname_col,$m['user_id']??'');?>
<tr>
<td><?php if(!empty($m['photo']) && file_exists($uploadDir.$m['photo'])):?><img src="../uploads/board/<?= $m['photo']?>" class="avatar"><?php else:?><div class="avatar bg-secondary d-flex align-items-center justify-content-center text-white"><i class="bi bi-person"></i></div><?php endif;?></td>
<td><span class="badge bg-light text-dark border"><?= $m['member_number']?? '-'?></span></td>
<td class="fw-bold"><?= htmlspecialchars($m['full_name']?? $m['name']?? '')?><br><small class="text-muted"><?= $m['sector']?? ''?></small></td>
<td><?= htmlspecialchars($m['position']?? '')?></td>
<td><?= htmlspecialchars($m['phone']?? '')?></td>
<td><?php if($uname):?><span class="badge bg-success"><?= htmlspecialchars($uname)?></span><?php else:?><span class="badge bg-secondary">No Account</span><?php endif;?></td>
<td>
<a href="?edit=<?= $m[$bid_col]?>" class="btn btn-sm btn-warning btn-pill"><i class="bi bi-pencil"></i></a>
<a href="?delete=<?= $m[$bid_col]?>" onclick="return confirm('Delete?')" class="btn btn-sm btn-danger btn-pill"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach;?>
<?php if(empty($members)) echo "<tr><td colspan=7 class='text-center py-4 text-muted'>No board members yet. Add first one!</td></tr>";?>
</tbody></table></div></div></div>
</div>

</div></body></html>