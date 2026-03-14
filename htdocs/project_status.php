<?php
session_start();
include "db.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

/* =========================
   EMAIL NOTIFICATION
   ========================= */
function notifyTeam($conn, $project_id, $action, $milestone) {

    // Fetch team emails from projects table
    $res = $conn->query("
        SELECT leader_email, member1_email, member2_email, member3_email
        FROM projects
        WHERE project_id = $project_id
    ");

    if (!$res || $res->num_rows == 0) return;

    $row = $res->fetch_assoc();

    // Remove empty emails
    $emails = array_filter($row);

    $subject = "Project Milestone $action";
    $message = "Hello Team,

A project milestone has been $action by the mentor.

Milestone Title: $milestone

Please log in to the portal to view the latest update.

Regards,
Project Monitoring System";

    foreach ($emails as $to) {

    $mail = new PHPMailer(true);

    try {
        // SMTP SETTINGS
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'yourgmail@gmail.com';   // 🔴 your Gmail
        $mail->Password   = 'YOUR_APP_PASSWORD';     // 🔴 Gmail App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // EMAIL HEADERS
        $mail->setFrom('yourgmail@gmail.com', 'Project Monitoring System');
        $mail->addAddress($to);

        // EMAIL CONTENT
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = nl2br($message);

        $mail->send();

    } catch (Exception $e) {
        // Optional: error log
        // error_log($mail->ErrorInfo);
    }
}
}



if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Faculty') {
    die("Access denied");
}



if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* FETCH PROJECT */
$project = $conn->query(
    "SELECT title FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* SAVE / UPDATE STATUS */
if (isset($_POST['save_status'])) {
    $title = $conn->real_escape_string($_POST['milestone_title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $status = $_POST['status'];
    $progress = (int)$_POST['progress'];

    $conn->query("
        INSERT INTO project_status 
        (project_id, milestone_title, description, status, progress_percent)
        VALUES 
        ($project_id, '$title', '$desc', '$status', $progress)
    ");
	notifyTeam($conn, $project_id, "added", $title);


    header("Location: project_status.php?project_id=$project_id");
    exit();
}
/* UPDATE EXISTING STATUS */
if (isset($_POST['update_status'])) {
    $status_id = (int)$_POST['status_id'];
    $title = $conn->real_escape_string($_POST['milestone_title']);
    $desc  = $conn->real_escape_string($_POST['description']);
    $status = $_POST['status'];
    $progress = (int)$_POST['progress'];

    $conn->query("
        UPDATE project_status SET
        milestone_title='$title',
        description='$desc',
        status='$status',
        progress_percent=$progress,
        updated_at=NOW()
        WHERE status_id=$status_id
    ");
notifyTeam($conn, $project_id, "updated", $title);

    header("Location: project_status.php?project_id=$project_id");
    exit();
}


/* FETCH STATUS */
$statusList = $conn->query(
    "SELECT * FROM project_status WHERE project_id=$project_id ORDER BY updated_at DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Project Status</title>
<style>
*{box-sizing:border-box}

body{
    font-family:'Segoe UI',Arial,sans-serif;
    background-image: url("statusimage.jpg");
    padding:20px;
}

.box{
    max-width:900px;
    margin:auto;
    background:#fff;
    padding:25px;
    border-radius:16px;
    box-shadow:0 15px 40px rgba(0,0,0,.08);
}

h2,h3{color:#2c3e50}

input,textarea,select,button{
    width:100%;
    padding:10px;
    margin-bottom:10px;
    border-radius:8px;
    border:1px solid #d1d5db;
    font-size:14px;
}

input:focus,textarea:focus,select:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.2);
}

button{
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:transform .2s, box-shadow .2s;
}

button:hover{
    transform:translateY(-1px);
    box-shadow:0 8px 20px rgba(37,99,235,.35);
}

/* Milestone Card */
.card{
    background:#f8fafc;
    padding:15px;
    border-radius:12px;
    margin-bottom:12px;
    border-left:5px solid #2563eb;
    animation:fadeSlide .3s ease;
}

@keyframes fadeSlide{
    from{opacity:0;transform:translateY(-5px)}
    to{opacity:1;transform:translateY(0)}
}

.badge{
    display:inline-block;
    padding:4px 10px;
    border-radius:14px;
    font-size:12px;
    color:#fff;
    font-weight:600;
}

.not-started{background:#94a3b8}
.in-progress{background:#f59e0b}
.completed{background:#22c55e}
.on-hold{background:#ef4444}

/* Progress Bar */
.bar{
    height:10px;
    background:#e5e7eb;
    border-radius:10px;
    overflow:hidden;
    margin-top:6px;
}

.fill{
    height:100%;
    transition:width .4s ease;
}

.fill.low{background:#ef4444}
.fill.medium{background:#f59e0b}
.fill.high{background:#22c55e}

small{color:#64748b}

/* Collapsible */
.toggle{
    cursor:pointer;
    font-weight:600;
    color:#2563eb;
}
.details{
    display:none;
    margin-top:8px;
}
</style>

</head>

<body>

<div class="box">
<h2>Project Status – <?= htmlspecialchars($project['title']) ?></h2>

<!-- ADD MILESTONE -->
<form method="post" id="statusForm">


<!-- REQUIRED FOR EDIT -->
<input type="hidden" name="status_id" id="status_id">

<input name="milestone_title" id="milestone_title"
       placeholder="Milestone title" required>

<textarea name="description" id="description"
          placeholder="Description"></textarea>

<select name="status" id="status">
<option>Not Started</option>
<option>In Progress</option>
<option>Completed</option>
<option>On Hold</option>
</select>

<input type="number" name="progress" id="progress"
       placeholder="Progress %" min="0" max="100">

<!-- ADD MODE -->
<button name="save_status" id="saveBtn">
Save Milestone
</button>

<!-- EDIT MODE -->
<button name="update_status" id="updateBtn"
        style="display:none;background:#16a34a">
Update Milestone
</button>

</form>


<hr>

<h3>Milestone History</h3>

<?php if ($statusList->num_rows == 0): ?>
<p>No milestones added yet.</p>
<?php endif; ?>

<?php while($s = $statusList->fetch_assoc()): ?>
<div class="card">

<b><?= htmlspecialchars($s['milestone_title']) ?></b><br>
<?= nl2br(htmlspecialchars($s['description'])) ?><br><br>

Status: <b><?= $s['status'] ?></b><br>
Progress: <?= $s['progress_percent'] ?>%

<div class="bar">
    <div class="fill" style="width:<?= $s['progress_percent'] ?>%"></div>
</div>

<!-- 🔹 ADD THIS EDIT BUTTON -->
<button onclick='editMilestone(
    <?= $s["status_id"] ?>,
    <?= json_encode($s["milestone_title"]) ?>,
    <?= json_encode($s["description"]) ?>,
    <?= json_encode($s["status"]) ?>,
    <?= $s["progress_percent"] ?>
)' style="margin-top:8px;background:#f59e0b">
Edit
</button>

<br><br>
<small>Updated: <?= $s['updated_at'] ?></small>

</div>
<?php endwhile; ?>


</div>
<script>
/* COLLAPSE MILESTONE DETAILS */
function toggleDetails(id){
    const el=document.getElementById(id);
    el.style.display = (el.style.display==='block')?'none':'block';
}

/* PROGRESS COLOR LOGIC */
document.querySelectorAll('.fill').forEach(bar=>{
    const val=parseInt(bar.dataset.val);
    if(val<40) bar.classList.add('low');
    else if(val<75) bar.classList.add('medium');
    else bar.classList.add('high');
});

/* SIMPLE FORM VALIDATION */
document.querySelector('form').addEventListener('submit',e=>{
    const p=document.querySelector('input[name="progress"]').value;
    if(p<0 || p>100){
        alert("Progress must be between 0 and 100");
        e.preventDefault();
    }
});
</script>
<script>
function editMilestone(id, title, desc, status, progress) {

    // 1️⃣ Set hidden status ID (VERY IMPORTANT)
    document.getElementById('status_id').value = id;

    // 2️⃣ Fill form fields
    document.getElementById('milestone_title').value = title;
    document.getElementById('description').value = desc;
    document.getElementById('status').value = status;
    document.getElementById('progress').value = progress;

    // 3️⃣ Switch form mode (Add → Edit)
    document.getElementById('saveBtn').style.display = 'none';
    document.getElementById('updateBtn').style.display = 'block';

    // 4️⃣ Smooth scroll to form
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>
<script>
/* RESET FORM AFTER UPDATE OR MANUAL RESET */
document.getElementById('statusForm').addEventListener('reset', function () {

    // Clear hidden ID
    document.getElementById('status_id').value = '';

    // Switch buttons back
    document.getElementById('saveBtn').style.display = 'block';
    document.getElementById('updateBtn').style.display = 'none';
});
</script>


</body>
</html>
