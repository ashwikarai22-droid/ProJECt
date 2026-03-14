<?php
session_start();
include "db.php";
$branches = ['AIDS','CS','IT','ME','CE','IP','EE','EC'];
$semesters = ['I','II','III','IV','V','VI','VII','VIII'];
// Check if logged-in user is a Student
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$isFaculty = ($_SESSION['role'] === 'Faculty');


// Ensure the logged-in student exists in the database
$student_id = (int)$_SESSION['user_id'];
$check = $conn->prepare("SELECT * FROM users WHERE user_id=?");
$check->bind_param("i", $student_id);
$check->execute();
$student_res = $check->get_result();
if ($student_res->num_rows === 0) {
    die("Error: logged-in student does not exist in the users table.");
}
$check->close();

/* =========================
   HANDLE PROJECT SUBMISSION
   ========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $project_type = $_POST['project_type'];
    $team_name = $_POST['team_name'];
    $total_members = (int)$_POST['total_members'];

    $leader_name = $_POST['leader_name'];
    $leader_roll_no = $_POST['leader_roll_no'];
    $leader_branch = $_POST['leader_branch'];
    $leader_semester = $_POST['leader_semester'];
    $leader_email = $_POST['leader_email'];
    $leader_phone = $_POST['leader_phone'];

    $requested_faculty_name = $_POST['requested_faculty_name'] ?? '';

    // Optional members
    $members = [];
    for ($i = 1; $i <= 3; $i++) {
        $members[$i] = [
            'name' => $_POST["member{$i}_name"] ?? '',
            'roll_no' => $_POST["member{$i}_roll_no"] ?? '',
            'branch' => $_POST["member{$i}_branch"] ?? '',
            'email' => $_POST["member{$i}_email"] ?? '',
            'phone' => $_POST["member{$i}_phone"] ?? '',
            'sem' => $_POST["member{$i}_sem"] ?? ''
        ];
    }

    // File upload
    $project_file = '';
    if (isset($_FILES['project_file']) && $_FILES['project_file']['error'] === 0) {
        if (!is_dir('uploads')) mkdir('uploads', 0777, true);
        $project_file = 'uploads/' . time() . '_' . basename($_FILES['project_file']['name']);
        move_uploaded_file($_FILES['project_file']['tmp_name'], $project_file);
    }
	

    /* =========================
       PREPARED STATEMENT INSERT
       ========================= */
    $stmt = $conn->prepare("
        INSERT INTO projects (
            title, team_name, total_members, project_file, description, project_type, student_id,
            leader_name, leader_roll_no, leader_branch, leader_semester, leader_email, leader_phone,
            member1_name, member1_roll_no, member1_branch, member1_email, member1_phone, member1_sem,
            member2_name, member2_roll_no, member2_branch, member2_email, member2_phone, member2_sem,
            member3_name, member3_roll_no, member3_branch, member3_email, member3_phone, member3_sem,
            requested_faculty_name, mentorship_request_status, status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, ?, ?, ?, ?, ?,
            ?, 'Pending', 'Proposed'
        )
    ");

    $stmt->bind_param(
        "ssisssisssssssssssssssssssssssss",
        $title,
        $team_name,
        $total_members,
        $project_file,
        $description,
        $project_type,
        $student_id,

        $leader_name,
        $leader_roll_no,
        $leader_branch,
        $leader_semester,
        $leader_email,
        $leader_phone,

        $members[1]['name'],
        $members[1]['roll_no'],
        $members[1]['branch'],
        $members[1]['email'],
        $members[1]['phone'],
        $members[1]['sem'],

        $members[2]['name'],
        $members[2]['roll_no'],
        $members[2]['branch'],
        $members[2]['email'],
        $members[2]['phone'],
        $members[2]['sem'],

        $members[3]['name'],
        $members[3]['roll_no'],
        $members[3]['branch'],
        $members[3]['email'],
        $members[3]['phone'],
        $members[3]['sem'],

        $requested_faculty_name
    );

    $stmt->execute();
    $stmt->close();

    header("Location: student.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { padding: 20px; background: linear-gradient(135deg, #e0eafc, #cfdef3); color: #333; }
        h1 { text-align: center; margin-bottom: 30px; font-size: 32px; }
        h2 { margin-bottom: 15px; }
        .card { background: #fff; padding: 25px; border-radius: 16px; margin-bottom: 30px; box-shadow: 0 10px 25px rgba(0,0,0,0.12); }
        input, select, textarea { width: 100%; padding: 10px; margin: 6px 0 14px; border-radius: 6px; border: 1px solid #ccc; }
        textarea { resize: vertical; min-height: 90px; }
        hr { border: none; height: 1px; background: #ddd; margin: 18px 0; }
        button { padding: 12px 26px; background: #28a745; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; }
        .team { background: #f8f9fc; padding: 16px; margin-top: 18px; border-radius: 12px; border-left: 5px solid #4e73df; }
		.actions{
    display:flex;
    gap:12px;
    margin-top:15px;
    flex-wrap:wrap;
}

.btn{
    padding:10px 18px;
    border:none;
    border-radius:8px;
    font-size:14px;
    cursor:pointer;
    font-weight:600;
    transition:.25s;
}

.btn:hover{
    transform:translateY(-2px);
}

.kanban{
    background:#2563eb;
    color:#fff;
}

.gantt{
    background:#9333ea;
    color:#fff;
}

.status{
    background:#16a34a;
    color:#fff;
}

/* Status badge */
.badge{
    padding:4px 10px;
    border-radius:14px;
    color:#fff;
    font-size:12px;
    font-weight:600;
}

.proposed{background:#64748b}
.approved{background:#22c55e}
.rejected{background:#ef4444}

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
        <label>Leader Branch</label>
<select name="leader_branch" id="branch" required>
    <option value="">-- Select Branch --</option>
    <?php foreach ($branches as $b): ?>
        <option value="<?= $b ?>"><?= $b ?></option>
    <?php endforeach; ?>
</select>

<label>Leader Semester</label>
<select name="leader_semester" required>
    <option value="">-- Select Semester --</option>
    <?php foreach ($semesters as $s): ?>
        <option value="<?= $s ?>"><?= $s ?></option>
    <?php endforeach; ?>
</select>

        <input type="email" name="leader_email" placeholder="Leader Email" required>
        <input type="text" name="leader_phone" placeholder="Leader Phone" required>

        <hr>
        <h3>Team Members (Optional)</h3>
        <?php for ($i = 1; $i <= 3; $i++) { ?>
            <h4>Member <?= $i ?></h4>
            <input type="text" name="member<?= $i ?>_roll_no" placeholder="Roll No">
            <input type="text" name="member<?= $i ?>_name" placeholder="Name">
            <label>Meader Branch</label>
<select name="member<?= $i ?>_branch" id="branch" required>
    <option value="">-- Select Branch --</option>
    <?php foreach ($branches as $b): ?>
        <option value="<?= $b ?>"><?= $b ?></option>
    <?php endforeach; ?>
</select>

<label>Member Semester</label>
<select name="member<?= $i ?>_semester" required>
    <option value="">-- Select Semester --</option>
    <?php foreach ($semesters as $s): ?>
        <option value="<?= $s ?>"><?= $s ?></option>
    <?php endforeach; ?>
</select>

            <input type="email" name="member<?= $i ?>_email" placeholder="Email">
            <input type="text" name="member<?= $i ?>_phone" placeholder="Phone">
            <hr>
        <?php } ?>

        <input type="text" name="requested_faculty_name" placeholder="Request Mentorship from Faculty">

        <button type="submit">Submit Project</button>
    </form>
</div>

<div class="card">
    <h2>My Projects</h2>
    <?php
    $result = $conn->query("SELECT * FROM projects WHERE student_id=$student_id");

    if ($result->num_rows == 0) {
        echo "<p>No projects submitted yet.</p>";
    }

    while ($row = $result->fetch_assoc()) {
    ?>
        <div class="team">

<p><b>Title:</b> <?= htmlspecialchars($row['title']) ?></p>
<p><b>Description:</b><br><?= nl2br(htmlspecialchars($row['description'])) ?></p>

<p><b>Type:</b> <?= $row['project_type'] ?></p>
<p><b>Team Name:</b> <?= htmlspecialchars($row['team_name']) ?></p>
<p><b>Total Members:</b> <?= $row['total_members'] ?></p>

<p>
<b>Project Status:</b>
<span class="badge <?= strtolower($row['status']) ?>">
<?= $row['status'] ?>
</span>
</p>

<hr>

<h4>Team Leader</h4>
<p>
<?= htmlspecialchars($row['leader_name']) ?>  
(<?= htmlspecialchars($row['leader_roll_no']) ?>,  
<?= $row['leader_branch'] ?>,  
<?= $row['leader_semester'] ?>)
</p>
<p>Email: <?= $row['leader_email'] ?> | Phone: <?= $row['leader_phone'] ?></p>

<?php for ($i = 1; $i <= 3; $i++): ?>
<?php if (!empty($row["member{$i}_name"])): ?>
<hr>
<h4>Member <?= $i ?></h4>
<p>
<?= htmlspecialchars($row["member{$i}_name"]) ?>
(<?= $row["member{$i}_roll_no"] ?>,
<?= $row["member{$i}_branch"] ?>,
<?= $row["member{$i}_sem"] ?>)
</p>
<p>Email: <?= $row["member{$i}_email"] ?> | Phone: <?= $row["member{$i}_phone"] ?></p>
<?php endif; ?>
<?php endfor; ?>

<?php if (!empty($row['requested_faculty_name'])): ?>
<hr>
<p>
<b>Mentorship Requested:</b>
<?= htmlspecialchars($row['requested_faculty_name']) ?>
(<?= $row['mentorship_request_status'] ?>)
</p>
<?php endif; ?>
<label>Request Mentorship From Faculty</label>
<select name="requested_faculty_name" id="faculty" required>
    <option value="">-- Select Faculty --</option>
</select>




<!-- 🔹 STUDENT ACTION BUTTONS -->
<hr>

<div class="actions">

<a href="kanban.php?project_id=<?= $row['project_id'] ?>">
    <button class="btn kanban">Kanban Board</button>
</a>

<a href="gantt_from_kanban.php?project_id=<?= $row['project_id'] ?>">
    <button class="btn gantt">Gantt Chart</button>
</a>


<a href="forum.php?project_id=<?= $row['project_id'] ?>">
    <button class="btn kanban" style="background:#f39c12">Q&A Forum</button>
</a>
	<a href="student_milestones.php?project_id=<?= $row['project_id'] ?>">
        <button class="btn milestones">Milestones & Files</button>
    </a>
	<a href="student_calender.php?project_id=<?= $row['project_id'] ?>">
        <button class="btn milestones">Calender</button>
    </a>

</div>
</div>
<?php } ?>
<script>
document.getElementById('branch').addEventListener('change', function () {
    const branch = this.value;
    const facultyDropdown = document.getElementById('faculty');

    facultyDropdown.innerHTML = '<option>Loading...</option>';

    fetch('get_faculty.php?branch=' + branch)
        .then(res => res.text())
        .then(data => {
            facultyDropdown.innerHTML = data;
        });
});
</script>


</body>
</html>
