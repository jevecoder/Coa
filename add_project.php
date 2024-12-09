<?php
include 'conn.php';

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

        // Insert into the projects table
        $stmt = $conn->prepare("INSERT INTO projects (contractor_project, user_id, subjects, document_no, date_received, date_assigned, agency, supervisor, collaborators) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $collaborators_str = implode(',', $collaborators); // Convert collaborators to string
        $stmt->bind_param("sssssssss", $contractor_project, $user_id, $subjects, $document_no, $date_received, $date_assigned, $agency, $supervisor, $collaborators_str);
        if (!$stmt->execute()) {
            throw new Exception("Error inserting project: " . $stmt->error);
        }

        // Get the newly inserted project ID
        $project_id = $conn->insert_id;

        // Insert into the project_tasks table
        $task_stmt = $conn->prepare("INSERT INTO project_tasks (contractor_project, supervisor, project_id, collaborators, user_id) VALUES (?, ?, ?, ?, ?)");
$collaborators_str = implode(',', $collaborators); // Convert collaborators to string
$task_stmt->bind_param("ssisi", $contractor_project, $supervisor, $project_id, $collaborators_str, $user_id);
if (!$task_stmt->execute()) {
    throw new Exception("Error inserting project task: " . $task_stmt->error);
}


        // Get the newly created task ID (this can be used if needed later)
        $task_id = $conn->insert_id;

        // Insert supervisor into project_users table
        $supervisor_stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id, project_tasks_id) VALUES (?, ?, ?)");
        $supervisor_stmt->bind_param("iii", $project_id, $supervisor, $task_id);
        if (!$supervisor_stmt->execute()) {
            throw new Exception("Error assigning supervisor: " . $supervisor_stmt->error);
        }

        // Insert collaborators into project_users table
        if (!empty($collaborators)) {
            $collaborator_stmt = $conn->prepare("INSERT INTO project_users (project_id, user_id, project_tasks_id) VALUES (?, ?, ?)");
            foreach ($collaborators as $collaborator_id) {
                $collaborator_stmt->bind_param("iii", $project_id, $collaborator_id, $task_id);
                if (!$collaborator_stmt->execute()) {
                    throw new Exception("Error assigning collaborator: " . $collaborator_stmt->error);
                }
            }
        }

        // Commit the transaction
        $conn->commit();

        // Redirect with success message
        $_SESSION['message'] = 'Project and task added successfully!';
        header("Location: project.php");
        exit;

    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();

        // Store error message in session and redirect back
        $_SESSION['error_message'] = 'Error adding project: ' . $e->getMessage();
        header("Location: add_project.php");
        exit;
    }
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
<!-- Form to add project -->
<form method="POST" action="add_project.php" class="container mt-4 p-4 border rounded shadow-sm scrollable-form">
<div class="mb-3">
<label for="user" class="form-label">Contractor Project</label>
    <input type="text" class="form-control" name="contractor_project" placeholder="Contractor / Project" required>
</div>

<div class="mb-3">
    <label for="user" class="form-label">Action Officer</label>
    <select name="user" class="form-select" required>
        <option value="">Select User</option>
        <?php
        $result_action_officers = $conn->query("
            SELECT u.id, u.name 
            FROM users u 
            INNER JOIN roles r ON u.role_id = r.id 
            WHERE r.role_name = 'action officer'
        ");
        if ($result_action_officers && $result_action_officers->num_rows > 0) {
            while ($row = $result_action_officers->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
            }
        } else {
            echo "<option value=''>No action officers available</option>";
        }
        ?>
    </select>
</div>


    <div class="mb-3">
    <label for="user" class="form-label">Subject</label>
    <textarea name="subjects" class="form-control" placeholder="Subject" required></textarea>
    </div>

    <div class="mb-3">
    <label for="user" class="form-label">Document No.</label>
    <input type="text" class="form-control" name="document_no" placeholder="Document No." required>
    </div>

    <div class="mb-3">
    <label for="user" class="form-label">Date Received</label>
    <input type="date" class="form-control" name="date_received" required>
    </div>
    
    <div class="mb-3">
    <label for="user" class="form-label">Date Assigned</label>
    <input type="date" class="form-control" name="date_assigned" required>
    </div>
    
    <div class="mb-3">
    <label for="user" class="form-label">Agency</label>
    <input type="text" class="form-control" name="agency" placeholder="Agency" required>
    </div>

    <div class="mb-3">
    <label for="supervisor" class="form-label">Supervisor</label>
    <select name="supervisor" class="form-select" required>
        <option value="">Select Supervisor</option>
        <?php
        $result_supervisors = $conn->query("
            SELECT u.id, u.name 
            FROM users u 
            INNER JOIN roles r ON u.role_id = r.id 
            WHERE r.role_name = 'supervisor'
        ");
        if ($result_supervisors && $result_supervisors->num_rows > 0) {
            while ($row = $result_supervisors->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['name']) . "</option>";
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
        while ($row = $result_collaborators->fetch_assoc()) {
            echo "<div>
                    <input type='checkbox' name='collaborators[]' value='" . $row['id'] . "'> 
                    " . htmlspecialchars($row['name']) . "
                  </div>";
        }
    } else {
        echo "<p>No collaborators available</p>";
    }
    ?>
    </div>

    <button type="submit">Add Project</button>
</form>
</body>
</html>