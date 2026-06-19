<?php
include 'db/connection.php';

$message = "";
$success = false;
$new_user_id = "";
$name = "";

if (isset($_POST['register'])) {
    $name     = $_POST['name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $repass   = $_POST['re-password'];

    if ($password !== $repass) {
        $message = "Passwords do not match!";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql    = "insert into users(name, email, password)
                   values('$name','$email','$hashed')";

        if (mysqli_query($conn, $sql)) {
            $success     = true;
            $new_user_id = mysqli_insert_id($conn);
            $message     = "Registration Successful!";
        } else {
            $message = "Email already exists!";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container">

    <?php if ($success): ?>

        <h2 style="color:green;">Registration Successful!</h2>

        <div style="
            background: #e8f4fd;
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;">

            <p style="font-size:15px; color:#333;">Welcome, <strong><?php echo $name; ?></strong>!</p>
            <p style="font-size:14px; color:#555; margin-top:8px;">Your Account Details:</p>
            <p style="font-size:28px; font-weight:bold; color:#007bff; margin:10px 0;">
                User ID: #<?php echo $new_user_id; ?>
            </p>
            <p style="font-size:13px; color:#888;">
                This ID will appear on every product you add.
            </p>
        </div>

        <div class="link">
            <a href="login.php" class="btn">Go to Login</a>
        </div>

    <?php else: ?>

        <h2>Register</h2>

        <?php if ($message != ""): ?>
            <p class="error"><?php echo $message; ?></p>
        <?php endif; ?>

        <form method="POST" id="registerForm">

            <input type="text" name="name"
                   id="name" placeholder="Enter your Name" required>
            <span class="error-msg" id="nameErr"></span>

            <input type="email" name="email"
                   id="email" placeholder="Enter your Email" required>
            <span class="error-msg" id="emailErr"></span>

            <input type="password" name="password"
                   id="password" placeholder="Enter your Password" required>
            <span class="error-msg" id="passErr"></span>

            <input type="password" name="re-password"
                   id="repassword" placeholder="Confirm Password" required>
            <span class="error-msg" id="repassErr"></span>

            <button type="submit" class="add" name="register">Register</button>

        </form>

        <div class="link"> Already have account? <a href="login.php">Login</a></div>

    <?php endif; ?>

</div>

<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    const name   = document.getElementById('name').value.trim();
    const email  = document.getElementById('email').value.trim();
    const pass   = document.getElementById('password').value;
    const repass = document.getElementById('repassword').value;

    document.getElementById('nameErr').textContent   = '';
    document.getElementById('emailErr').textContent  = '';
    document.getElementById('passErr').textContent   = '';
    document.getElementById('repassErr').textContent = '';

    if (name === '') {
        document.getElementById('nameErr').textContent = 'Name is required.';
        valid = false;
    }
    if (email === '') {
        document.getElementById('emailErr').textContent = 'Email is required.';
        valid = false;
    }
    if (pass.length < 6) {
        document.getElementById('passErr').textContent = 'Password must be at least 6 characters.';
        valid = false;
    }
    if (pass !== repass) {
        document.getElementById('repassErr').textContent = 'Passwords do not match!';
        valid = false;
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>