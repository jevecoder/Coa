<?php
include('conn.php');
// Check if login success session exists for Toastr message
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    $showToastr = true;
    unset($_SESSION['login_success']);
} else {
    $showToastr = false;
}
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: index.php");
    exit();
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

.hide-row {
    display: none;
}

    </style>
</head>
<body>
    <main>
        <?php include('./nav.php'); ?>
        
        <section class="hero-section">
        </section>

        <section class="p-2 table-responsive d-flex flex-column justify-content-center w-100" style="width: 100%;margin-top:-20%">
        <div class="row w-100 d-flex justify-content-around align-items-start position-relative">
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
                        <canvas id="2ndBarChart" style="width: 100%; height: 300px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
<br><br><br>
<div class="w-100 flex-column d-flex">
<div class="search-container mb-3">
<svg style="margin-top: 12px;" class="icon" aria-hidden="true" viewBox="0 0 24 24"><g><path d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"></path></g></svg>
    <input class="input" type="text" id="searchInput" class="form-control" placeholder="Search tasks..." oninput="liveSearch()">
</div>
        <table class="table custom-table table-hover mb-0 align-self-center justify-content-center">
            <thead class="table-header bg-dark">
                <tr style="font-size: 10px;">
                    <th>CONTRACTOR / PROJECT</th>
                    <th>Activity</th>
                    <th>Progress</th>
                    <th>REMARKS (Personnel)</th>
                    <th>Collaboration</th>
                    <th>Supervisor</th>
                    <th>Start Date</th>
                    <th>Due Date</th>
                    <th>Day left</th>
                    <th>COMMENTS</th>
                    <th>STATUS</th>
                    <th>ACTION</th>
                </tr>
            </thead>
            <tbody id="taskTable">
            <?php
// I-check kung may user_id sa query string
$user_id = $_SESSION['user_id'];
$view_user_id = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['user_id'];  // Default to logged-in user
$_SESSION['start_timer'] = true;
$_SESSION['badge_start_time'] = time();
 // Get search query if available
 $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';  // Kunin ang search input mula sa query string
// Pagination Variables
$tasksPerPage = 10; // Bilang ng tasks na ipapakita kada page
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Kukunin ang kasalukuyang page mula sa URL
$offset = ($currentPage - 1) * $tasksPerPage; // I-calculate ang offset

$sql = "
SELECT pt.*, 
GROUP_CONCAT(u.name SEPARATOR ', ') AS collaborators_names,
a.name AS activity_name,
s.name AS supervisor_name,
u2.name AS last_updated_by_name
FROM project_tasks pt
LEFT JOIN users u ON FIND_IN_SET(u.id, pt.collaborators)
LEFT JOIN activities a ON pt.activity_id = a.id
LEFT JOIN users s ON pt.supervisor = s.id
LEFT JOIN users u2 ON pt.last_updated_by = u2.id
WHERE (pt.supervisor = '$view_user_id' 
       OR FIND_IN_SET('$view_user_id', pt.collaborators) 
       OR pt.user_id = '$view_user_id') 
AND (pt.contractor_project LIKE '%$searchQuery%' 
     OR a.name LIKE '%$searchQuery%' 
     OR pt.contractor_project LIKE '%$searchQuery%')
     AND (pt.progress != 'done' AND pt.progress != 100)  -- Exclude completed tasks
GROUP BY pt.id
LIMIT $tasksPerPage OFFSET $offset
";


$totalTasksQuery = "
SELECT COUNT(DISTINCT pt.id) AS total_tasks
FROM project_tasks pt
LEFT JOIN activities a ON pt.activity_id = a.id
WHERE (pt.supervisor = '$view_user_id' OR FIND_IN_SET('$view_user_id', pt.collaborators) OR pt.user_id = '$view_user_id') 
AND (pt.contractor_project LIKE '%$searchQuery%' OR a.name LIKE '%$searchQuery%' OR pt.contractor_project LIKE '%$searchQuery%')
";
$totalTasksResult = mysqli_query($conn, $totalTasksQuery);
$totalTasksRow = mysqli_fetch_assoc($totalTasksResult);
$totalTasks = $totalTasksRow['total_tasks'];
$totalPages = ceil($totalTasks / $tasksPerPage); // Kabuuang bilang ng pages




$queryForGraph = "
    SELECT 
        a.name AS activity_name, 
        COUNT(pt.id) AS total_count, 
        IFNULL(SUM(pt.progress), 0) AS total_progress,
        -- Assign default percentages based on activity name
        CASE
            WHEN a.name = 'PRELIMINARY REVIEW' THEN COUNT(pt.id) * 10.00
            WHEN a.name = 'TECHNICAL EVALUATION' THEN COUNT(pt.id) * 20.00
            WHEN a.name = 'TECHNICAL INSPECTION' THEN COUNT(pt.id) * 15.00
            WHEN a.name = 'COMPUTATION' THEN COUNT(pt.id) * 25.00
            WHEN a.name = 'DRAFT OF FINAL REPORT' THEN COUNT(pt.id) * 25.00
            WHEN a.name = 'FINALIZATION' THEN COUNT(pt.id) * 3.00
            WHEN a.name = 'RELEASED' THEN COUNT(pt.id) * 2.00
            ELSE 0.00 -- Default case if activity is not listed
        END AS total_percentage
    FROM activities a
    LEFT JOIN project_tasks pt ON pt.activity_id = a.id
    WHERE pt.supervisor = '$view_user_id' 
       OR FIND_IN_SET('$view_user_id', pt.collaborators) 
       OR pt.created_by = '$view_user_id'  -- Assuming created_by field for the user who created the task
    GROUP BY a.name
";


$stmt = $conn->prepare($queryForGraph); // No need to bind parameters
$stmt->execute();
$graphResult = $stmt->get_result();

$activityPercentages = [];
if (mysqli_num_rows($graphResult) > 0) {
    $task_ids = [];
$start_times = [];
    while ($row = mysqli_fetch_assoc($graphResult)) {
        $activityPercentages[] = [
            'activity_name' => $row['activity_name'],
            'total_count' => (int) $row['total_count'],
            'total_percentage' => (float) $row['total_percentage'],
        ];
    }
}


// Handle empty $activityPercentages if no data
$activityPercentages = !empty($activityPercentages) ? $activityPercentages : [];
$result = mysqli_query($conn, $sql);
// Remainder of the code for displaying the tasks...
// I-fetch ang pangalan ng user na ipinasa sa URL
$user_name_query = "SELECT name FROM users WHERE id = '$view_user_id'";
$user_name_result = mysqli_query($conn, $user_name_query);
$user_name_row = mysqli_fetch_assoc($user_name_result);
$user_name = $user_name_row['name'];

echo "<h3 style='font-size:20px'>Viewing tasks for: " . htmlspecialchars($user_name) . "</h3>";
$daysLeftArray = []; // Array to store days_left values
$taskNames = []; // Array to store task names
if (mysqli_num_rows($result) > 0) {
    $task_ids = [];
$updated_times = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Calculate the remaining days
        $task_ids[] = $row['id'];
        $updated_times[] = strtotime($row['updated_at']);
        $due_date = new DateTime($row['due_date']);
        $current_date = new DateTime();
        $interval = $current_date->diff($due_date);
        $days_left = $interval->invert == 1 ? -$interval->days : $interval->days;

        $taskNames[] = htmlspecialchars($row['activity_name']);
        $daysLeftArray[] = $days_left; // I-store ang days_left, pwede itong maging positibo o negatibo

        $start_date = new DateTime($row['start_date']);
        $formatted_start_date = $start_date->format('Y-m-d');
        $formatted_due_date = $due_date->format('Y-m-d');

   // Ensure the session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize badge-related session variables if not already set
if (!isset($_SESSION['shown_badges'])) {
    $_SESSION['shown_badges'] = [];
}
if (!isset($_SESSION['badge_start_time'])) {
    $_SESSION['badge_start_time'] = time(); // Ensure the start time is set
}

$badgeHtml = '';  // Initialize badge HTML

// Timer Badge Logic for showing badge within 10 seconds of the last update
$current_time = time();

if (!isset($_SESSION['shown_badges'][$row['id']])) {
    $updated_at = strtotime($row['updated_at']);
    if ($updated_at) {
        $time_since_update = $current_time - $updated_at;
        $threshold = 10; // 10 seconds threshold

        if ($time_since_update <= $threshold) {
            // Show badge within 10 seconds
            $_SESSION['shown_badges'][$row['id']] = true;  // Mark this badge as shown
            $badgeHtml = "<span class='position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger' id='badge-" . $row['id'] . "' data-start-time='$current_time'>
                            " . htmlspecialchars($row['last_updated_by_name']) . "<br>
                            <span id='timer-" . $row['id'] . "'>Time Left: 10s</span>
                          </span>";
        }
    }
}

 // Add task completion logic
 $is_fulfilled = $row['progress'] == 'done' || $row['progress'] == 100; // If task is marked as done or 100% complete
 $row_class = $is_fulfilled ? 'fulfilled-row' : '';  // Add a class for fulfilled tasks

 // Only echo this once, not twice
 echo '<tr class="' . $row_class . '" style="font-size:12px" ' . ($is_fulfilled ? 'style="display:none"' : '') . '>';
        echo '<td style="position:relative;width:250px">' . htmlspecialchars($row['contractor_project']) . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . htmlspecialchars($row['activity_name']) . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . htmlspecialchars($row['progress']) . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;width:200px">' . htmlspecialchars($row['remarks_personnel']) . $badgeHtml . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . htmlspecialchars($row['collaborators_names']) . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . htmlspecialchars($row['supervisor_name']) . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . $formatted_start_date . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;">' . $formatted_due_date . '</td>';
        echo '<td style="width:200px;text-align:center;text-align:center;">' . $days_left . '</td>';
        echo '<td style="width:200px;text-align:center;position:relative;width:200px">' . htmlspecialchars($row['comments']) . $badgeHtml . '</td>';
          // Mark task as "fulfilled"
          if ($is_fulfilled) {
            echo '<td style="width:100px;text-align:center;">' . 'Fulfilled' . '</td>';
        } else {
            echo '<td style="width:100px;text-align:center;"></td>';
        }
       // assuming you already fetched the task data into $row
if ($_SESSION['user_id'] == $row['user_id'] || 
    strpos($row['collaborators'], $_SESSION['user_id']) !== false || 
    $_SESSION['user_id'] == $row['supervisor']) {
    // show the edit button if the logged-in user is the task creator, a collaborator, or the supervisor
    echo '<td><a href="edit_project_task.php?id=' . $row['id'] . '" class="btn btn-dark btn-sm"><i class="fa fa-pencil"></i></a></td>';
} else {
    // if not, leave the cell empty (or show a message if desired)
    echo '<td></td>';
}

        


echo '</tr>';
    }
} else {
    echo '<tr><td colspan="11">No data found</td></tr>';
}
echo '<div class="pagination" style="padding:20px;margin:7px 7px;text-align: center;">';
for ($page = 1; $page <= $totalPages; $page++) {
    if ($page == $currentPage) {
        echo "<a class='bg-dark' href='?page=$page' style='padding: 8px 15px; margin: 0 5px; color:white'>$page</a>";
    } else {
        echo "<a class='btn btn-dark btn-sm' href='?page=$page' style='padding: 8px 15px; margin: 0 5px;'>$page</a>";
    }
}
echo '</div>';
?>
<script>
// Start Timer for badges
function startTimer(badgeElement) {
    const timerElement = badgeElement.querySelector('#timer-' + badgeElement.id);
    const startTime = parseInt(badgeElement.dataset.startTime); // Get start time from data attribute

    const interval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        const timeLeft = 10 - elapsed;

        if (timeLeft <= 0) {
            clearInterval(interval);
            badgeElement.style.display = 'none';  // Hide the badge after 10 seconds
        } else {
            timerElement.textContent = "Time Left: " + timeLeft + "s";
        }
    }, 1000);
}

// Retrieve badges on page load
const badges = document.querySelectorAll('.badge');

// Start timers for each badge
badges.forEach(badge => {
    startTimer(badge);
});

</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#taskTable tr'); // Get all rows

    rows.forEach(function(row) {
        const progressCell = row.cells[2]; // Assuming the progress is in the 3rd column (index 2)
        if (progressCell && progressCell.textContent.trim() === 'done') {
            row.style.display = 'none'; // Hide the row if progress is "done"
        }
    });
});
</script>


            </tbody>
        </table>
        <?php if (isset($_SESSION['user_id'])): ?>
    <div class="d-flex justify-content-end" style="margin-right:20px;padding:10px">
    <button class="circle-plus-button buttonplus" onclick="location.href='add_project_task.php'">
        <i class="fas fa-plus"></i>
    </button>
    </div>
            <?php endif; ?>
        </div>
        </section>

        <?php include('./footer.php'); ?>
    </main>

    <?php include('./modals.php'); ?>

    <!-- JAVASCRIPT FILES -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/jquery.sticky.js"></script>
    <script src="js/click-scroll.js"></script>
    <script src="js/custom.js"></script>
    <script src="js/clock.js"></script>
    <script src="js/table.js"></script>
    <script src="js/logout_alert.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
     <script>
document.addEventListener("DOMContentLoaded", function () {
    const activitiesData = <?php echo json_encode($activityPercentages); ?>;
    const chartContainer = document.getElementById("3dBarChart").parentNode;

    if (activitiesData && activitiesData.length > 0) {
        const activityNames = activitiesData.map(item => item.activity_name);
        const activityPercentages = activitiesData.map(item => item.total_percentage);

        const getUniqueColor = () => `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.6)`;
        const barColors = activityNames.map(() => getUniqueColor());

        const canvas = document.getElementById('3dBarChart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: activityNames,
                    datasets: [
                        {
                            label: 'Activity Percentage',
                            data: activityPercentages,
                            backgroundColor: 'rgba(0, 123, 255, 0.5)',
                            borderColor: 'rgba(0, 123, 255, 1)',
                            borderWidth: 1,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Values (%)',
                            },
                            ticks: {
                                callback: (value) => value + '%',
                            },
                        }
                    },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: (tooltipItem) => `${tooltipItem.dataset.label}: ${tooltipItem.raw}%`,
                            }
                        }
                    }
                }
            });
        }
    } else {
        // Clear chart container
        chartContainer.innerHTML = "";

        // Display "No activities found" message
        const noDataMessage = document.createElement("p");
        noDataMessage.textContent = "No activities found";
        noDataMessage.style.color = "red";
        noDataMessage.style.textAlign = "center";
        noDataMessage.style.marginTop = "20px";
        noDataMessage.style.fontSize = "18px";
        noDataMessage.style.height = "265px";
        noDataMessage.style.width = "100%";

        chartContainer.appendChild(noDataMessage);
    }
});

</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const taskNames = <?php echo json_encode($taskNames); ?>;
    const daysLeftArray = <?php echo json_encode($daysLeftArray); ?>;
    const chartContainer = document.getElementById("2ndBarChart").parentNode;

    if (taskNames.length > 0 && daysLeftArray.length > 0) {
        const ctx = document.getElementById('2ndBarChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: taskNames, // Task names
                datasets: [{
                    label: 'Days Left (+/-)',
                    data: daysLeftArray, // Days left values (may negative)
                    backgroundColor: daysLeftArray.map(value => value >= 0 ? 'rgba(54, 162, 235, 0.2)' : 'rgba(255, 99, 132, 0.2)'), // Blue for positive, Red for negative
                    borderColor: daysLeftArray.map(value => value >= 0 ? 'rgba(54, 162, 235, 1)' : 'rgba(255, 99, 132, 1)'),
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y', // Horizontal bar chart
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + ' days'; // Add "days" suffix
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    } else {
        // No data handling
        chartContainer.innerHTML = "<p style='color: red; text-align: center; font-size: 18px;'>No data found</p>";
    }
});

</script>
<script>
     function liveSearch() {
        const input = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#taskTable tr');
        
        rows.forEach(row => {
            const columns = row.getElementsByTagName('td');
            let showRow = false;
            
            for (let i = 0; i < columns.length; i++) {
                const cellText = columns[i].textContent || columns[i].innerText;
                
                if (cellText.toLowerCase().includes(input)) {
                    showRow = true;
                    break;
                }
            }
            
            if (showRow) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
</body>
</html>
