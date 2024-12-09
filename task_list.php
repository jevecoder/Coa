<?php
include('conn.php');

if (isset($_SESSION['user_id'])) {
  $last_updated_by = $_SESSION['user_id']; 
} else {
  die('User not logged in');
}
$last_updated_by = $_SESSION['user_id'];
$activitiesData = [];
$taskData = []; // To store the task details

// Fetch activities data
$activitiesResult = $conn->query("
    SELECT id, name, percentage 
    FROM activities
");

while ($row = $activitiesResult->fetch_assoc()) {
    $activitiesData[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'percentage' => $row['percentage']
    ];
}

// Fetch user tasks that match activity IDs
$userTasksResult = $conn->query("
   SELECT 
        ut.*, 
        a.name AS activity_name, 
        sh.section_heads_name, 
        IFNULL(GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', '), 'No Collaborators') AS collaborators,
        updater.name AS last_updated_by
    FROM 
        user_task_list ut
    JOIN 
        activities a ON ut.activity_id = a.id
    JOIN 
        section_heads sh ON ut.section_head = sh.id
    LEFT JOIN 
        user_task_collaboration utc ON ut.id = utc.task_id
    LEFT JOIN 
        users u ON utc.user_id = u.id
    LEFT JOIN 
        users updater ON ut.last_updated_by = updater.id
    WHERE 
        ut.user_id = '$last_updated_by'
        OR utc.user_id =  '$last_updated_by'
    GROUP BY 
        ut.id
");

while ($task = $userTasksResult->fetch_assoc()) {
    $taskData[] = $task['activity_id'];
}

// Calculate total percentage for each activity
$activityPercentages = [];
foreach ($activitiesData as $activity) {
    $activityCount = 0;
    // Count how many times this activity_id appears in the user task list
    $activityCount = array_count_values($taskData)[$activity['id']] ?? 0;
    
    // Calculate the total percentage based on matching tasks
    $activityPercentages[] = [
        'name' => $activity['name'],
        'total_percentage' => $activityCount * $activity['percentage']
    ];
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>COA TSO Special Services Assignment Tracker</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="css/coa.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
    <style>
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
    .button {
        padding: 5px 10px;
        font-size: 12px;
        cursor: pointer;
    }
    .save-btn {
        background-color: #4CAF50;
        color: white;
    }
    .cancel-btn {
        background-color: #f44336;
        color: white;
    }
    </style>
</head>
<body>
    <main>
<?php
        include('./nav.php');
        ?>

<section class="hero-section d-flex justify-content-center align-items-start position-relative">
<div class="row justify-content-around position-absolute w-100">
      <div class="col-md-5">
        <div class="card" style="width: 100%;">
          <div class="card-body">
          <canvas id="3dBarChart" width="300" height="300"></canvas>
          </div>
        </div>
      </div>
  
      <div class="col-md-5">
        <div class="card" style="width: 100%;">
          <div class="card-body">
            status update
          <canvas id="3dBarChart" width="300" height="300"></canvas>
          </div>
        </div>
      </div>
    </div>
    </section>


    <section class=" p-2 table-responsive" style="width: 100%;">
    <?php
// Update task in the database
$updated_by_user_id = $_SESSION['user_id'];
$task_id = 1; // Task ID na i-update
$data_to_update = [
    'project_code_name' => 'New Project Name',
    'remarks_personnel' => 'Updated remarks here',
    'collaboration' => 'Collaboration example',
    'section_head' => 2,
    'start_date' => '2024-11-01 08:00:00',
    'due_date' => '2024-11-15 17:00:00',
    'comments_supervisor' => 'Supervisor comments updated',
    'progress' => 'On Going',
];

// Build the dynamic query
$set_clause = [];
$params = [];
$param_types = '';

foreach ($data_to_update as $column => $value) {
    $set_clause[] = "$column = ?";
    $params[] = $value;
    $param_types .= is_int($value) ? 'i' : (is_string($value) ? 's' : 'd');
}

// Add last_updated_by column
$set_clause[] = "last_updated_by = ?";
$params[] = $updated_by_user_id;
$param_types .= 'i';

// Add updated_at column
$set_clause[] = "updated_at = NOW()";
$set_clause_string = implode(', ', $set_clause);
$params[] = $task_id;
$param_types .= 'i';

// Update query
$sql = "UPDATE user_task_list SET $set_clause_string WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);

if ($stmt->execute()) {
    $_SESSION['success_message'] = "Task updated successfully."; // Store success message in session
} else {
    echo "Error updating task: " . $stmt->error;
}

$stmt->close();

// Fetch activities and task details
$activities = [];
$result = $conn->query("SELECT name FROM activities");

while ($row = $result->fetch_assoc()) {
    $activities[] = $row['name']; 
}

if (!isset($_SESSION['user_id'])) {
    echo "Error: User is not logged in.";
    exit;
}

$user_id = $_GET['user_id'];

$sql1 = "SELECT 
            ut.*, 
            a.name AS activity_name, 
            sh.section_heads_name AS section_head, 
            IFNULL(GROUP_CONCAT(DISTINCT u.name ORDER BY u.name SEPARATOR ', '), 'No Collaborators') AS collaborators,
            updater.name AS last_updated_by,
            ut.project_code_name,
            pl.contractor_project
        FROM 
            user_task_list ut
        JOIN 
            activities a ON ut.activity_id = a.id
        JOIN 
            section_heads sh ON ut.section_head = sh.id
        LEFT JOIN 
            user_task_collaboration utc ON ut.id = utc.task_id
        LEFT JOIN 
            users u ON utc.user_id = u.id
        LEFT JOIN 
            users updater ON ut.last_updated_by = updater.id
        LEFT JOIN
            project_list pl ON ut.project_id = pl.id  -- Join project_list to get project info
        WHERE 
            ut.user_id = ? 
            OR utc.user_id = ?
        GROUP BY 
            ut.id;";

// Prepare and execute the first query
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param('ii', $user_id, $user_id);
$stmt1->execute();
$result1 = $stmt1->get_result();

$sql2 = "SELECT 
            pl.contractor_project,
            pl.agency,
            pl.supervisor,
            pl.collaboration_id
        FROM 
            project_list pl
        WHERE 
            pl.collaboration_id IN (SELECT collaboration FROM user_task_list WHERE user_id = ? 
                                    OR collaboration IN (SELECT collaboration FROM user_task_collaboration WHERE user_id = ?));";

// Prepare and execute the second query
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param('ii', $user_id, $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();

// Output the tasks
echo "<table class='table bg-light text-center table-hover table-sm rounded-table'>
        <thead class='bg-dark text-light'>
            <tr style='font-size: 10px;'>
                <th>Project Code Name</th>
                <th>Activity</th>
                <th>Progress</th>
                <th>Remarks (Personnel)</th>
                <th>Collaboration</th>
                <th>Section Head</th>
                <th>Start Date</th>
                <th>Due Date</th>
                <th>Day Left</th>
                <th>Comments (Supervisor)</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>";
        

// Loop through tasks (result1)
while ($row1 = $result1->fetch_assoc()) {
    $start_date = date("F j, Y", strtotime($row1['start_date']));
    $due_date = date("F j, Y", strtotime($row1['due_date']));
    $day_left = max(0, ceil((strtotime($row1['due_date']) - time()) / (60 * 60 * 24)));
    $last_updated_by = $row1['last_updated_by'] ? $row1['last_updated_by'] : 'No updates yet';

    echo "<tr>
              <td class='position-relative' style='font-size: 10px;'>";
    echo !empty($row1['project_code_name']) ? htmlspecialchars($row1['project_code_name']) : htmlspecialchars($row1['contractor_project']);
    echo "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger'>" . htmlspecialchars($last_updated_by) . "</span>
              </td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['activity_name']) . "</td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['progress']) . "</td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['remarks_personnel']) . "</td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['collaborators']) . "</td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['section_head']) . "</td>
              <td style='font-size: 10px;'>" . $start_date . "</td>
              <td style='font-size: 10px;'>" . $due_date . "</td>
              <td class='fw-bold'>" . $day_left . " day" . ($day_left != 1 ? 's' : '') . " left</td>
              <td style='font-size: 10px;'>" . htmlspecialchars($row1['comments_supervisor']) . "</td>
              <td>
                  <a href='update_task.php?id=" . htmlspecialchars($row1['id']) . "' class='btn btn-dark btn-sm'>
                      <i class='fa fa-pencil'></i>
                  </a>
              </td>
          </tr>";
}

echo "</tbody></table>";

$stmt1->close();
$stmt2->close();
?>










<script>

  console.log("$row['collaborators']")
</script>
</section>

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/task_list.js"></script>
    <script>
    // Pass PHP data to JavaScript
    document.addEventListener("DOMContentLoaded", function () {
    // Fetching data from PHP and preparing chart
    const activitiesData = <?php echo json_encode($activityPercentages); ?>;
    console.log(activitiesData);

    // Extract activity names and total percentages
    const activityNames = activitiesData.map(item => item.name);
    const activityPercentages = activitiesData.map(item => item.total_percentage);

    // Generate unique colors for bars
    const getUniqueColor = () => {
        return `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.6)`;
    };
    const barColors = activityNames.map(() => getUniqueColor());

    // Ensure canvas element is present
    const canvas = document.getElementById('3dBarChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: activityNames,
                datasets: [{
                    label: 'Activity Progress',
                    data: activityPercentages, // Fill this with actual percentage values
                    backgroundColor: barColors,
                    borderColor: barColors,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100, // Ensure the max Y value is always 100
                        ticks: {
                            callback: (value) => value + '%',
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: (tooltipItem) => tooltipItem.raw + '%'
                        }
                    }
                }
            }
        });
    } else {
        console.error("Canvas element not found!");
    }
});


</script>





</body>
</html>