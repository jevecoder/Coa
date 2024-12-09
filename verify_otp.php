<?php
include ('conn.php');

// Check if 'otp_email' is set in the session
if (!isset($_SESSION['otp_email'])) {
    echo "<script>alert('Email is not set. Please go back and request OTP.'); window.location.href = 'forgot_password.php';</script>";
    exit; // Stop execution if session variable is not set
}

$email = $_SESSION['otp_email']; // Email saved from forgot password step

$showToastr = false; // Flag for toastr
$otp_expiry = 0; // Set default expiry time
$otp_remaining_time = 0; // Default remaining time in seconds

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp_entered = $_POST['otp']; // OTP entered by user
    $current_time = time(); // Current timestamp

    // Retrieve the OTP and expiry time from the database
    $sql = "SELECT otp, otp_expiry FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $stored_otp = $user['otp'];
        $otp_expiry = $user['otp_expiry'];
        $otp_remaining_time = $otp_expiry - $current_time; // Calculate remaining time

        // Check if OTP is correct and not expired
        if ($otp_entered == $stored_otp) {
            if ($current_time <= $otp_expiry) {
                // OTP is valid and not expired
                $showToastr = true; // Show success toastr
                $_SESSION['otp_verified'] = true; // OTP verified
                header("Location: reset_password.php");
                exit; // Ensure the script stops execution after redirect
            } else {
                // OTP expired
                $showToastr = true;
                $toastr_message = "OTP has expired. Please try again.";
                $toastr_type = "error";
            }
        } else {
            // Invalid OTP
            $showToastr = true;
            $toastr_message = "Invalid OTP. Please try again.";
            $toastr_type = "error";
        }
    } else {
        $showToastr = true;
        $toastr_message = "Email not found or OTP not sent. Please try again.";
        $toastr_type = "error";
    }

    $stmt->close();
    $conn->close();
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
            toastr.<?php echo $toastr_type; ?>("<?php echo $toastr_message; ?>");
        });

        // Countdown and progress bar
        var remainingTime = <?php echo $otp_remaining_time; ?>;
        var countdownInterval = setInterval(function() {
            if (remainingTime <= 0) {
                clearInterval(countdownInterval);
                toastr.error("OTP has expired.");
            } else {
                remainingTime--;
                var progress = (remainingTime / <?php echo $otp_expiry - time(); ?>) * 100;
                // Update progress bar
                $("#progress-bar").css("width", progress + "%");
            }
        }, 1000);
    </script>
<?php endif; ?>
<div class="log-form">
        <h2>Enter OTP</h2>
        <form method="POST">
            <input type="text" name="otp" required placeholder="Enter OTP">
            <button type="submit">Verify</button>
            <div class="progress" style="height: 20px;">
                <div id="progress-bar" class="progress-bar" style="width: 0%; background-color: green;"></div>
            </div>
    
            <!-- Timer Display -->
            <div id="timer" style="margin-top: 10px;">Time remaining: <span id="time-left"></span></div>
        </form>
    </div>
    <script>
        // Initialize the OTP expiry time from PHP
        var otpExpiry = <?php echo $otp_expiry; ?>; // Expiry time from PHP
        var currentTime = Math.floor(Date.now() / 1000); // Current time in seconds
        var remainingTime = otpExpiry - currentTime; // Remaining time in seconds

        // Calculate the total duration (5 minutes in seconds)
        var totalDuration = 300;

        // Function to update the progress bar and timer
        function updateProgressBar() {
            if (remainingTime <= 0) {
                document.getElementById('progress-bar').style.width = '100%';
                document.getElementById('timer').innerHTML = "OTP expired!";
                return; // Stop the function when expired
            }

            // Calculate percentage of time left
            var percentageLeft = (remainingTime / totalDuration) * 100;
            document.getElementById('progress-bar').style.width = percentageLeft + '%';

            // Update timer text
            var minutes = Math.floor(remainingTime / 60);
            var seconds = remainingTime % 60;
            document.getElementById('time-left').innerHTML = minutes + "m " + seconds + "s";

            // Decrease the remaining time every second
            remainingTime--;
        }

        // Update the progress bar every second
        setInterval(updateProgressBar, 1000);

    </script>
</body>
</html>
