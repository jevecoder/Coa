<?php
include('conn.php');


if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $project_code_name = $_POST['project_code_name'];
    $remarks_personnel = $_POST['remarks_personnel'];
    $collaboration = isset($_POST['collaboration']) ? $_POST['collaboration'] : [];
    $section_head = $_POST['section_head'];
    $start_date = $_POST['start_date'];
    $due_date = $_POST['due_date'];
    $comments_supervisor = $_POST['comments_supervisor'];
    $progress = $_POST['progress'];
    $activity_id = $_POST['activity_id'];


    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    if (!$user_id) {
        echo "Error: User is not logged in.";
        exit();
    }

 
    $sql = "INSERT INTO user_task_list (project_code_name, activity_id, progress, remarks_personnel, collaboration, section_head, start_date, due_date, comments_supervisor, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: " . $conn->error;
        exit();
    }


    $collaboration_str = $collaboration ? implode(',', $collaboration) : '';


    $stmt->bind_param("sisssssssi", 
        $project_code_name,
        $activity_id,
        $progress,
        $remarks_personnel,
        $collaboration_str, 
        $section_head,
        $start_date,
        $due_date,
        $comments_supervisor,
        $user_id
    );

    $sql2 = "INSERT INTO project_list 
    (contractor_project, user_id, collaboration_id, subjects, document_no, date_received, date_assigned, agency, supervisor) 
    VALUES (?, ?, ?, '', '', '', '', '', '')";

$stmt2 = $conn->prepare($sql2);
if (!$stmt2) {
    echo "Prepare failed for project_list: " . $conn->error;
    exit();
}

// Set collaboration_id to NULL if no collaborators are provided
$collaboration_id = empty($collaboration) ? NULL : (int)$collaboration_str;



  
    if ($stmt->execute()) {
       
        $task_id = $stmt->insert_id;

        if (!empty($collaboration)) {
            $collaboration_sql = "INSERT INTO user_task_collaboration (task_id, user_id) VALUES (?, ?)";
            $collab_stmt = $conn->prepare($collaboration_sql);
            foreach ($collaboration as $collaborator_id) {
                $collab_stmt->bind_param("ii", $task_id, $collaborator_id);
                $collab_stmt->execute();
            }
            $collab_stmt->close();
        }

        $stmt2->bind_param("sii", $project_code_name, $user_id, $collaboration_id);
if (!$stmt2->execute()) {
    echo "Error inserting into project_list: " . $stmt2->error;
}
$stmt2->close();

        header("Location: task_list.php?user_id={$user_id}");
        exit(); 
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>



<!-- Add Task Form -->
<div class="container mt-3">
    <h4>Add New Task</h4>
    <form action="add_task.php" method="POST">
        <div class="mb-3">
            <label for="project_code_name" class="form-label">Project Code Name</label>
            <input type="text" class="form-control" id="project_code_name" name="project_code_name" required>
        </div>
        <div class="mb-3">
            <label for="remarks_personnel" class="form-label">Remarks (Personnel)</label>
            <input type="text" class="form-control" id="remarks_personnel" name="remarks_personnel" required>
        </div>
        <div class="mb-3">
            <label for="collaboration" class="form-label">Collaboration</label>
            <div>
                <?php
               
                $sql = "SELECT id, name FROM users WHERE section_heads_id IS NOT NULL";
                $result = $conn->query($sql);
                
                while ($row = $result->fetch_assoc()) {
                   
                    echo "<div class='form-check'>";
                    echo "<input type='checkbox' class='form-check-input' name='collaboration[]' value='" . $row['id'] . "' id='section_head_" . $row['id'] . "'>";
                    echo "<label class='form-check-label' for='section_head_" . $row['id'] . "'>" . $row['name'] . "</label>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div class="mb-3">
            <label for="section_head" class="form-label">Section Head</label>
            <select name="section_head" id="section_head" class="form-select" required>
                <?php
                $result = $conn->query("SELECT * FROM section_heads");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['section_heads_name'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" id="start_date" name="start_date" required>
        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label">Due Date</label>
            <input type="date" class="form-control" id="due_date" name="due_date" required>
        </div>
        <div class="mb-3">
            <label for="comments_supervisor" class="form-label">Comments (Supervisor)</label>
            <input type="text" class="form-control" id="comments_supervisor" name="comments_supervisor">
        </div>
        <div class="mb-3">
            <label for="progress" class="form-label">Progress</label>
            <select class="form-control" id="progress" name="progress" required>
                <option value="" disabled selected>Select progress</option>
                <?php
                $progress_values = ['Not Started', 'On Going', 'For Approval', 'For Correction', 'Submitted Memo for DocRec', 'Done'];
                foreach ($progress_values as $progress_option) {
                    echo "<option value='$progress_option'>$progress_option</option>";
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="activity_id" class="form-label">Activity</label>
            <select class="form-select" id="activity_id" name="activity_id" required>
                <?php
                $sql = "SELECT * FROM activities";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
                }
                ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Task</button>
    </form>
</div>