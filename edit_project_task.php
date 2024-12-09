<?php
include 'conn.php';

// Check if the Project Task ID is passed
if (!isset($_GET['id'])) {
    $_SESSION['error_message'] = 'Project Task ID is missing!';
    header("Location: project_tasks.php");
    exit;
}
$project_task_id = intval($_GET['id']);

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role_id'])) {
    $_SESSION['error_message'] = "User not logged in.";
    header("Location: index.php");
    exit;
}

// Fetch logged-in user's ID and role
$logged_in_user_id = $_SESSION['user_id'];
$logged_in_user_role = $_SESSION['role_id'];

// Fetch task details for form pre-filling
$query = "SELECT * FROM project_tasks WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $project_task_id);
$stmt->execute();
$project_task = $stmt->get_result()->fetch_assoc();

if (!$project_task) {
    $_SESSION['error_message'] = 'Project Task not found!';
    header("Location: project_tasks.php");
    exit;
}

// Fetch users and activities for dropdowns
$result_users = $conn->query("SELECT id, name FROM users");
$result_activities = $conn->query("SELECT id, name FROM activities");

// Determine access permissions
$is_action_officer = ($logged_in_user_id == $project_task['user_id']);
$is_supervisor = ($logged_in_user_id == $project_task['supervisor']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $contractor_project = isset($_POST['contractor_project']) ? trim($_POST['contractor_project']) : '';
    $collaborators = isset($_POST['collaborators']) ? $_POST['collaborators'] : [];
    $supervisor = isset($_POST['supervisor']) ? intval($_POST['supervisor']) : 0;
    $activity_id = intval($_POST['activity_id']);
    $progress = $_POST['progress'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $comments = isset($_POST['comments']) ? $_POST['comments'] : '';
    $remarks_personnel = $_POST['remarks_personnel'];

    // Determine which column was updated
    $updated_column = 'none';
    if ($contractor_project != $project_task['contractor_project']) $updated_column = 'contractor_project';
    elseif ($collaborators != $project_task['collaborators']) $updated_column = 'collaborators';
    elseif ($supervisor != $project_task['supervisor']) $updated_column = 'supervisor';
    elseif ($activity_id != $project_task['activity_id']) $updated_column = 'activity_id';
    elseif ($progress != $project_task['progress']) $updated_column = 'progress';
    elseif ($start_date != $project_task['start_date']) $updated_column = 'start_date';
    elseif ($due_date != $project_task['due_date']) $updated_column = 'due_date';
    elseif ($comments != $project_task['comments']) $updated_column = 'comments';
    elseif ($remarks_personnel != $project_task['remarks_personnel']) $updated_column = 'remarks_personnel';

    // Build query based on roles
    $collaborators_str = implode(',', $collaborators);
    $update_query = $is_action_officer
        ? "UPDATE project_tasks 
            SET contractor_project = ?, collaborators = ?, supervisor = ?, activity_id = ?, progress = ?, 
                start_date = ?, due_date = ?, comments = ?, remarks_personnel = ?, last_updated_by = ?, 
                updated_column = ?, updated_at = NOW() WHERE id = ?"
        : ($is_supervisor
            ? "UPDATE project_tasks 
                SET activity_id = ?, progress = ?, start_date = ?, due_date = ?, comments = ?, remarks_personnel = ?, 
                    last_updated_by = ?, updated_column = ?, updated_at = NOW() WHERE id = ?"
            : "UPDATE project_tasks 
                SET activity_id = ?, progress = ?, start_date = ?, due_date = ?, remarks_personnel = ?, 
                    last_updated_by = ?, updated_column = ?, updated_at = NOW() WHERE id = ?");

    $stmt = $conn->prepare($update_query);

    // Bind params based on role
    if ($is_action_officer) {
        $stmt->bind_param(
            "ssiisssssisi",
            $contractor_project, $collaborators_str, $supervisor,
            $activity_id, $progress, $start_date, $due_date,
            $comments, $remarks_personnel, $logged_in_user_id,
            $updated_column, $project_task_id
        );
    } elseif ($is_supervisor) {
        $stmt->bind_param(
            "issssisi",
            $activity_id, $progress, $start_date, $due_date,
            $comments, $remarks_personnel, $logged_in_user_id,
            $updated_column, $project_task_id
        );
    } else {
        $stmt->bind_param(
            "issssisi",
            $activity_id, $progress, $start_date, $due_date,
            $remarks_personnel, $logged_in_user_id,
            $updated_column, $project_task_id
        );
    }

    if (!$stmt->execute()) {
        error_log("Error updating Project Task: " . $stmt->error);
        $_SESSION['error_message'] = "Error updating Project Task: " . $stmt->error;
        header("Location: edit_project_task.php?id=" . $project_task_id);
        exit;
    }

    // Sync collaborators with `project_users`
    $project_id = $project_task['project_id'];
    $conn->query("DELETE FROM project_users WHERE project_id = $project_id");

    foreach ($collaborators as $user_id) {
        $conn->query("INSERT INTO project_users (project_id, user_id) VALUES ($project_id, $user_id)");
    }

    $_SESSION['message'] = "Project Task updated successfully!";
    header("Location: project_task.php");
    exit;
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
<form action="edit_project_task.php?id=<?php echo $project_task_id; ?>" method="post" class="container mt-4 p-4 border rounded shadow-sm scrollable-form">

<?php if ($is_action_officer): ?>
        <div class="mb-3">
            <label for="contractor_project" class="form-label">Contractor/Project:</label>
            <input type="text" id="contractor_project" name="contractor_project" class="form-control" value="<?php echo htmlspecialchars($project_task['contractor_project']); ?>">
        </div>

        <div class="mb-3">
            <label for="collaborators" class="form-label">Collaborators:</label><br>
            <?php while ($row = $result_users->fetch_assoc()): ?>
                <label>
                    <input type="checkbox" name="collaborators[]" value="<?php echo $row['id']; ?>" <?php echo in_array($row['id'], explode(',', $project_task['collaborators'])) ? 'checked' : ''; ?>>
                    <?php echo htmlspecialchars($row['name']); ?>
                </label><br>
            <?php endwhile; ?>
        </div>

<div class="mb-3">
            <label for="supervisor" class="form-label">Supervisor:</label>
            <select name="supervisor" class="form-select" id="supervisor">
                <option value="">Select Supervisor</option>
                <?php while ($row = $result_users->fetch_assoc()): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $row['id'] == $project_task['supervisor'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    <?php endif; ?>


<div class="mb-3">
    <label class="form-label" for="activity_id">Activity:</label>
    <select name="activity_id" id="activity_id" class="form-select" required>
        <option value="">Select Activity</option>
        <?php while ($activity = $result_activities->fetch_assoc()): ?>
            <option value="<?php echo $activity['id']; ?>" <?php echo $activity['id'] == $project_task['activity_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($activity['name']); ?>
            </option>
        <?php endwhile; ?>
    </select>
</div>

<div class="mb-3">
    <label for="progress" class="form-label">Progress:</label>
    <select name="progress" id="progress" class="form-select" required>
        <?php
        $progress_values = ['Not Started', 'On Going', 'For Approval', 'For Correction', 'Submitted Memo for DocRec', 'Done'];
        foreach ($progress_values as $progress_option):
            $selected = $progress_option == $project_task['progress'] ? 'selected' : '';
            ?>
            <option value="<?php echo $progress_option; ?>" <?php echo $selected; ?>>
                <?php echo htmlspecialchars($progress_option); ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="mb-3">
    <label for="start_date" class="form-label">Start Date:</label>
    <input type="date" id="start_date" class="form-control" name="start_date" value="<?php 
        // Convert start_date to yyyy-mm-dd format
        echo htmlspecialchars(date('Y-m-d', strtotime($project_task['start_date']))); ?>" required>
</div>

<div class="mb-3">
    <label for="due_date" class="form-label">Due Date:</label>
    <input type="date" id="due_date" class="form-control" name="due_date" value="<?php 
        // Convert due_date to yyyy-mm-dd format
        echo htmlspecialchars(date('Y-m-d', strtotime($project_task['due_date']))); ?>" required>
</div>

<?php if ($is_supervisor): ?>
        <div class="mb-3">
            <label for="comments" class="form-label">Comments:</label>
            <textarea id="comments" name="comments" class="form-control"><?php echo htmlspecialchars($project_task['comments']); ?></textarea>
        </div>
    <?php endif; ?>

<div class="mb-3">
    <label for="remarks_personnel" class="form-label">Remarks:</label>
    <textarea id="remarks_personnel" class="form-control" name="remarks_personnel"><?php echo htmlspecialchars($project_task['remarks_personnel']); ?></textarea>
</div>

    <button type="submit">Update Project Task</button>
</form>
</body>
</html>