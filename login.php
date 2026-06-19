<?php
session_start();
include 'db/connection.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = "";

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "select * from users where email='$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if (!$user) {

        $message = "Email does not exist!";

    } elseif (!password_verify($password, $user['password'])) {

        $message = "Incorrect Password!";

    } else {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];

        header("Location: dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">

    <h2>Login</h2>

    <?php if ($message != ""): ?>
        <p class="error"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" id="loginForm">

        <input type="email"
               name="email"
               id="email"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
               placeholder="Enter your Email"
               required>
        <span class="error-msg" id="emailErr"></span>

        <input type="password" name="password"
               id="password" placeholder="Enter your Password" required>
        <span class="error-msg" id="passErr"></span>

        <button type="submit" class="add" name="login">Login</button>

    </form>

    <div class="link">New User..? <a href="register.php">Register</a></div>

</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;

    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('password').value;

    document.getElementById('emailErr').textContent = '';
    document.getElementById('passErr').textContent  = '';

    if (email === '') {
        document.getElementById('emailErr').textContent = 'Email is required.';
        valid = false;
    }

    if (pass === '') {
        document.getElementById('passErr').textContent = 'Password is required.';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>