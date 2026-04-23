<?php
require_once __DIR__ . '/connect-db.php';

session_start();

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and execute
    $stmt = $db->prepare("SELECT username, password_hash FROM users WHERE username = ?");
    $stmt->execute([$username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
	if(password_verify($password, $user['password_hash'])) {
            $_SESSION['username'] = $user['username'];

            $message = "Login successful";
            $toastClass = "bg-success";  // green

            // Optional redirect
            //header("Location: dashboard.php"); Change to dashboard page when implemented
            exit();

        } else {
            $message = "Invalid password";
            $toastClass = "bg-danger";   // red
        }
    } else {
        $message = "User not found";
        $toastClass = "bg-danger";   // red
    }

    // Optional cleanup
    $stmt = null;
    $db = null;
}
?>
<!DOCTYPE html>
<html lang="en">

<style>
   .center-text {
   text-align: center;
   }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" 
          content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="../css/login.css">
    <title>Login Page</title>
</head>

<div class="center-text" style = "font-size:60px; color: white;">
MyNetflixReviews </div>


<body class="bg-dark">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white 
            <?php echo $toastClass; ?> border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close
                    btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form action="" method="post" class="form-control mt-5 p-4"
            style="height:auto; width:380px; box-shadow: rgba(60, 64, 67, 0.3) 
            0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2"
          style="text-align: center; color: #990000;"></i>
                <h5 class="text-center p-4" 
          style="font-weight: 700;">Login Into Your Account</h5>
            </div>
            <div class="col-mb-3">
                <label for="username"><i 
                  class="fa fa-user"></i> Username</label>
                <input type="text" name="username" id="username"
                  class="form-control" required>
            </div>
            <div class="col mb-3 mt-3">
                <label for="password"><i
                  class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password" 
                  class="form-control" required>
            </div>
            <div class="col mb-3 mt-3">
                <button type="submit" 
                  class="btn" style="font-weight: 600; background-color: #990000; color: white">Login</button>
            </div>
            <div class="col mb-2 mt-4">
                <p class="text-center" 
                  style="font-weight: 600; color: navy;"
                  ><a href="./register.php"
                        style="text-decoration: none;">Create Account</a>
            </div>
        </form>
    </div>
    <script>
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>

</html>