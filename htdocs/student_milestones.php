<?php
session_start();
include "db.php";

if ($_SESSION['role'] !== 'Student') {
    die("Access denied");
}

if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* FETCH PROJECT */
$project = $conn->query(
    "SELECT title, project_type FROM projects WHERE project_id=$project_id"

)->fetch_assoc();

/* FETCH MILESTONES */
$milestones = $conn->query(
    "SELECT * FROM project_status 
     WHERE project_id=$project_id 
     ORDER BY updated_at DESC"
);

/* HANDLE FILE UPLOAD */
if (isset($_POST['upload_files'])) {

    $status_id = (int)$_POST['status_id'];
    $baseDir = "uploads/milestones/project_$project_id/status_$status_id/";

    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0777, true);
    }

    foreach ($_FILES['files']['name'] as $i => $name) {

        $tmp  = $_FILES['files']['tmp_name'][$i];
        $size = $_FILES['files']['size'][$i];

        if ($size > 10 * 1024 * 1024) continue; // 10MB limit

        $safeName = preg_replace("/[^a-zA-Z0-9._-]/", "_", $name);
        move_uploaded_file($tmp, $baseDir . $safeName);
    }

    header("Location: student_milestones.php?project_id=$project_id&uploaded=1");

    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Project Milestones</title>

<style>
body{
    font-family:Segoe UI,Arial;
    background:#f1f5f9;
    padding:20px;
}
.container{
    max-width:1000px;
    margin:auto;
}
.card{
    background:#fff;
    padding:20px;
    border-radius:14px;
    margin-bottom:15px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    color:#fff;
    font-weight:600;
}
.Not\ Started{background:#94a3b8}
.In\ Progress{background:#f59e0b}
.Completed{background:#22c55e}
.On\ Hold{background:#ef4444}

.progress{
    height:8px;
    background:#e5e7eb;
    border-radius:10px;
    overflow:hidden;
}
.progress div{
    height:100%;
    background:#2563eb;
}

input[type=file]{
    margin-top:10px;
}
button{
    background:#2563eb;
    color:#fff;
    padding:8px 14px;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
.files a{
    display:block;
    color:#2563eb;
    text-decoration:none;
    font-size:14px;
}
.card.Completed{border-left:5px solid #22c55e}
.card.In\ Progress{border-left:5px solid #f59e0b}
.card.Not\ Started{border-left:5px solid #94a3b8}
.card.On\ Hold{border-left:5px solid #ef4444}
sidebar{
    flex:1;
    background:#ffffff;
    padding:18px;
    border-radius:14px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
    position:sticky;
    top:20px;
}

/* Sidebar heading */
.sidebar h3{
    margin-top:0;
    color:#0f766e;
}

/* Responsive */
@media (max-width:900px){
    .layout{
        flex-direction:column;
    }
    .sidebar{
        position:static;
    }
}

</style>
</head>

<body>
<?php if (isset($_GET['uploaded'])): ?>
<script>
alert("Files uploaded successfully");
</script>
<?php endif; ?>

<div class="container">
<h2>📌 Project Milestones – <?= htmlspecialchars($project['title']) ?></h2>
<div class="layout">
<div class="main">
<?php while($m = $milestones->fetch_assoc()): ?>
<div class="card <?= $m['status'] ?>">


<h3><?= htmlspecialchars($m['milestone_title']) ?></h3>
<p><?= nl2br(htmlspecialchars($m['description'])) ?></p>

<span class="badge <?= $m['status'] ?>">
<?= $m['status'] ?>
</span>

<p>Progress: <?= $m['progress_percent'] ?>%</p>
<div class="progress">
<div style="width:<?= $m['progress_percent'] ?>%"></div>
</div>

<h4>📎 Uploaded Files</h4>
<div class="files">
<?php
$dir = "uploads/milestones/project_$project_id/status_{$m['status_id']}/";
if (is_dir($dir)) {
    foreach (scandir($dir) as $f) {
        if ($f!='.' && $f!='..') {
            echo "<a href='$dir$f' target='_blank'>$f</a>";
        }
    }
} else {
    echo "<small>No files uploaded</small>";
}
?>
</div>

<?php if ($m['status'] === 'In Progress'): ?>


<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="status_id" value="<?= $m['status_id'] ?>">
    <input type="file" name="files[]" multiple required
           accept=".pdf,.doc,.docx,.ppt,.pptx,.zip">
    <button name="upload_files">Upload Files</button>
</form>

<?php else: ?>

<small style="color:#64748b;font-weight:600;">
⚠ File upload is allowed only when milestone status is <b>In Progress</b>.
</small>

<?php endif; ?>
</div>
<?php endwhile; ?>
</div>
<div class="sidebar">

<?php if ($project['project_type'] === 'Minor'): ?>

<h3>📌 Minor Project Weightage</h3>
<ul>
  <li>Problem Identification & Topic Selection – 10%</li>
  <li>Literature Review & Feasibility Study – 15%</li>
  <li>System Requirements & Design – 15%</li>
  <li>Implementation (Core Module) – 30%</li>
  <li>Testing & Debugging – 20%</li>
  <li>Documentation & Presentation – 10%</li>
</ul>

<?php elseif ($project['project_type'] === 'Major'): ?>

<h3>📌 Major Project Weightage</h3>
<ul>
  <li>Project Initiation & Ideation – 5%</li>
  <li>Literature Survey & Feasibility Study – 10%</li>
  <li>SRS & Documentation (Synopsis) – 10%</li>
  <li>System Design & Architecture – 15%</li>
  <li>Prototype Development (POC) – 10%</li>
  <li>Full-Stack Implementation – 30%</li>
  <li>Testing & Validation – 10%</li>
  <li>Final Deployment & Optimization – 5%</li>
  <li>Project Report & Viva Preparation – 5%</li>
</ul>

<?php endif; ?>

</div>
</div>
</body>
</html>
