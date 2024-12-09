<?php
// Include database connection
include('conn.php');

$sql = "SELECT id FROM roles WHERE role_name = 'admin'"; // Assuming 'admin' is the name of the role
$result = $conn->query($sql);

$admin_roles = [];
while ($row = $result->fetch_assoc()) {
    $admin_roles[] = $row['id']; // Store admin role ids
}

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role_id'], $admin_roles)) {
    echo "Access Denied.";
    exit;
}

// Query para kunin lahat ng login logs
$query = "SELECT login_logs.*, users.name AS user_name FROM login_logs 
          JOIN users ON login_logs.user_id = users.id 
          ORDER BY login_logs.timestamp DESC";
$result = $conn->query($query);
?>


    <div class="container mt-5">
        <h2>Login Logs</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Action</th>
                    <th>IP Address</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Check if there are logs in the database
                if ($result->num_rows > 0) {
                    // Loop through each log and display it
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['id'] . "</td>";
                        echo "<td>" . $row['user_name'] . "</td>";
                        echo "<td>" . $row['action'] . "</td>";
                        echo "<td>" . $row['ip_address'] . "</td>";
                        echo "<td>" . $row['timestamp'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='text-center'>No login logs found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- Optional: You can include JavaScript frameworks like jQuery or Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
