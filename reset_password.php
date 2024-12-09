<?php
include ('conn.php');

// If the user is not redirected from the OTP verification page, redirect them back
if (!isset($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit;
}

$showToastr = false;
$errorMessage = '';

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_SESSION['otp_email']; // Retrieve email from session
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Ensure passwords match
    if ($new_password !== $confirm_password) {
        $errorMessage = 'Passwords do not match.';
        $showToastr = true;
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the password and clear the OTP fields
        $sql = "UPDATE users SET password = ?, otp = NULL, otp_expiry = NULL WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $hashed_password, $email);

        if ($stmt->execute()) {
            // Success message
            $showToastr = true;
            $errorMessage = 'Password reset successfully.';
            session_unset(); // Clear session data
            session_destroy(); // End the session
            header("Location: index.php?status=password_changed"); // Redirect to the login page with status
            exit;
        } else {
            $errorMessage = 'Failed to reset password.';
            $showToastr = true;
        }

        $stmt->close();
        $conn->close();
    }
}
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
                <?php if ($errorMessage): ?>
                    toastr.error("<?php echo $errorMessage; ?>");
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <div class="container mx-auto p-8 log-form">
        <h2 class="text-2xl font-bold mb-4">Reset Your Password</h2>
        <form method="POST">
            <div class="mb-4">
                <label for="new_password" class="block">New Password</label>
                <input type="password" id="new_password" name="new_password" class="border p-2 w-full" required>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="block">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="border p-2 w-full" required>
            </div>

            <div>
                <button type="submit" class="bg-blue-500 text-white p-2 rounded">Reset Password</button>
            </div>
        </form>
    </div>

</body>
</html>
