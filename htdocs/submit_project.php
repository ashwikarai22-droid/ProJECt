<?php
session_start();
include "db.php";
echo "<pre>";
print_r($_SESSION);
exit();
// Only allow students
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Student') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Basic project info
    $title = $_POST['title'];
    $description = $_POST['description'];
    $project_type = $_POST['project_type'];
    $team_name = $_POST['team_name'];
    $total_members = $_POST['total_members'];

    // Leader info
    $leader_name = $_POST['leader_name'];
    $leader_roll_no = $_POST['leader_roll_no'];
    $leader_branch = $_POST['leader_branch'];
    $leader_semester = $_POST['leader_semester'];
    $leader_email = $_POST['leader_email'];
    $leader_phone = $_POST['leader_phone'];

    $requested_faculty_name = $_POST['requested_faculty_name'] ?? '';

    // Optional members info
    $members = [];
    for ($i = 1; $i <= 3; $i++) {
        $members[$i] = [
            'name' => $_POST["member{$i}_name"] ?? '',
            'roll_no' => $_POST["member{$i}_roll_no"] ?? '',
            'branch' => $_POST["member{$i}_branch"] ?? '',
            'email' => $_POST["member{$i}_email"] ?? '',
            'phone' => $_POST["member{$i}_phone"] ?? '',
            'sem' => $_POST["member{$i}_sem"] ?? '',
        ];
    }

    $student_id = $_SESSION['user_id'];

    // Handle file upload
    $project_file = '';
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] == 0) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        $project_file = 'uploads/' . time() . '_' . basename($_FILES['project_file']['name']);
        move_uploaded_file($_FILES['project_file']['tmp_name'], $project_file);
    }

    // Prepare insert query
    $sql = "INSERT INTO projects 
    (title, team_name, total_members, project_file, description, project_type, student_id,
     leader_name, leader_roll_no, leader_branch, leader_semester, leader_email, leader_phone,
     member1_name, member1_roll_no, member1_branch, member1_email, member1_phone, member1_sem,
     member2_name, member2_roll_no, member2_branch, member2_email, member2_phone, member2_sem,
     member3_name, member3_roll_no, member3_branch, member3_email, member3_phone, member3_sem,
     requested_faculty_name, mentorship_request_status, status)
    VALUES
    ('$title', '$team_name', '$total_members', '$project_file', '$description', '$project_type', '$student_id',
     '$leader_name', '$leader_roll_no', '$leader_branch', '$leader_semester', '$leader_email', '$leader_phone',
     '{$members[1]['name']}', '{$members[1]['roll_no']}', '{$members[1]['branch']}', '{$members[1]['email']}', '{$members[1]['phone']}', '{$members[1]['sem']}',
     '{$members[2]['name']}', '{$members[2]['roll_no']}', '{$members[2]['branch']}', '{$members[2]['email']}', '{$members[2]['phone']}', '{$members[2]['sem']}',
     '{$members[3]['name']}', '{$members[3]['roll_no']}', '{$members[3]['branch']}', '{$members[3]['email']}', '{$members[3]['phone']}', '{$members[3]['sem']}',
     '$requested_faculty_name', 'Pending', 'Proposed')";

    if ($conn->query($sql) === TRUE) {
        header("Location: student.php?success=1");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f0f2f5; }
        h1, h2 { color: #333; }
        .card { background: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
        .team { background: #f9f9f9; padding: 10px; margin-top: 10px; border-radius: 6px; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0 10px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 20px; background: #28a745; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #218838; }
        hr { margin: 15px 0; }
    </style>
</head>
<body>

<h1>Student Dashboard</h1>

<div class="card">
    <h2>Submit Project</h2>
    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="title" placeholder="Project Title" required>
        <textarea name="description" placeholder="Project Description" required></textarea>
        <select name="project_type" required>
            <option value="Minor">Minor</option>
            <option value="Major">Major</option>
        </select>
        <input type="text" name="team_name" placeholder="Team Name" required>
        <input type="number" name="total_members" placeholder="Total Members" min="1" max="4" required>
        <input type="file" name="project_file" required>

        <hr>
        <h3>Team Leader Details</h3>
        <input type="text" name="leader_roll_no" placeholder="Leader Roll No" required>
        <input type="text" name="leader_name" placeholder="Leader Name" required>

        <select name="leader_branch" required>
            <option value="">Select Branch</option>
            <option value="AI&DS">Artificial Intelligence and Data Science (AI&DS)</option>
            <option value="CS">Computer Science (CS)</option>
            <option value="IT">Information Technology (IT)</option>
            <option value="ME">Mechanical Engineering (ME)</option>
            <option value="CE">Civil Engineering (CE)</option>
            <option value="IP">Industrial Production (IP)</option>
            <option value="Electrical">Electrical</option>
            <option value="EC">Electronics and Telecommunication (EC)</option>
            <option value="MT">Mechatronics (MT)</option>
        </select>

        <select name="leader_semester" required>
            <option value="">Select Semester</option>
            <option value="I">I</option>
            <option value="II">II</option>
            <option value="III">III</option>
            <option value="IV">IV</option>
            <option value="V">V</option>
            <option value="VI">VI</option>
            <option value="VII">VII</option>
            <option value="VIII">VIII</option>
        </select>

        <input type="email" name="leader_email" placeholder="Leader Email" required>
        <input type="text" name="leader_phone" placeholder="Leader Phone" required>

        <hr>
        <h3>Team Members (Optional)</h3>
        <?php for ($i = 1; $i <= 3; $i++) { ?>
            <h4>Member <?= $i ?></h4>
            <input type="text" name="member<?= $i ?>_roll_no" placeholder="Roll No">
            <input type="text" name="member<?= $i ?>_name" placeholder="Name">

            <select name="member<?= $i ?>_branch">
                <option value="">Select Branch</option>
                <option value="AI&DS">Artificial Intelligence and Data Science (AI&DS)</option>
                <option value="CS">Computer Science (CS)</option>
                <option value="IT">Information Technology (IT)</option>
                <option value="ME">Mechanical Engineering (ME)</option>
                <option value="CE">Civil Engineering (CE)</option>
                <option value="IP">Industrial Production (IP)</option>
                <option value="Electrical">Electrical</option>
                <option value="EC">Electronics and Telecommunication (EC)</option>
                <option value="MT">Mechatronics (MT)</option>
            </select>

            <select name="member<?= $i ?>_sem">
                <option value="">Select Semester</option>
                <option value="I">I</option>
                <option value="II">II</option>
                <option value="III">III</option>
                <option value="IV">IV</option>
                <option value="V">V</option>
                <option value="VI">VI</option>
                <option value="VII">VII</option>
                <option value="VIII">VIII</option>
            </select>

            <input type="email" name="member<?= $i ?>_email" placeholder="Email">
            <input type="text" name="member<?= $i ?>_phone" placeholder="Phone">
            <hr>
        <?php } ?>

        <input type="text" name="requested_faculty_name" placeholder="Request Mentorship from Faculty">

        <button type="submit">Submit Project</button>
    </form>
</div>

</body>
</html>
