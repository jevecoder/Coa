<?php
include 'conn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $contractor_project = $_POST['contractor_project'];
    $action_officer = $_POST['action_officer'];
    $collaboration_with = $_POST['collaboration_with'];
    $subjects = $_POST['subjects'];
    $document_no = $_POST['document_no'];
    $date_received = $_POST['date_received'];
    $date_assigned = $_POST['date_assigned'];
    $agency = $_POST['agency'];
    $supervisor = $_POST['supervisor'];

    $sql = "UPDATE project_list SET contractor_project = :contractor_project, action_officer = :action_officer, 
            collaboration_with = :collaboration_with, subjects = :subjects, document_no = :document_no, 
            date_received = :date_received, date_assigned = :date_assigned, agency = :agency, supervisor = :supervisor 
            WHERE id = :id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id' => $id,
        ':contractor_project' => $contractor_project,
        ':action_officer' => $action_officer,
        ':collaboration_with' => $collaboration_with,
        ':subjects' => $subjects,
        ':document_no' => $document_no,
        ':date_received' => $date_received,
        ':date_assigned' => $date_assigned,
        ':agency' => $agency,
        ':supervisor' => $supervisor
    ]);
    echo "Data updated successfully!";
}
?>

<form method="POST" action="update.php">
    <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
    <button type="submit">Update</button>
</form>
