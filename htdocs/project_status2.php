<?php
session_start();
include "db.php";
include "milestone_config.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/PHPMailer.php';
require __DIR__ . '/PHPMailer/SMTP.php';
require __DIR__ . '/PHPMailer/Exception.php';



function sendMilestoneMail($emails, $projectTitle, $milestone, $progress, $status) {

    if (empty($emails)) {
        error_log("Mail skipped: No recipients");
        return;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jecpro1947@gmail.com';
        $mail->Password   = 'unpudvmdouarklji'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($mail->Username, 'Project Monitoring System');
        $mail->addReplyTo($mail->Username);

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email);
            }
        }

        $mail->isHTML(true);
        $mail->Subject = "📌 Milestone Updated: $projectTitle";

        $mail->Body = "
        <h3>Milestone Update</h3>
        <p><b>Project:</b> $projectTitle</p>
        <p><b>Milestone:</b> $milestone</p>
        <p><b>Status:</b> $status</p>
        <p><b>Progress:</b> $progress%</p>
        <br>
        <p>— Faculty Mentor</p>
        ";

        $mail->send();

    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
    }
}




$project_id = (int)$_GET['project_id'];

// fetch project
$project = $conn->query("SELECT title, project_type FROM projects WHERE project_id=$project_id")->fetch_assoc();
$weights = ($project['project_type'] === 'Major') ? $MAJOR : $MINOR;
$validMilestones = array_keys($weights);


// auto-create milestones once
foreach ($weights as $title => $w) {
    $check = $conn->query("SELECT * FROM project_status WHERE project_id=$project_id AND milestone_title='$title'");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO project_status 
        (project_id, milestone_title, status, progress_percent)
        VALUES ($project_id,'$title','Not Started',0)");
    }
}
$emails = [];

// Leader email
if (!empty($project['leader_email'])) {
    $emails[] = $project['leader_email'];
}

// Team member emails
if (!empty($project['member1_email'])) {
    $emails[] = $project['member1_email'];
}

if (!empty($project['member2_email'])) {
    $emails[] = $project['member2_email'];
}

if (!empty($project['member3_email'])) {
    $emails[] = $project['member3_email'];
}

// Optional: remove duplicates
$emails = array_unique($emails);



// update milestone
if (isset($_POST['update'])) {

    $id = (int)$_POST['id'];
    $desc = $conn->real_escape_string($_POST['desc']);
    $prog = (int)$_POST['progress'];
    $status = $_POST['status'];

    // Fetch milestone title
    $ms = $conn->query("SELECT milestone_title FROM project_status WHERE status_id=$id")
               ->fetch_assoc();
    $milestoneTitle = $ms['milestone_title'];

    $conn->query("
        UPDATE project_status 
        SET description='$desc',
            progress_percent=$prog,
            status='$status' 
        WHERE status_id=$id
    ");

    // 📧 Send email
    sendMilestoneMail(
        $emails,
        $project['title'],
        $milestoneTitle,
        $prog,
        $status
    );

    header("Location: project_status.php?project_id=$project_id");
    exit();
}
$res = $conn->query("SELECT * FROM project_status WHERE project_id=$project_id");


// calculate overall progress
$total=0;
while($r=$res->fetch_assoc()){
  $title = trim($r['milestone_title']);

if (isset($weights[$title])) {
    $total += ($r['progress_percent'] * $weights[$title]) / 100;
}

}
$total=round($total,2);
$res->data_seek(0);
?>

<!DOCTYPE html>
<html>
<head>
<title>Faculty Milestones</title>
<style>
/* ===== Global ===== */
body{
    font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
    background: linear-gradient(135deg,#f1f5f9,#e2e8f0);
    padding:20px;
    color:#1f2937;
}

h2{
    margin-bottom:6px;
}

h3{
    margin-top:0;
    color:#0f766e;
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
    border-radius:16px;
    box-shadow:0 15px 35px rgba(0,0,0,.08);
    position:sticky;
    top:20px;
}

/* ===== Progress Bar ===== */
.progress{
    height:12px;
    background:#e5e7eb;
    border-radius:12px;
    overflow:hidden;
    margin:12px 0 20px;
}

.fill{
    height:100%;
    width:0;
    background:linear-gradient(135deg,#2563eb,#1d4ed8);
    transition:width 1s ease;
}

/* ===== Milestone Card ===== */
.card{
    background:#ffffff;
    padding:18px;
    border-radius:16px;
    margin-bottom:16px;
    box-shadow:0 12px 30px rgba(0,0,0,.08);
    border-left:5px solid #2563eb;
    transition:transform .2s ease, box-shadow .2s ease;
}

.card:hover{
    transform:translateY(-2px);
    box-shadow:0 18px 40px rgba(0,0,0,.12);
}

.card b{
    font-size:16px;
}

/* ===== Badge ===== */
.badge{
    background:#0f766e;
    color:#ffffff;
    padding:4px 12px;
    border-radius:20px;
    font-size:12px;
    margin-left:6px;
}

/* ===== Form Elements ===== */
textarea,
input,
select{
    width:100%;
    padding:10px 12px;
    margin-top:8px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:14px;
}

textarea{
    resize:vertical;
    min-height:60px;
}

textarea:focus,
input:focus,
select:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}

/* ===== Buttons ===== */
button{
    margin-top:10px;
    background:linear-gradient(135deg,#2563eb,#1e40af);
    color:#ffffff;
    border:none;
    padding:8px 14px;
    border-radius:10px;
    cursor:pointer;
    font-weight:600;
    transition:transform .2s ease, box-shadow .2s ease;
}

button:hover{
    transform:translateY(-1px);
    box-shadow:0 8px 20px rgba(37,99,235,.35);
}

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

/* ===== Responsive ===== */
@media (max-width: 900px){
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
<div class="layout">
<div class="main">
<h2><?= $project['title'] ?> – Overall Progress <?= $total ?>%</h2>
<p style="color:#475569;margin-bottom:10px;">
Project Type: 
<b style="color:#0f766e;"><?= htmlspecialchars($project['project_type']) ?></b>
</p>

<div class="progress"><div class="fill" style="width:<?= $total ?>%"></div></div>
<?php while ($m = $res->fetch_assoc()): ?>

<?php
$title = trim($m['milestone_title']);

// 🚫 Skip milestones not meant for this project type
if (!in_array($title, $validMilestones)) {
    continue;
}
?>

<div class="card">
<b><?= htmlspecialchars($title) ?></b>
<span class="badge"><?= $weights[$title] ?>%</span>

<form method="post">
<textarea name="desc" placeholder="Mentor description">
<?= htmlspecialchars($m['description']) ?>
</textarea>

<input type="number" name="progress"
       value="<?= $m['progress_percent'] ?>" min="0" max="100">

<select name="status">
<option <?= $m['status']=="Not Started"?"selected":"" ?>>Not Started</option>
<option <?= $m['status']=="In Progress"?"selected":"" ?>>In Progress</option>
<option <?= $m['status']=="Completed"?"selected":"" ?>>Completed</option>
</select>

<input type="hidden" name="id" value="<?= $m['status_id'] ?>">
<button name="update">Update</button>
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
<script>
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".fill").forEach(bar => {
        const width = bar.getAttribute("style");
        bar.style.width = "0";
        setTimeout(() => {
            bar.setAttribute("style", width);
        }, 100);
    });
});
</script>

</body>
</html>
