<?php
include('conn.php');

// Check if id is provided in the GET or POST request
if (isset($_GET['id'])) {
    $id = $_GET['id']; // Get the id from the URL (GET request)
} elseif (isset($_POST['id'])) {
    $id = $_POST['id']; // Get the id from the form (POST request)
} else {
    echo "No ID provided.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $project_code_name = $_POST['project_code_name'];
    $remarks_personnel = $_POST['remarks_personnel'];
    $updated_collaborators = isset($_POST['collaboration']) ? $_POST['collaboration'] : []; 
    $section_head = $_POST['section_head'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $comments_supervisor = $_POST['comments_supervisor'];
    $progress = $_POST['progress'];
    $activity_id = $_POST['activity_id'];
    $id = $_POST['id'];
    $user_id = $_SESSION['user_id'];
    $current_collaborators = [];
    $sql_fetch = "SELECT user_id FROM user_task_collaboration WHERE task_id = ?";


    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $id);
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();
        while ($row = $result->fetch_assoc()) {
            $current_collaborators[] = $row['user_id'];
        }
        $stmt_fetch->close();
    } else {
        echo "Error fetching collaborators: " . $conn->error;
        exit();
    }

    $to_delete = array_diff($current_collaborators, $updated_collaborators); 
    if (!empty($to_delete)) {
        $placeholders = implode(',', array_fill(0, count($to_delete), '?')); 
        $sql_delete = "DELETE FROM user_task_collaboration WHERE task_id = ? AND user_id IN ($placeholders)";
        if ($stmt_delete = $conn->prepare($sql_delete)) {
            $params = array_merge([$id], $to_delete); 
            $stmt_delete->bind_param(str_repeat('i', count($params)), ...$params);
            $stmt_delete->execute();
            $stmt_delete->close();
        } else {
            echo "Error preparing delete statement: " . $conn->error;
            exit();
        }
    }

   
    $to_add = array_diff($updated_collaborators, $current_collaborators); 
    if (!empty($to_add)) {
        $sql_insert = "INSERT INTO user_task_collaboration (task_id, user_id) VALUES (?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            foreach ($to_add as $new_collaborator) {
                $stmt_insert->bind_param("ii", $id, $new_collaborator);
                $stmt_insert->execute();
            }
            $stmt_insert->close();
        } else {
            echo "Error preparing insert statement: " . $conn->error;
            exit();
        }
    }

   
    $collaboration = implode(',', $updated_collaborators); 
    $sql_update = "UPDATE user_task_list 
                   SET project_code_name = ?, remarks_personnel = ?, collaboration = ?, section_head = ?, 
                       start_date = ?, due_date = ?, comments_supervisor = ?, 
                       progress = ?, activity_id = ?, last_updated_by = ? 
                   WHERE id = ?";
    if ($stmt_update = $conn->prepare($sql_update)) {
        $stmt_update->bind_param(
            "sssssssssii",
            $project_code_name, $remarks_personnel, $collaboration, $section_head,
            $start_date, $due_date, $comments_supervisor, $progress, $activity_id,
            $user_id, $id
        );
        if ($stmt_update->execute()) {
            header("Location: task_list.php?user_id={$user_id}");
            exit();
        } else {
            echo "Error updating task: " . $stmt_update->error;
        }
        $stmt_update->close();
    } else {
        echo "Error preparing update statement: " . $conn->error;
    }
}




$sql = "SELECT * FROM user_task_list WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id); 
    $stmt->execute();
    $result = $stmt->get_result();
    
   
    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        
       
        $project_code_name = isset($task['project_code_name']) ? $task['project_code_name'] : '';
        $remarks_personnel = isset($task['remarks_personnel']) ? $task['remarks_personnel'] : '';
        $collaboration = isset($task['collaboration']) ? explode(',', $task['collaboration']) : []; // Convert CSV to array
        $section_head = isset($task['section_head']) ? $task['section_head'] : '';
        $start_date = isset($task['start_date']) ? $task['start_date'] : '';
        $due_date = isset($task['due_date']) ? $task['due_date'] : '';
        $comments_supervisor = isset($task['comments_supervisor']) ? $task['comments_supervisor'] : '';
        $progress = isset($task['progress']) ? $task['progress'] : '';
        $activity_id = isset($task['activity_id']) ? $task['activity_id'] : '';
    } else {
        echo "Task not found.";
        exit();
    }
    $stmt->close();
} else {
    echo "Error preparing SQL statement: " . $conn->error;
    exit();
}
?>









<!-- Edit Task Form -->
<div class="container mt-3">
    <h4>Edit Task</h4>
    <form action="update_task.php" method="POST">
    <input type="hidden" name="id" value="<?php echo $id; ?>">

    <div class="mb-3">
        <label for="project_code_name" class="form-label">Project Code Name</label>
        <input type="text" class="form-control" id="project_code_name" name="project_code_name" 
               value="<?php echo htmlspecialchars($project_code_name); ?>" required>
    </div>

    <div class="mb-3">
        <label for="remarks_personnel" class="form-label">Remarks (Personnel)</label>
        <input type="text" class="form-control" id="remarks_personnel" name="remarks_personnel" 
               value="<?php echo htmlspecialchars($remarks_personnel); ?>" required>
    </div>

    <div class="mb-3">
        <label for="collaboration" class="form-label">Collaboration</label>
        <div>
            <?php
            
            $sql = "SELECT id, name FROM users WHERE section_heads_id IS NOT NULL";
            $result = $conn->query($sql);

            
            while ($row = $result->fetch_assoc()) {
               
                $checked = in_array($row['id'], $collaboration) ? 'checked' : '';
                echo "<div class='form-check'>";
                echo "<input type='checkbox' class='form-check-input' name='collaboration[]' value='" . $row['id'] . "' id='section_head_" . $row['id'] . "' $checked>";
                echo "<label class='form-check-label' for='section_head_" . $row['id'] . "'>" . $row['name'] . "</label>";
                echo "</div>";
            }
            ?>
        </div>
    </div>




    <div class="mb-3">
        <label for="section_head" class="form-label">Section Head</label>
        <select class="form-select" id="section_head" name="section_head" required>
            <?php
            
            $sql = "SELECT * FROM section_heads";
            $result = $conn->query($sql);
            
            while ($row = $result->fetch_assoc()) {
            
                $selected = ($section_head == $row['id']) ? 'selected' : '';
                echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['section_heads_name']) . "</option>";
            }
            ?>
        </select>
    </div>

    <div class="mb-3">
    <label for="start_date" class="form-label">Start Date</label>
    <input type="date" class="form-control" id="start_date" name="start_date" 
           value="<?php echo date('Y-m-d', strtotime($start_date)); ?>" required>
</div>

<div class="mb-3">
    <label for="due_date" class="form-label">Due Date</label>
    <input type="date" class="form-control" id="due_date" name="due_date" 
           value="<?php echo date('Y-m-d', strtotime($due_date)); ?>" required>
</div>


    <div class="mb-3">
        <label for="comments_supervisor" class="form-label">Comments (Supervisor)</label>
        <input type="text" class="form-control" id="comments_supervisor" name="comments_supervisor" 
               value="<?php echo htmlspecialchars($comments_supervisor); ?>">
    </div>

    <!-- Progress Dropdown -->
    <div class="mb-3">
        <label for="progress" class="form-label">Progress</label>
        <select class="form-select" id="progress" name="progress" required>
            <option value="Not Started" <?php echo ($progress == "Not Started") ? 'selected' : ''; ?>>Not Started</option>
            <option value="On Going" <?php echo ($progress == "On Going") ? 'selected' : ''; ?>>On Going</option>
            <option value="For Approval" <?php echo ($progress == "For Approval") ? 'selected' : ''; ?>>For Approval</option>
            <option value="For Correction" <?php echo ($progress == "For Correction") ? 'selected' : ''; ?>>For Correction</option>
            <option value="Submitted Memo for DocRec" <?php echo ($progress == "Submitted Memo for DocRec") ? 'selected' : ''; ?>>Submitted Memo for DocRec</option>
            <option value="Done" <?php echo ($progress == "Done") ? 'selected' : ''; ?>>Done</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="activity_id" class="form-label">Activity</label>
        <select class="form-select" id="activity_id" name="activity_id" required>
            <?php
                // Query to fetch activities
                $sql = "SELECT * FROM activities";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    $selected = ($activity_id == $row['id']) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($row['id']) . "' $selected>" . htmlspecialchars($row['name']) . "</option>";
                }
            ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Update Task</button>
    </form>
</div>