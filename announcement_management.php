<?php
// Include database connection and session
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

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];  // Get user_id from session
    $showToastr = true;  // User is logged in, show toast notification
} else {
    $showToastr = false;
    die("User is not logged in.");
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $second_title = mysqli_real_escape_string($conn, $_POST['second_title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $date_start = mysqli_real_escape_string($conn, $_POST['date_start']);
    $date_end = mysqli_real_escape_string($conn, $_POST['date_end']);

    // Validate if user_id is set
    if (empty($user_id)) {
        die("User ID is not set. Please log in.");
    }

    // Handle image upload (Check if image is uploaded first)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image = $_FILES['image'];
        $image_name = $image['name'];
        $image_tmp_name = $image['tmp_name'];
        $image_error = $image['error'];
        $image_size = $image['size'];

        // Validate image upload
        if ($image_error === 0) {
            if ($image_size <= 2000000) {  // Limit file size to 2MB
                // Rename the file to avoid conflicts
                $image_new_name = uniqid('', true) . '.' . pathinfo($image_name, PATHINFO_EXTENSION);
                $image_upload_path = 'uploads/' . $image_new_name;  // Save in 'uploads/' folder

                // Upload image to server
                if (move_uploaded_file($image_tmp_name, $image_upload_path)) {
                    // Insert announcement with image path
                    $stmt = $conn->prepare("INSERT INTO announcements (user_id, title, second_title, description, date_start, date_end, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("issssss", $user_id, $title, $second_title, $description, $date_start, $date_end, $image_upload_path);

                    if ($stmt->execute()) {
                        // Success: Store message and type for toast
                        $_SESSION['toast_message'] = "Announcement created successfully!";
                        $_SESSION['toast_type'] = "success";
                        header("Location: announcement_management.php");
                        exit;
                    } else {
                        // Error: Store message and type for toast
                        $_SESSION['toast_message'] = "Error creating announcement.";
                        $_SESSION['toast_type'] = "error";
                        header("Location: announcement_management.php");
                        exit;
                    }

                } else {
                    $_SESSION['toast_message'] = "Error uploading image.";
                    $_SESSION['toast_type'] = "error";
                    header("Location: announcement_management.php");
                    exit;
                }
            } else {
                $_SESSION['toast_message'] = "Image size is too large. Please upload an image smaller than 2MB.";
                $_SESSION['toast_type'] = "error";
                header("Location: announcement_management.php");
                exit;
            }
        } else {
            $_SESSION['toast_message'] = "Error uploading image.";
            $_SESSION['toast_type'] = "error";
            header("Location: announcement_management.php");
            exit;
        }
    } else {
        // If no image was uploaded, insert without the image URL
        $stmt = $conn->prepare("INSERT INTO announcements (user_id, title, second_title, description, date_start, date_end) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $user_id, $title, $second_title, $description, $date_start, $date_end);

        if ($stmt->execute()) {
            $_SESSION['toast_message'] = "Announcement created successfully!";
            $_SESSION['toast_type'] = "success";
            header("Location: announcement_management.php");
            exit;
        } else {
            $_SESSION['toast_message'] = "Error creating announcement.";
            $_SESSION['toast_type'] = "error";
            header("Location: announcement_management.php");
            exit;
        }
        
    }
}

// Check if 'id' and 'status' are set via GET
if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = (int) $_GET['id'];  // Ensure ID is an integer
    $status = $_GET['status']; // Status can be 'active' or 'inactive'

    // Validate status
    if ($status == 'active' || $status == 'inactive') {
        // Update the status in the database
        $sql = "UPDATE announcements SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $status, $id);  // 's' for string, 'i' for integer
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // If the status was updated successfully, redirect back
            header("Location: announcement_management.php");  // Or wherever the announcements are displayed
            exit;
        } else {
            // If the update fails
            echo "Error updating status.";
        }
    } else {
        echo "Invalid status.";
    }
}

// Fetching all announcements from the database
$sql = "SELECT * FROM announcements ORDER BY date_start DESC";
$result = $conn->query($sql);
?>






<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>COA TSO Special Services Assignment Tracker</title>
    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/coa.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.4.4/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
#customers {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: center;
  color: white;
  font-size: 10px;
}

/* Add your custom styles */
.action-buttons {
            display: none;
        }
        .edit-container {
            display: flex;
            gap: 5px;
        }
        .edit-input {
            width: 100%;
            padding: 5px;
            font-size: 14px;
        }
        .save-btn {
            background-color: #4CAF50;
            color: white;
        }
        .cancel-btn {
            background-color: #f44336;
            color: white;
        }
        .buttonplus {
    background-color: #4CAF50; /* Green */
    border: none;
    color: white;
    padding: 15px 32px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    font-size: 16px;
    position: fixed; /* Fixed position */
    bottom: 20px; /* Distance mula sa ibaba ng screen */
    right: 20px; /* Distance mula sa kanan ng screen */
    z-index: 1000; /* Para siguraduhing nasa ibabaw ito ng ibang elements */
    cursor: pointer; /* Maglagay ng pointer cursor kapag hover */
    border-radius: 5px; /* Optional: Gawing rounded ang button */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Optional: Magdagdag ng shadow */
}

        /* Style for the circular button */
.circle-plus-button {
    background-color: #4CAF50; /* Green background */
    color: white; /* White icon color */
    border: none; /* Remove border */
    border-radius: 50%; /* Make the button circular */
    width: 50px; /* Button width */
    height: 50px; /* Button height */
    display: flex; /* Center the icon */
    align-items: center;
    justify-content: center;
    cursor: pointer; /* Add a pointer cursor on hover */
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2); /* Add shadow for depth */
    font-size: 20px; /* Icon size */
    transition: background-color 0.3s ease, transform 0.2s ease; /* Add hover effects */
}

.circle-plus-button:hover {
    background-color: #45a049; /* Slightly darker green on hover */
    transform: scale(1.1); /* Slightly increase size on hover */
}

.circle-plus-button:active {
    background-color: #3e8e41; /* Even darker green on click */
    transform: scale(1); /* Reset size on click */
}
.table-container {
            border-radius: 10px; /* Rounded corners */
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            overflow: hidden; /* Ensures the table corners are rounded */
        }
        .custom-table {
            border-radius: 10px;
            margin: 0; /* Remove margins around the table */
            width: 95%;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow */
            overflow: hidden; /* Ensures the table corners are rounded */
        }
        .table-header {
            background-color: #000; /* Custom header color */
            color: white; /* White text */
            text-align: center;
        }
</style>
</head>

<body>
<?php 
include ('loader.html');
?>
    <main>

        <?php
        include('./nav.php');
        ?>
<!-- Display Toast Notifications -->
<?php if (isset($_SESSION['toast_message']) && isset($_SESSION['toast_type'])): ?>
            <script>
                window.onload = function() {
                    toastr.<?php echo $_SESSION['toast_type']; ?>("<?php echo $_SESSION['toast_message']; ?>");
                };
            </script>
            <?php 
                unset($_SESSION['toast_message']);
                unset($_SESSION['toast_type']);
            ?>
        <?php endif; ?>

        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
            </section>

            <section class="p-2 table-responsive d-flex flex-column justify-content-center w-100" style="width: 100%;margin-top:-10%">
                <div style="z-index:5;" class="w-100 flex-column d-flex">
                <?php
$result = $conn->query("SELECT a.id, u.name AS user, a.title, a.second_title, a.description, a.date_start, a.date_end, a.updated_at, a.image, a.status 
FROM announcements a 
JOIN users u ON a.user_id = u.id 
ORDER BY a.updated_at DESC");

if (!$result) {
    die("Error in query execution: " . $conn->error);
}
?>
<table class="table bg-white custom-table table-hover mb-0 align-self-center justify-content-center">
    <thead class='table-header bg-dark'>
        <tr style="font-size: 10px;">
            <th>ID</th>
            <th>User</th>
            <th>Title</th>
            <th>Second Title</th>
            <th>Description</th>
            <th>Date Start</th>
            <th>Date End</th>
            <th>Updated At</th>
            <th>Image</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr style="font-size:12px;text-align:center">
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['user']) ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['second_title']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= htmlspecialchars($row['date_start']) ?></td>
            <td><?= htmlspecialchars($row['date_end']) ?></td>
            <td><?= htmlspecialchars($row['updated_at']) ?></td>
            <td>
                <?php 
                    // Check if image exists
                    if (!empty($row['image'])): 
                ?>
                    <!-- Display the image if there is a valid image URL -->
                    <img src="<?= htmlspecialchars($row['image']) ?>" alt="Announcement Image" style="max-width: 100px; height: auto;">
                <?php else: ?>
                    <!-- If no image URL exists, display a default message -->
                    No image available
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($row['status']) ?></td>
            <td class="border px-4 py-2">
                <?php if ($row['status'] == 'active'): ?>
                    <!-- 'Active' is displayed as disabled -->
                    <span class="text-green-600 cursor-not-allowed">Active</span> | 
                    <!-- 'Inactive' link is clickable -->
                    <a href="announcement_management.php?id=<?= $row['id']; ?>&status=inactive" class="text-red-600">Inactive</a>
                <?php else: ?>
                    <!-- 'Inactive' is displayed as disabled -->
                    <span class="text-green-600 cursor-not-allowed">Inactive</span> | 
                    <!-- 'Active' link is clickable -->
                    <a href="announcement_management.php?id=<?= $row['id']; ?>&status=active" class="text-red-600">Active</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="11" style="text-align: center;">No Announcement Created.</td>
        </tr>
    <?php endif; ?>
</table>

</div>
</section>


<div class="d-flex justify-content-end" style="margin-right:20px;padding:10px" data-bs-toggle="modal" data-bs-target="#plusModal">
    <button class="circle-plus-button buttonplus">
        <i class="fas fa-plus"></i>
    </button>
    </div>

<!-- Modal -->
<div class="modal fade" id="plusModal" tabindex="-1" aria-labelledby="plusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="plusModalLabel">Add Announcement</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          
        <form action="announcement_management.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="title" class="form-label">Title</label>
        <input type="text" class="form-control" id="title" name="title" required>
    </div>
    <div class="mb-3">
        <label for="second_title" class="form-label">Second Title</label>
        <input type="text" class="form-control" id="second_title" name="second_title">
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="date_start" class="form-label">Start Date</label>
        <input type="date" class="form-control" id="date_start" name="date_start" required>
    </div>
    <div class="mb-3">
        <label for="date_end" class="form-label">End Date</label>
        <input type="date" class="form-control" id="date_end" name="date_end" required>
    </div>
    <label for="image">Upload Image:</label>
    <input type="file" name="image" accept="image/*">
    <button type="submit">Upload Image</button>
</form>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>


        <?php
        include('./footer.php');
        ?>

        
    </main>

<?php
include ('./modals.php');
?>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/click-scroll.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/clock.js"></script>
    <script src="js/table.js"></script>
    <script src="js/logout_alert.js"></script>
</body>

</html>