<?php
require_once __DIR__ . '/connect-db.php';

$message = "";
$toastClass = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST['username'];
$password = $_POST['password'];
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$checkUsername = $db->prepare("SELECT username FROM users WHERE username = ?");
$checkUsername->execute([$username]);

if ($checkUsername->rowCount() > 0) {
        $message = "Username already exists";
        $toastClass = "bg-danger"; // Danger color
    } else {
        // Prepare and bind
        $stmt = $db->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");

        if ($stmt->execute([$username, $hashedPassword])) {
            $message = "Account created successfully";
            $toastClass = "bg-success"; // Success color
        } else {
            $message = "Error: " . $stmt->error;
          $toastClass = "bg-danger"; // Danger color
        }
    }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href=
"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css">
    <link rel="shortcut icon" href=
"https://cdn-icons-png.flaticon.com/512/295/295128.png">
    <script src=
"https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <title>Registration</title>
</head>
<div class="center-text" style = "font-size:60px; color: white;">
MyNetflixReviews </div>

<body class="bg-dark">
    <div class="container p-5 d-flex flex-column align-items-center">
        <?php if ($message): ?>
            <div class="toast align-items-center text-white <?php echo $toastClass; ?> border-0" 
          role="alert" aria-live="assertive" aria-atomic="true"
                style="background-color: "bg-danger"; border: 2px #990000;">
                <div class="d-flex">
                    <div class="toast-body">
                        <?php echo $message; ?>
                    </div>
                    <button type="button" class="btn-close
                    btn-close-white me-2 m-auto" 
                          data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
        <form method="post" class="form-control mt-5 p-4"
            style="height:auto; width:380px;
            box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px,
            rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
            <div class="row text-center">
                <i class="fa fa-user-circle-o fa-3x mt-1 mb-2" style="color: #990000;"></i>
                <h5 class="p-4" style="font-weight: 700;">Create Your Account</h5>
            </div>
            <div class="mb-2">
                <label for="username"><i 
                  class="fa fa-user"></i> User Name</label>
                <input type="text" name="username" id="username"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-2">
                <label for="password"><i 
                  class="fa fa-lock"></i> Password</label>
                <input type="password" name="password" id="password"
                  class="form-control" required>
            </div>
            <div class="mb-2 mt-3">
                <button type="submit" 
                  class="btn" style="font-weight: 600; background-color: #990000; color: white">Create
                    Account</button> 
            </div>
            <div class="mb-2 mt-4">
                <p class="text-center" style="font-weight: 600; 
                color: navy;">I have an Account: <a href="./login.php"
                        style="text-decoration: none;">Login</a></p>
            </div>
        </form>
    </div>
    <script>
        let toastElList = [].slice.call(document.querySelectorAll('.toast'))
        let toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 3000 });
        });
        toastList.forEach(toast => toast.show());
    </script>
</body>
</html>
