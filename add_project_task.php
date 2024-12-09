<?php
include('conn.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "User is not logged in.";
    exit();
}

// Fetch logged-in user's name
$user_id = $_SESSION['user_id'];
$sql_user = "SELECT name FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_name = ""; // Default value in case no user is found

if ($result_user->num_rows > 0) {
    $user_data = $result_user->fetch_assoc();
    $user_name = $user_data['name']; // Assign the user's name
}

$stmt_user->close();

// Fetch users for collaboration and supervisor options
$sql_users = "SELECT id, name FROM users"; // Adjust the query as needed
$result_users = $conn->query($sql_users);

if (!$result_users) {
    echo "Error fetching users: " . $conn->error;
    exit();
}

// Fetch supervisors (users with role = 'supervisor')
$sql_supervisors = "SELECT id, name FROM users WHERE role_id = 4";
$result_supervisors = $conn->query($sql_supervisors);
// Fetch activities for the activities dropdown
$sql_activities = "SELECT id, name FROM activities"; // Adjust the query as needed
$result_activities = $conn->query($sql_activities);

if (!$result_activities) {
    echo "Error fetching activities: " . $conn->error;
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form values
    $contractor_project = $_POST['contractor_project'];
    $collaborators = isset($_POST['collaborators']) ? implode(',', $_POST['collaborators']) : ''; // Rename variable to collaborators
    $supervisor = $_POST['supervisor'];
    $activity_id = $_POST['activity_id'];
    $progress = $_POST['progress'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $comments = $_POST['comments'];
    $remarks_personnel = $_POST['remarks_personnel'];

     // Check if 'progress' is set and valid
     if (isset($_POST['progress'])) {
        $progress = $_POST['progress'];
    } else {
        // If 'progress' is not set, display error and stop execution
        echo "Progress field is required.";
        exit();
    }

    
    // Insert data into the projects table
    $sql_project = "INSERT INTO projects (contractor_project, user_id, supervisor, date_received, date_assigned, collaborators) 
                VALUES (?, ?, ?, NOW(), NOW(), ?)";
$stmt_project = $conn->prepare($sql_project);
$stmt_project->bind_param("siis", $contractor_project, $user_id, $supervisor, $collaborators);
$stmt_project->execute();

    $project_id = $stmt_project->insert_id; // Get the inserted project ID

    // Insert data into the project_tasks table
    $sql_task = "INSERT INTO project_tasks (contractor_project, collaborators, supervisor, activity_id, progress, start_date, due_date, comments, remarks_personnel, user_id, project_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt_task = $conn->prepare($sql_task);
$stmt_task->bind_param("ssississiii", $contractor_project, $collaborators, $supervisor, $activity_id, $progress, $start_date, $due_date, $comments, $remarks_personnel, $user_id, $project_id);
$stmt_task->execute();

    $task_id = $stmt_task->insert_id; // Get the inserted task ID

    // Insert data into the project_users table for the supervisor
    $sql_supervisor = "INSERT INTO project_users (project_id, user_id, project_tasks_id) VALUES (?, ?, ?)";
    $stmt_supervisor = $conn->prepare($sql_supervisor);
    $stmt_supervisor->bind_param("iii", $project_id, $supervisor, $task_id);
    $stmt_supervisor->execute();

    // Insert data into the project_users table for each collaborator
    if (!empty($_POST['collaboration'])) {
        foreach ($_POST['collaboration'] as $collaborator_id) {
            $sql_collaborator = "INSERT INTO project_users (project_id, user_id, project_tasks_id) VALUES (?, ?, ?)";
            $stmt_collaborator = $conn->prepare($sql_collaborator);
            $stmt_collaborator->bind_param("iii", $project_id, $collaborator_id, $task_id);
            $stmt_collaborator->execute();
        }
    }

    // Close the statement and connection
    $stmt_project->close();
    $stmt_task->close();
    $stmt_supervisor->close();
    header("Location: project_task.php?project_id=" . $project_id);  // You can pass the project ID if needed
    exit; // Ensure no further code is executed after the redirect
}

$conn->close();
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
<form action="add_project_task.php" method="post" class="container mt-4 p-4 border rounded shadow-sm scrollable-form">
    <h3 class="text-center mb-4">Add Project Task</h3>

    <div class="mb-3">
        <label for="contractor_project" class="form-label">Contractor/Project:</label>
        <input type="text" id="contractor_project" name="contractor_project" class="form-control" placeholder="Enter contractor/project" required>
    </div>

    <div class="mb-3">
        <label for="collaborators">Collaborators:</label><br>
<select name="collaborators[]" id="collaborators" class="form-select" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;">
    <?php while ($row = $result_users->fetch_assoc()): ?>
        <option value="<?php echo $row['id']; ?>">
            <?php echo htmlspecialchars($row['name']); ?>
        </option>
    <?php endwhile; ?>
</select>

    </div>

    <div class="mb-3">
            <label for="supervisor" class="form-label">Supervisor:</label>
            <select name="supervisor" id="supervisor" class="form-select">
                <option value="">Select Supervisor</option>
                <?php while ($supervisor = $result_supervisors->fetch_assoc()): ?>
                    <option value="<?php echo $supervisor['id']; ?>"><?php echo htmlspecialchars($supervisor['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>


    <div class="mb-3">
        <label for="activity_id" class="form-label">Activity:</label>
        <select name="activity_id" id="activity_id" class="form-select">
            <option value="">Select Activity</option>
            <?php
            while ($activity = $result_activities->fetch_assoc()): ?>
                <option value="<?php echo $activity['id']; ?>"><?php echo htmlspecialchars($activity['name']); ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="progress" class="form-label">Progress:</label>
        <select class="form-select" id="progress" name="progress" required>
            <option value="" disabled selected>Select progress</option>
            <?php
            $progress_values = ['Not Started', 'On Going', 'For Approval', 'For Correction', 'Submitted Memo for DocRec', 'Done'];
            foreach ($progress_values as $progress_option) {
                echo "<option value='$progress_option'>$progress_option</option>";
            }
            ?>
        </select>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" id="start_date" name="start_date" class="form-control" required>
        </div>
        <div class="col-md-6 mb-3">
            <label for="due_date" class="form-label">Due Date:</label>
            <input type="date" id="due_date" name="due_date" class="form-control" required>
        </div>
    </div>

    <div class="mb-3">
        <label for="comments" class="form-label">Comments:</label>
        <textarea id="comments" name="comments" class="form-control" rows="3" placeholder="Enter comments"></textarea>
    </div>

    <div class="mb-3">
        <label for="remarks_personnel" class="form-label">Remarks:</label>
        <textarea id="remarks_personnel" name="remarks_personnel" class="form-control" rows="3" placeholder="Enter remarks"></textarea>
    </div>

    <div class="mb-3">
        <label for="user_id" class="form-label">User Name:</label>
        <select name="user_id" id="user_id" class="form-select" disabled>
            <option value="<?php echo $user_id; ?>"><?php echo htmlspecialchars($user_name); ?></option>
        </select>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-primary">Add Project Task</button>
    </div>
</form>
</body>
</html>