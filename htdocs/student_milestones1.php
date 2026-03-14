<?php
session_start();
include "db.php";
include "milestone_config.php";

if ($_SESSION['role'] !== 'Student') die("Access denied");

$project_id=(int)$_GET['project_id'];
$project=$conn->query("SELECT title,project_type FROM projects WHERE project_id=$project_id")->fetch_assoc();
$weights=($project['project_type']==='Major')?$MAJOR:$MINOR;

$res=$conn->query("SELECT * FROM project_status WHERE project_id=$project_id");

// upload
if(isset($_POST['upload'])){
  $sid=(int)$_POST['sid'];
  $dir="uploads/milestones/project_$project_id/status_$sid/";
  if(!is_dir($dir)) mkdir($dir,0777,true);
  foreach($_FILES['files']['name'] as $i=>$n){
    move_uploaded_file($_FILES['files']['tmp_name'][$i],$dir.$n);
  }
  header("Location: student_milestones.php?project_id=$project_id");
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Student Milestones</title>
<style>
body{font-family:Segoe UI;background:#eef2f7;padding:20px}
.card{background:#fff;padding:16px;border-radius:14px;margin-bottom:12px}
.progress{height:8px;background:#e5e7eb;border-radius:10px}
.fill{height:100%;background:#16a34a}
/* ===== Sidebar ===== */
.sidebar ul{
    padding-left:18px;
    margin:10px 0 0;
}

.sidebar li{
    margin-bottom:10px;
    font-size:14px;
    color:#334155;
}
/* ===== Layout ===== */
.layout{
    display:flex;
    gap:24px;
    align-items:flex-start;
}

.main{
    flex:3;
}

.sidebar{
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
<h2><?= $project['title'] ?> – Milestones</h2>
<div class="layout">
<div class="main">


<?php while($m=$res->fetch_assoc()): ?>
<div class="card">
<b><?= $m['milestone_title'] ?></b>
<p><?= $m['description'] ?></p>
<div class="progress"><div class="fill" style="width:<?= $m['progress_percent'] ?>%"></div></div>
<small><?= $m['progress_percent'] ?>%</small>

<form method="post" enctype="multipart/form-data">
<input type="file" name="files[]" multiple required>
<input type="hidden" name="sid" value="<?= $m['status_id'] ?>">
<button name="upload">Upload</button>
</form>
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
