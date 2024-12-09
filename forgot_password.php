<?php
include ('conn.php');


// Include PHPMailer
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;

$showToastr = false; // Initialize flag to false

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Check if the email exists in the database
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        $expiry = time() + 300; // Expiration time in 5 minutes (300 seconds)

        // Save OTP and expiry time to database
        $update_sql = "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param('iis', $otp, $expiry, $email);
        if ($stmt_update->execute()) {

            // Send OTP to user's email
            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'devgiltechnicalsolutions@gmail.com'; // Replace with your email
            $mail->Password = 'eyqd rsfd dbio gysi'; // Replace with your App-Specific Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('devgiltechnicalsolutions@gmail.com', 'COA TSO Special Services Assignment Tracker');
            $mail->addAddress($email); // User's email
            $mail->Subject = 'Your OTP Code';
            $mail->Body    = "Your OTP code is: $otp. It expires in 5 minutes.";

            if ($mail->send()) {
                $_SESSION['otp_email'] = $email; // Save email for verification page
                header("Location: verify_otp.php");
                exit;
            } else {
                $showToastr = true; // Show error Toastr if mail fails
            }
        } else {
            $showToastr = true; // Show error Toastr if OTP update fails
        }
    } else {
        $showToastr = true; // Show error Toastr if email not found
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COA TSO Special Services Assignment Tracker</title>
    <!-- notif  -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300,700|Open+Sans:400,300,600');

* {
  box-sizing: border-box;
}

body {
  font-family: 'Open Sans', helvetica, arial, sans-serif;
  background: url('./images/Yellow\ Modern\ The\ Building\ Presentation.png') no-repeat center center fixed;
  background-size: cover;
}

:root {
  --grey: #2a2a2a;
  --blue: #1fb5bf;
}

.log-form {
  width: 40%;
  min-width: 320px;
  max-width: 475px;
  background: #fff;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.25);
}

@media (max-width: 640px) {
  .log-form {
    width: 95%;
    position: relative;
    margin: 2.5% auto 0 auto;
    left: 0;
    transform: translate(0, 0);
  }
}

.log-form form {
  display: block;
  width: 100%;
  padding: 2em;
}

.log-form h2 {
  width: 100%;
  color: #4d4d4d;
  font-family: 'Open Sans Condensed', sans-serif;
  font-size: 1.35em;
  display: block;
  background: var(--grey);
  text-transform: uppercase;
  padding: 0.75em 1em 0.75em 1.5em;
  box-shadow: inset 0px 1px 1px rgba(255, 255, 255, 0.05);
  border: 1px solid #262626;
  margin: 0;
  font-weight: 200;
}

.log-form input {
  display: block;
  width: 100%;
  margin-bottom: 2em;
  padding: 0.5em 0;
  border: none;
  border-bottom: 1px solid #eaeaea;
  padding-bottom: 1.25em;
  color: #757575;
}

.log-form input:focus {
  outline: none;
}

.log-form .btn {
  display: inline-block;
  background: var(--blue);
  border: 1px solid #1a9a9d;
  padding: 0.5em 2em;
  color: white;
  margin-right: 0.5em;
  box-shadow: inset 0px 1px 0px rgba(255, 255, 255, 0.2);
}

.log-form .btn:hover {
  background: #29c2cd;
}

.log-form .btn:active {
  background: var(--blue);
  box-shadow: inset 0px 1px 1px rgba(0, 0, 0, 0.1);
}

.log-form .btn:focus {
  outline: none;
}

.log-form .forgot {
  color: #35c4cf;
  line-height: 0.5em;
  position: relative;
  top: 2.5em;
  text-decoration: none;
  font-size: 0.75em;
  float: right;
}

.log-form .forgot:hover {
  color: #1a9a9d;
}
    </style>
</head>
<body>
<?php 
include ('loader.html');
?>
<?php if ($showToastr): ?>
        <script>
            $(document).ready(function() {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-bottom-right",
                    "timeOut": "5000"
                };
                <?php if ($result->num_rows === 0): ?>
                    toastr.error("Email not found in the database. Please try again.");
                <?php elseif (!$mail->send()): ?>
                    toastr.error("Failed to send OTP. Please try again.");
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>
<div class="log-form">
  <h2>Forgot Passwords?</h2>
<form method="POST" action="">
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send OTP</button>
</form>
</div>
</body>
</html>