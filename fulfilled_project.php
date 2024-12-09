<?php
include 'conn.php'; // Connection file

// Query to get all tasks with progress as 'Done'
$sql = "SELECT * FROM project_tasks WHERE progress = 'Done'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fulfilled Projects</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto my-8 p-6 bg-white shadow-lg rounded-lg">
        <h1 class="text-2xl font-bold mb-4">Fulfilled Projects</h1>
        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="py-2 px-4 border">ID</th>
                    <th class="py-2 px-4 border">Contractor Project</th>
                    <th class="py-2 px-4 border">Collaborators</th>
                    <th class="py-2 px-4 border">Supervisor</th>
                    <th class="py-2 px-4 border">Start Date</th>
                    <th class="py-2 px-4 border">Due Date</th>
                    <th class="py-2 px-4 border">Progress</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='py-2 px-4 border'>" . $row['id'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['contractor_project'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['collaborators'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['supervisor'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['start_date'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['due_date'] . "</td>";
                        echo "<td class='py-2 px-4 border'>" . $row['progress'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='py-2 px-4 border text-center'>No fulfilled projects found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
