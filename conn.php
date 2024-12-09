<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'coa_assignment_tracking';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();


function log_login_activity($user_id) {
    global $conn;


    $ip_address = $_SERVER['REMOTE_ADDR'];


    $query = "INSERT INTO login_logs (user_id, action, ip_address) VALUES (?, 'login', ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $ip_address);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && isset($_POST['password']) && !empty($_POST['email']) && !empty($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            echo "Error: " . $conn->error;
        } else {
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    if ($user['status'] === 'active') {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['role_id'] = $user['role_id'];

                        log_login_activity($user['id']);
                        $_SESSION['login_success'] = true;

                        header("Location: index.php");
                        exit;
                    } else {
                        echo "<script>alert('Your account is inactive. Please contact the administrator.');</script>";
                    }
                } else {
                    echo "<script>alert('Invalid email or password. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Invalid email or password. Please try again.');</script>";
            }

            $stmt->close();
        }
    } else {
        echo "<script>alert('Please provide email and password');</script>";
    }
}

?>
