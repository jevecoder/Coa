<?php
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

// Handle user creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $position = $_POST['position'];
    $section_id = $_POST['section_id'];
    $designation_id = $_POST['designation_id'];
    $section_heads_id = $_POST['section_heads_id'];
    $birthday = $_POST['birthday'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role_id = $_POST['role_id'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, number, position, role_id, section_heads_id, designation_id, section_id, birthday, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiiiss", $name, $email, $number, $position, $role_id, $section_heads_id, $designation_id, $section_id, $birthday, $password);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User created successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error creating user: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Handle user editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $number = $_POST['number'];
    $position = $_POST['position'];
    $role_id = $_POST['role_id'];
    $section_id = $_POST['section_id'];
    $designation_id = $_POST['designation_id'];
    $section_heads_id = $_POST['section_heads_id'];
    $birthday = $_POST['birthday'];

    $stmt = $conn->prepare("UPDATE users SET name=?, email=?, number=?, position=?, role_id=?, section_heads_id=?, designation_id=?, section_id=?, birthday=? WHERE id=?");
    $stmt->bind_param("ssssiiiisi", $name, $email, $number, $position, $role_id, $section_heads_id, $designation_id, $section_id, $birthday, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating user: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting user: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Handle user activation
if (isset($_GET['activate'])) {
    $id = $_GET['activate'];

    $stmt = $conn->prepare("UPDATE users SET status='active' WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User activated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error activating user: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: user_management.php");
    exit();
}

// Handle user deactivation
if (isset($_GET['deactivate'])) {
    $id = $_GET['deactivate'];

    $stmt = $conn->prepare("UPDATE users SET status='inactive' WHERE id=?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "User deactivated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deactivating user: " . $stmt->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: user_management.php");
    exit();
}
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
/* From Uiverse.io by alexruix */ 
.group {
 display: flex;
 line-height: 28px;
 align-items: center;
 position: relative;
 max-width: 190px;
}

.input {
 width: 20%;
 height: 40px;
 line-height: 28px;
 padding: 0 1rem;
 padding-left: 2.5rem;
 border: 2px solid transparent;
 border-radius: 8px;
 outline: none;
 background-color: #f3f3f4;
 color: #0d0c22;
 transition: .3s ease;
}

.input::placeholder {
 color: #9e9ea7;
}

.input:focus, input:hover {
 outline: none;
 border-color: rgba(234,76,137,0.4);
 background-color: #fff;
 box-shadow: 0 0 0 4px rgb(234 76 137 / 10%);
}

.icon {
 position: absolute;
 left: 1rem;
 fill: #9e9ea7;
 width: 1rem;
 height: 1rem;
}
    </style>
</head>

<body>
<?php 
include ('loader.html');
?>
<?php
// Check kung mayroong session message
if (isset($_SESSION['message']) && isset($_SESSION['message_type'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type']; // success, error, etc.

    // Linisin ang session message
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
?>
    <script>
        $(document).ready(function() {
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-bottom-right",
                "timeOut": "3000"
            };
            // Ipakita ang tamang uri ng toaster
            toastr["<?php echo $messageType; ?>"]("<?php echo $message; ?>");
        });
    </script>
<?php
}
?>
<?php if (isset($_SESSION['message'])): ?>
<script>
    // SweetAlert for major actions
    Swal.fire({
        icon: '<?php echo $_SESSION["message_type"]; ?>',
        title: '<?php echo $_SESSION["message"]; ?>',
        showConfirmButton: false,
        timer: 1500
    });

    // Toastr for non-critical updates
    toastr.options = {
        "closeButton": true,
        "progressBar": true
    };
    toastr["<?php echo $_SESSION['message_type']; ?>"]("<?php echo $_SESSION['message']; ?>");
</script>
<?php 
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
?>
<?php endif; ?>




    <main>

        <?php
        include('./nav.php');
        ?>

        <section style="z-index: -1;" class="hero-section d-flex justify-content-center align-items-center" id="section_1">
            </section>
            <!-- Display Message -->
<?php if (isset($_SESSION['message'])): ?>
    <p><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></p>
<?php endif; ?>

    <div class="d-flex justify-content-end" style="margin-right:20px;padding:10px" data-bs-toggle="modal" data-bs-target="#exampleModal">
    <button class="circle-plus-button buttonplus">
        <i class="fas fa-plus"></i>
    </button>
    </div>
<section class="p-2 table-responsive d-flex flex-column justify-content-center w-100" style="width: 100%;margin-top:-10%">
    <div style="z-index:5;" class="w-100 flex-column d-flex">
<!-- Display Users -->
<?php
$sql = "SELECT 
u.id, 
u.name, 
u.email, 
u.number, 
u.position, 
u.birthday, 
r.role_name, 
s.section_name, 
d.designations_name, 
sh.section_heads_name,
u.status  
FROM users u
LEFT JOIN roles r ON u.role_id = r.id
LEFT JOIN sections s ON u.section_id = s.id
LEFT JOIN designations d ON u.designation_id = d.id
LEFT JOIN section_heads sh ON u.section_heads_id = sh.id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<table class='table bg-white custom-table table-hover mb-0 align-self-center justify-content-center'>";
  echo "<thead class='table-header bg-dark'>
          <tr style='font-size: 10px;'>
              <th>Name</th>
              <th>Email</th>
              <th>Number</th>
              <th>Position</th>
              <th>Role</th>
              <th>Section</th>
              <th>Designation</th>
              <th>Section Heads</th>
              <th>Birthday</th>
              <th>Status</th>
          </tr>
        </thead>";
  echo "<tbody>";
  while ($user = $result->fetch_assoc()) {
      echo "<tr style='font-size:12px;text-align:center'>
          <td>" . $user['name'] . "</td>
          <td>" . $user['email'] . "</td>
          <td>" . $user['number'] . "</td>
          <td>" . $user['position'] . "</td>
          <td>" . $user['role_name'] . "</td>
          <td>" . $user['section_name'] . "</td>
          <td>" . $user['designations_name'] . "</td>
          <td>" . $user['section_heads_name'] . "</td>
          <td>" . $user['birthday'] . "</td>
          <td>";
      if ($user['status'] == 'active') {
          echo "Active | <a href='user_management.php?deactivate=" . $user['id'] . "'>Deactivate</a>";
      } else {
          echo "Inactive | <a href='user_management.php?activate=" . $user['id'] . "'>Activate</a>";
      }
      echo "</td>
      </tr>";
  }
  echo "</tbody></table>";
}
?>

</section>
<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- User Form -->
        <form action="user_management.php" class="p-2" method="POST">
          <input type="hidden" name="action" value="create">
          
          <div class="row g-3">
            <!-- First Row -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="name" class="form-label">Name:</label>
                <input type="text" id="name" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                <label for="number" class="form-label">Phone Number:</label>
                <input type="text" id="number" name="number" class="form-control">
              </div>
              <div class="mb-3">
                <label for="position" class="form-label">Position:</label>
                <input type="text" id="position" name="position" class="form-control" required>
              </div>
            </div>

            <!-- Second Row -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="role_name" class="form-label">Role:</label>
                <select id="role_name" name="role_id" class="form-select">
  <?php
  $role_query = "SELECT * FROM roles";
  $role_result = $conn->query($role_query);
  if ($role_result->num_rows > 0) {
    while ($row = $role_result->fetch_assoc()) {
      echo "<option value='" . $row['id'] . "'>" . $row['role_name'] . "</option>";
    }
  }
  ?>
</select>

              </div>
              <div class="mb-3">
                <label for="section_id" class="form-label">Section:</label>
                <select id="section_id" name="section_id" class="form-select">
                  <?php
                  $section_query = "SELECT * FROM sections";
                  $section_result = $conn->query($section_query);
                  if ($section_result->num_rows > 0) {
                    while ($row = $section_result->fetch_assoc()) {
                      echo "<option value='" . $row['id'] . "'>" . $row['section_name'] . "</option>";
                    }
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="section_heads_id" class="form-label">Section Head:</label>
                <select id="section_heads_id" name="section_heads_id" class="form-select">
                  <?php
                  $section_heads_query = "SELECT * FROM section_heads";
                  $section_heads_result = $conn->query($section_heads_query);
                  if ($section_heads_result->num_rows > 0) {
                    while ($row = $section_heads_result->fetch_assoc()) {
                      echo "<option value='" . $row['id'] . "'>" . $row['section_heads_name'] . "</option>";
                    }
                  }
                  ?>
                </select>
              </div>
            </div>

            <!-- Third Row -->
            <div class="col-md-4">
              <div class="mb-3">
                <label for="designation_id" class="form-label">Designation:</label>
                <select id="designation_id" name="designation_id" class="form-select">
                  <?php
                  $designation_query = "SELECT * FROM designations";
                  $designation_result = $conn->query($designation_query);
                  if ($designation_result->num_rows > 0) {
                    while ($row = $designation_result->fetch_assoc()) {
                      echo "<option value='" . $row['id'] . "'>" . $row['designations_name'] . "</option>";
                    }
                  }
                  ?>
                </select>
              </div>
              <div class="mb-3">
                <label for="birthday" class="form-label">Birthday:</label>
                <input type="date" id="birthday" name="birthday" class="form-control">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal for editing user -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- User Form -->
                <form action="user_management.php" class="p-2" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="user_id" name="id">

                    <div class="row g-3">
                        <!-- First Row -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name:</label>
                                <input type="text" id="edit_name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="edit_email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="number" class="form-label">Phone Number:</label>
                                <input type="text" id="edit_number" name="number" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label for="position" class="form-label">Position:</label>
                                <input type="text" id="edit_position" name="position" class="form-control" required>
                            </div>
                        </div>

                        <!-- Second Row -->
                        <div class="col-md-4">
                            <!-- Role Dropdown -->
<div class="mb-3">
    <label for="role_name" class="form-label">Role:</label>
    <select id="edit_role" name="role_name" class="form-select">
        <?php
        // Populate roles from the database
        $role_query = "SELECT * FROM roles";  // Example query, change based on your structure
        $role_result = mysqli_query($conn, $role_query);
        while ($role = mysqli_fetch_assoc($role_result)) {
            $selected = ($role['role_name'] == $user['role_name']) ? "selected" : "";
            echo "<option value='" . $role['role_name'] . "' $selected>" . $role['role_name'] . "</option>";
        }
        ?>
    </select>
</div>
                            <!-- Section Dropdown -->
<div class="mb-3">
    <label for="section_id" class="form-label">Section:</label>
    <select id="edit_section" name="section_id" class="form-select">
        <?php
        // Populate sections from the database
        $section_query = "SELECT * FROM sections";  // Example query, change based on your structure
        $section_result = mysqli_query($conn, $section_query);
        while ($section = mysqli_fetch_assoc($section_result)) {
            $selected = ($section['section_name'] == $user['section_name']) ? "selected" : "";
            echo "<option value='" . $section['section_name'] . "' $selected>" . $section['section_name'] . "</option>";
        }
        ?>
    </select>
</div>
                            <!-- Section Head Dropdown -->
<div class="mb-3">
    <label for="section_heads_id" class="form-label">Section Head:</label>
    <select id="edit_section_head" name="section_heads_id" class="form-select">
        <?php
        // Populate section heads from the database
        $section_head_query = "SELECT * FROM section_heads";  // Example query, change based on your structure
        $section_head_result = mysqli_query($conn, $section_head_query);
        while ($section_head = mysqli_fetch_assoc($section_head_result)) {
            $selected = ($section_head['section_heads_name'] == $user['section_heads_name']) ? "selected" : "";
            echo "<option value='" . $section_head['section_heads_name'] . "' $selected>" . $section_head['section_heads_name'] . "</option>";
        }
        ?>
    </select>
</div>
                        </div>

                        <!-- Third Row -->
                        <div class="col-md-4">
                            <!-- Designation Dropdown -->
<div class="mb-3">
    <label for="designation_id" class="form-label">Designation:</label>
    <select id="edit_designation" name="designation_id" class="form-select">
        <?php
        // Populate designations from the database
        $designation_query = "SELECT * FROM designations";  // Example query, change based on your structure
        $designation_result = mysqli_query($conn, $designation_query);
        while ($designation = mysqli_fetch_assoc($designation_result)) {
            $selected = ($designation['designations_name'] == $user['designations_name']) ? "selected" : "";
            echo "<option value='" . $designation['designations_name'] . "' $selected>" . $designation['designations_name'] . "</option>";
        }
        ?>
    </select>
</div>
                            <div class="mb-3">
                                <label for="birthday" class="form-label">Birthday:</label>
                                <input type="date" id="edit_birthday" name="birthday" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="edit_password" name="password" class="form-control">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Update User</button>
                </form>
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
    <script>
function confirmDelete(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirect to the delete URL
            window.location.href = "user_management.php?delete=" + userId;
        }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Populate edit modal with user data
    const editUserModal = document.getElementById('editUserModal');
    editUserModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        const userId = button.getAttribute('data-id');
        const name = button.getAttribute('data-name');
        const email = button.getAttribute('data-email');
        const number = button.getAttribute('data-number');
        const position = button.getAttribute('data-position');
        const role = button.getAttribute('data-role');
        const section = button.getAttribute('data-section');
        const designation = button.getAttribute('data-designation');
        const sectionHead = button.getAttribute('data-section_head');
        const birthday = button.getAttribute('data-birthday');

        // Set values in the modal form
        document.getElementById('user_id').value = userId;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_number').value = number;
        document.getElementById('edit_position').value = position;
        document.getElementById('edit_role').value = role;
        document.getElementById('edit_section').value = section;
        document.getElementById('edit_designation').value = designation;
        document.getElementById('edit_section_head').value = sectionHead;
        document.getElementById('edit_birthday').value = birthday;
    });
</script>


</body>

</html>