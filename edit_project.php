<?php
include 'conn.php';

// Fetch the project ID from the URL (or form, if necessary)
if (isset($_GET['id'])) {
    $project_id = intval($_GET['id']);
} else {
    $_SESSION['error_message'] = 'Project ID is missing!';
    header("Location: project.php");
    exit;
}

// Fetch the project details from the database
$query = "SELECT * FROM projects WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();

// Check if the project exists
if (!$project) {
    $_SESSION['error_message'] = 'Project not found!';
    header("Location: project.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contractor_project = trim($_POST['contractor_project']);
    $user_id = intval($_POST['user']);
    $subjects = trim($_POST['subjects']);
    $document_no = trim($_POST['document_no']);
    $date_received = $_POST['date_received'];
    $date_assigned = $_POST['date_assigned'];
    $agency = trim($_POST['agency']);
    $supervisor = intval($_POST['supervisor']);
    $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Update the projects table
        $stmt = $conn->prepare("UPDATE projects SET contractor_project = ?, user_id = ?, subjects = ?, document_no = ?, date_received = ?, date_assigned = ?, agency = ?, supervisor = ?, collaborators = ? WHERE id = ?");
        $collaborators_str = implode(',', $collaborators); // Convert collaborators to string
        $stmt->bind_param("sssssssssi", $contractor_project, $user_id, $subjects, $document_no, $date_received, $date_assigned, $agency, $supervisor, $collaborators_str, $project_id);
        if (!$stmt->execute()) {
            throw new Exception("Error updating project: " . $stmt->error);
        }

        // Update project_tasks table
        $task_stmt = $conn->prepare("UPDATE project_tasks SET contractor_project = ?, supervisor = ?, collaborators = ?, user_id = ? WHERE project_id = ?");
        $task_stmt->bind_param("ssssi", $contractor_project, $supervisor, $collaborators_str, $user_id, $project_id);
        if (!$task_stmt->execute()) {
            throw new Exception("Error updating project task: " . $task_stmt->error);
        }

        // Update the supervisor in project_users table
        $supervisor_stmt = $conn->prepare("UPDATE project_users SET user_id = ? WHERE project_id = ? AND user_id = ?");
        $supervisor_stmt->bind_param("iii", $supervisor, $project_id, $supervisor); // Update the supervisor record
        if (!$supervisor_stmt->execute()) {
            throw new Exception("Error updating supervisor: " . $supervisor_stmt->error);
        }

        // Remove users from project_users table who are no longer collaborators
        if (!empty($collaborators)) {
            // Get current collaborators from project_users table
            $current_collaborators_stmt = $conn->prepare("SELECT user_id FROM project_users WHERE project_id = ?");
            $current_collaborators_stmt->bind_param("i", $project_id);
            $current_collaborators_stmt->execute();
            $current_collaborators_result = $current_collaborators_stmt->get_result();
            $current_collaborators = [];
            while ($row = $current_collaborators_result->fetch_assoc()) {
                $current_collaborators[] = $row['user_id'];
            }

            // Find the users who are no longer collaborators
            $to_remove = array_diff($current_collaborators, $collaborators);

            // Remove users who are no longer collaborators
            if (!empty($to_remove)) {
                $remove_stmt = $conn->prepare("DELETE FROM project_users WHERE project_id = ? AND user_id = ?");
                foreach ($to_remove as $remove_user_id) {
                    $remove_stmt->bind_param("ii", $project_id, $remove_user_id);
                    if (!$remove_stmt->execute()) {
                        throw new Exception("Error removing user from project_users: " . $remove_stmt->error);
                    }
                }
            }
        }

        // Commit the transaction
        $conn->commit();

        // Redirect with success message
        $_SESSION['message'] = 'Project updated successfully!';
        header("Location: project.php");
        exit;

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Store error message in session and redirect back
        $_SESSION['error_message'] = 'Error updating project: ' . $e->getMessage();
        header("Location: edit_project.php?id=" . $project_id); // Redirect to the edit page
        exit;
    }
}
$date_received = substr($project['date_received'], 0, 10); // Get the date part
$date_assigned = substr($project['date_assigned'], 0, 10);
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
    body {
  font-family: 'Open Sans', helvetica, arial, sans-serif;
  background: url('./images/Yellow\ Modern\ The\ Building\ Presentation.png') no-repeat center center fixed;
  background-size: cover;
}
.scrollable-form {
    max-height: 80vh; /* Limitahan ang taas ng form sa 80% ng view height */
    overflow-y: auto; /* Gawin itong scrollable kung sumobra sa max height */
    padding: 15px;
    background: rgba(255, 255, 255, 0.9); /* Magdagdag ng background para sa readability */
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
  </style>
</head>

<body>
<!-- Form to edit project -->
<form method="POST" action="edit_project.php?id=<?php echo $project_id; ?>" class="container mt-4 p-4 border rounded shadow-sm scrollable-form">
    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">

    <div class="mb-3">
<label for="contractor_project" class="form-label">Contractor/Project:</label>
    <input type="text" class="form-control" name="contractor_project" value="<?php echo htmlspecialchars($project['contractor_project']); ?>" placeholder="Contractor / Project" required>
    </div>

    <div class="mb-3">
    <label for="user" class="form-label">Action Officer</label>
    <select name="user" class="form-select" required>
        <option value="">Select User</option>
        <?php
        $result_users = $conn->query("SELECT id, name FROM users WHERE role_id = 5");
        if ($result_users && $result_users->num_rows > 0) {
            while ($row = $result_users->fetch_assoc()) {
                // Set the selected user as the current user
                $selected = $row['id'] == $project['user_id'] ? 'selected' : ''; // Check if this user is assigned to the project
                echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
            }
        } else {
            echo "<option value=''>No users available</option>";
        }
        ?>
    </select>
</div>


    <div class="mb-3">
<label for="subject" class="form-label">Subject</label>
    <textarea name="subjects" class="form-control" placeholder="Subject" required><?php echo htmlspecialchars($project['subjects']); ?></textarea>
    </div>

    <div class="mb-3">
    <label for="document_no" class="form-label">Contractor/Project:</label>
    <input type="text" name="document_no" class="form-control" value="<?php echo htmlspecialchars($project['document_no']); ?>" placeholder="Document No." required>
    </div>
    
    <div class="mb-3">
    <label for="date_received" class="form-label">Date Received</label>
    <input type="date" name="date_received" class="form-control" value="<?php echo htmlspecialchars($date_received); ?>" required>
</div>

<div class="mb-3">
    <label for="date_assigned" class="form-label">Date Assigned</label>
    <input type="date" name="date_assigned" class="form-control" value="<?php echo htmlspecialchars($date_assigned); ?>" required>
</div>
    
    <div class="mb-3">
<label for="agency" class="form-label">Agency</label>
    <input type="text" name="agency" class="form-control" value="<?php echo htmlspecialchars($project['agency']); ?>" placeholder="Agency" required>
    </div>

    <div class="mb-3">
    <label for="supervisor" class="form-label">Supervisor</label>
    <select name="supervisor" class="form-select" required>
        <option value="">Select Supervisor</option>
        <?php
        $result_supervisors = $conn->query("SELECT id, name FROM users WHERE role_id = 4"); // Assuming 'role_id = 4' is for supervisor
        if ($result_supervisors && $result_supervisors->num_rows > 0) {
            while ($row = $result_supervisors->fetch_assoc()) {
                // Set the selected supervisor as the current supervisor
                $selected = $row['id'] == $project['supervisor'] ? 'selected' : '';
                echo "<option value='" . $row['id'] . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
            }
        } else {
            echo "<option value=''>No supervisors available</option>";
        }
        ?>
    </select>
</div>

    <div class="mb-3">
    <label for="collaborators" class="form-label">Collaborators</label>
    <?php
    $result_collaborators = $conn->query("SELECT id, name FROM users");
    if ($result_collaborators && $result_collaborators->num_rows > 0) {
        $collaborators_array = explode(',', $project['collaborators']);
        while ($row = $result_collaborators->fetch_assoc()) {
            // Check if this user is a collaborator
            $checked = in_array($row['id'], $collaborators_array) ? 'checked' : '';
            echo "<div>
                    <input type='checkbox' name='collaborators[]' value='" . $row['id'] . "' $checked> 
                    " . htmlspecialchars($row['name']) . "
                  </div>";
        }
    } else {
        echo "<p>No collaborators available</p>";
    }
    ?>
    </div>

    <button type="submit">Update Project</button>
</form>
</body>
</html>