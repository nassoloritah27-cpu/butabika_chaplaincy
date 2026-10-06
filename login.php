<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/config/database.php';
$error="";
if($_SERVER['REQUEST_METHOD']==='POST'){
    $login=trim($_POST['login']??''); $pass=$_POST['password']??'';
    try{
        $stmt=$pdo->prepare("SELECT * FROM users WHERE username=? OR email=? LIMIT 1");
        $stmt->execute([$login,$login]); $user=$stmt->fetch();
        if(!$user) $error="Admin account not found.";
        else{
            $ok=($pass===$user['password_hash'])||password_verify($pass,$user['password_hash']);
            if($ok && $user['status']==='approved'){
                $_SESSION['logged_in']=true; $_SESSION['user_id']=$user['user_id']; $_SESSION['username']=$user['username']; $_SESSION['role']=$user['role'];
                header("Location: admin/index.php"); exit;
            } else $error=$ok?"Account not approved":"Incorrect password.";
        }
    }catch(Exception $e){ $error=$e->getMessage(); }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login - Butabika Chaplaincy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body{background:#f5f7fb; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px}
.login-wrapper{max-width:1000px; width:100%; background:white; border-radius:30px; overflow:hidden; box-shadow:0 20px 60px rgba(0,0,0,.1); display:flex; min-height:600px}
.left{flex:1; background:linear-gradient(135deg,#0d6efd 0%, #0a3d8f 100%); color:white; padding:50px 40px; display:flex; flex-direction:column; justify-content:space-between}
.right{flex:1; padding:50px 40px; display:flex; flex-direction:column; justify-content:center}
@media(max-width:768px){ .login-wrapper{flex-direction:column} }
.input-group{border-radius:15px; overflow:hidden; border:1px solid #e9ecef}
.input-group .form-control{border:none; padding:14px} .input-group .input-group-text{border:none; background:white}
.btn-primary{padding:12px; border-radius:15px; font-weight:600}
</style>
</head><body>
<div class="login-wrapper">
<div class="left">
<div><div class="d-flex align-items-center mb-5"><div class="bg-white rounded-3 p-2 me-3"><i class="bi bi-building fs-3 text-primary"></i></div><div><h5 class="mb-0 fw-bold">Butabika</h5><small>Catholic Chaplaincy</small></div></div>
<h1 class="fw-bold display-6 mb-3">Welcome<br>Back, Admin</h1>
<p class="opacity-75">Secure access to Chaplaincy Construction Management System.</p></div>
<div><div class="bg-white bg-opacity-10 rounded-4 p-3 mb-3"><div class="small"><b>MTN:</b> 0782743394 (51657673)<br><b>Airtel:</b> 0701743394 (7050461)<br><b>Bank:</b> 3266540694</div></div><p class="small opacity-50 mb-0">© 2025 Finance Committee</p></div>
</div>
<div class="right">
<h3 class="fw-bold mb-2">Admin Login</h3><p class="text-muted small mb-4">Enter credentials to access dashboard</p>
<?php if($error): ?><div class="alert alert-danger rounded-4 small"><?=htmlspecialchars($error)?></div><?php endif;?>
<form method="POST">
<div class="mb-3"><label class="small fw-bold mb-2">Username or Email</label><div class="input-group shadow-sm"><span class="input-group-text"><i class="bi bi-person"></i></span><input name="login" class="form-control" placeholder="e.g. admin" required></div></div>
<div class="mb-4"><label class="small fw-bold mb-2">Password</label><div class="input-group shadow-sm"><span class="input-group-text"><i class="bi bi-lock"></i></span><input name="password" type="password" class="form-control" id="pass" placeholder="Enter password" required><span class="input-group-text" onclick="document.getElementById('pass').type=document.getElementById('pass').type==='password'?'text':'password'" style="cursor:pointer"><i class="bi bi-eye"></i></span></div></div>
<button class="btn btn-primary w-100 shadow"><i class="bi bi-box-arrow-in-right"></i> Login to Dashboard</button>
</form>
<div class="d-flex justify-content-between mt-4 small"><a href="admin/forgot.php" class="text-decoration-none">Forgot Password?</a><a href="index.php" class="text-decoration-none text-muted">Back to Home</a></div>
</div>
</div>
</body></html>