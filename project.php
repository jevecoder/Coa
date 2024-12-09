<?php
// Include database connection
include('conn.php');

// Check if login success session exists for Toastr message
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] == true) {
    $showToastr = true;
    unset($_SESSION['login_success']);
} else {
    $showToastr = false;
}

$query = "
    SELECT 
    p.id,
    p.contractor_project,
    p.subjects,
    p.document_no,
    p.date_received,
    p.date_assigned,
    p.agency,
    p.user_id,
    p.supervisor,
    p.collaborators,
    u1.name AS action_officer_name,
    u2.name AS supervisor_name,
    GROUP_CONCAT(u3.name) AS collaborator_names
FROM 
    projects p
LEFT JOIN users u1 ON p.user_id = u1.id
LEFT JOIN users u2 ON p.supervisor = u2.id
LEFT JOIN users u3 ON FIND_IN_SET(u3.id, p.collaborators) > 0
GROUP BY p.id
";

// Execute query to fetch projects
$stmt = $conn->prepare($query);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $projects = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];
} else {
    die("Error executing query: " . $stmt->error);
}

?>


<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    </style>
</head>

<body>
    <?php include('loader.html'); ?>

    <main>
        <?php include('./nav.php'); ?>

        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1"></section>

        <!-- Toastr Notification -->
        <?php if ($showToastr): ?>
        <script>
            toastr.success('Project added successfully!', 'Success');
        </script>
        <?php endif; ?>

        <section class="p-2 table-responsive d-flex flex-column justify-content-center w-100" style="width: 100%;margin-top:-10%">
    <div style="z-index:5;" class="w-100 flex-column d-flex">
            <table class="table bg-white custom-table table-hover mb-0 align-self-center justify-content-center">
                <thead class="table-header bg-dark">
                    <tr style="font-size: 10px;">
                        <th>CONTRACTOR / PROJECT</th>
                        <th>ACTION OFFICER</th>
                        <th>Collaboration With (initials only)</th>
                        <th>SUBJECT</th>
                        <th>DOCUMENT NO.</th>
                        <th>AGENCY</th>
                        <th>SUPERVISOR/ TEAM LEADER</th>
                        <?php if (isset($_SESSION['user_id'])): ?>
                        <th>DATE RECEIVED</th>
                        <th>DATE ASSIGNED</th>
                        <th>ACTION</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($projects)): ?>
                        <?php foreach ($projects as $project): ?>
                            <tr style="font-size: 10px;text-align:center">
                                <td ><?php echo htmlspecialchars($project['contractor_project']); ?></td>
                                <td ><?php echo htmlspecialchars($project['action_officer_name'] ?? 'Unknown'); ?></td>
                                <td ><?php echo htmlspecialchars($project['collaborator_names']); ?></td>
                                <td ><?php echo htmlspecialchars($project['subjects']); ?></td>
                                <td ><?php echo htmlspecialchars($project['document_no']); ?></td>
                                <td ><?php echo htmlspecialchars($project['agency']); ?></td>
                                <td ><?php echo htmlspecialchars($project['supervisor_name'] ?? 'Unknown'); ?></td>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <td ><?php echo htmlspecialchars($project['date_received']); ?></td>
                                <td ><?php echo htmlspecialchars($project['date_assigned']); ?></td>
                                <td >
                                        <a class="btn btn-dark btn-sm" href="edit_project.php?id=<?php echo $project['id']; ?>"><i class="fa fa-pencil"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="text-align: center;">No projects available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="d-flex justify-content-end" style="margin-right:20px;padding:10px">
            <button class="circle-plus-button buttonplus" onclick="location.href='add_project.php'">
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

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</body>
</html>
