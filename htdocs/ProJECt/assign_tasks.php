<?php

session_start();
include "db.php";


if (!isset($_SESSION['role']) || $_SESSION['role'] != 'Faculty') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* ==========================
   SAVE ASSIGNED TASK (ONLY ONCE)
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') 
 {

    $title    = $conn->real_escape_string($_POST['task_title']);
    $assigned = $conn->real_escape_string($_POST['assigned_to']);
    $priority = $_POST['priority'];
    $due      = $_POST['due_date'];

    $sql = "
        INSERT INTO kanban
        (project_id, task_title, assigned_to, priority, due_date, status)
        VALUES
        ($project_id, '$title', '$assigned', '$priority', '$due', 'Todo')
    ";

    if (!$conn->query($sql)) {
        die("DB Error: " . $conn->error);
    }

    header("Location: assign_tasks.php?project_id=$project_id");
    exit();
}



/* ==========================
   FETCH PROJECT
========================== */
$project = $conn->query(
    "SELECT * FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* ==========================
   FETCH TASKS (FROM KANBAN)
========================== */
$tasks = $conn->query(
    "SELECT * FROM kanban WHERE project_id=$project_id ORDER BY created_at DESC"
);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare(
        "INSERT INTO kanban
        (project_id, task_title, assigned_to, priority, due_date, status)
        VALUES (?, ?, ?, ?, ?, 'Todo')"
    );

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "issss",
        $project_id,
        $_POST['task_title'],
        $_POST['assigned_to'],
        $_POST['priority'],
        $_POST['due_date']
    );

    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }

    $stmt->close();

    header("Location: assign_tasks.php?project_id=$project_id");
    exit();
}





?>


<!DOCTYPE html>
<html>
<head>
<title>Assign Tasks</title>

<style>
*{
    box-sizing:border-box;
    font-family:'Segoe UI',system-ui;
}
body{
    background-image: url("task1.jpg");
    <padding:30px;>
    color:#111827;
}
.box{
    background:#fff;
    padding:30px;
    border-radius:18px;
    max-width:760px;
    margin:auto;
    box-shadow:0 20px 40px rgba(0,0,0,.1);
    animation:fadeIn .5s ease;
}
@keyframes fadeIn{
    from{opacity:0;transform:translateY(10px)}
    to{opacity:1;transform:none}
}
h2{
    margin-bottom:20px;
    color:#1e3a8a;
}
h3{
    margin-top:30px;
    color:#1f2937;
}
label{
    font-size:14px;
    font-weight:600;
    margin-top:10px;
    display:block;
}
input,select,button{
    width:100%;
    padding:12px;
    margin-top:6px;
    margin-bottom:14px;
    border-radius:10px;
    border:1px solid #d1d5db;
    font-size:15px;
}
input:focus,select:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 2px rgba(37,99,235,.2);
}
button{
    background:#2563eb;
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:.25s;
}
button:hover{
    background:#1e40af;
    transform:translateY(-1px);
}
.task{
    background:#f9fafb;
    padding:15px;
    border-radius:14px;
    margin-bottom:12px;
    border-left:6px solid #6366f1;
    transition:.2s;
}
.task:hover{
    transform:scale(1.01);
}
.badge{
    display:inline-block;
    padding:4px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:600;
}
.low{background:#dcfce7;color:#166534}
.medium{background:#fef9c3;color:#854d0e}
.high{background:#fee2e2;color:#7f1d1d}
.status{
    font-size:13px;
    color:#4b5563;
}
hr{
    border:none;
    height:1px;
    background:#e5e7eb;
    margin:25px 0;
}
</style>
</head>

<body>

<div class="box">
<h2><?= htmlspecialchars($project['title']) ?> – Assign Tasks</h2>

<form method="post" id="taskForm">

<label>Task Title</label>
<input name="task_title" placeholder="Enter task title" required>

<label>Assign To</label>
<select name="assigned_to" required>
<option value="">Select Member</option>

<option value="<?= $project['leader_roll_no'] ?>">
Leader – <?= $project['leader_name'] ?>
</option>

<?php
for ($i=1; $i<=3; $i++) {
    if (!empty($project["member{$i}_name"])) {
        echo "<option value='{$project["member{$i}_roll_no"]}'>
        {$project["member{$i}_name"]}
        </option>";
    }
}
?>
</select>

<label>Priority</label>
<select name="priority">
<option>Low</option>
<option selected>Medium</option>
<option>High</option>
</select>

<label>Due Date</label>
<input type="date" name="due_date" required>

<button type="submit">Assign Task</button>


</form>

<hr>

<h3>Assigned Tasks</h3>

<?php while($t=$tasks->fetch_assoc()): 
$prio = strtolower($t['priority']);
?>
<div class="task">
<b><?= htmlspecialchars($t['task_title']) ?></b><br><br>

Assigned To: <b><?= htmlspecialchars($t['assigned_to']) ?></b><br>

Priority: 
<span class="badge <?= $prio ?>">
<?= $t['priority'] ?>
</span><br>

Due: <?= $t['due_date'] ?><br>

<span class="status">Status: <?= $t['status'] ?></span>
</div>
<?php endwhile; ?>

</div>

<script>
// Simple client-side feedback (no logic change)
document.getElementById('taskForm').addEventListener('submit', e => {
    const btn = e.target.querySelector('button');
    btn.innerText = 'Assigning...';
    btn.disabled = true;
});
</script>

</body>
</html>
