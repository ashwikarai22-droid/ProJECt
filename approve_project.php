<?php
session_start();
include "db.php";

// Ensure the user is logged in as Faculty
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Faculty') {
    header("Location: login.php");
    exit();
}

// Get project ID from GET
if (!isset($_GET['id'])) {
    header("Location: mentorship_requests.php");
    exit();
}

$project_id = intval($_GET['id']);
$mentor_id  = $_SESSION['user_id'];

// Update the project: set mentorship_request_status to Approved and assign mentor
$stmt = $conn->prepare("UPDATE projects SET mentorship_request_status='Approved', mentor_id=? WHERE project_id=?");
$stmt->bind_param("ii", $mentor_id, $project_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: mentorship_requests.php");
    exit();
} else {
    echo "Error updating project: " . $conn->error;
}

?>
