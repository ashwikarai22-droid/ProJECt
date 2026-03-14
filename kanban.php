<?php
session_start();
include "db.php";

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}


if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* FETCH PROJECT */
$project = $conn->query(
    "SELECT title, project_file FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* UPDATE TASK STATUS (Drag & Drop) */
if (isset($_POST['task_id'], $_POST['status'])) {
    $task_id = (int)$_POST['task_id'];
    $status = $_POST['status'];

    $stmt = $conn->prepare(
        "UPDATE kanban SET status=? WHERE task_id=?"
    );
    $stmt->bind_param("si", $status, $task_id);
    $stmt->execute();
    exit();
}

/* FETCH TASKS */
$tasks = [
    'Todo' => [],
    'InProgress' => [],
    'Review' => [],
    'Done' => []
];

$res = $conn->query("
    SELECT 
        k.*, 
        u.name AS assignee_name
    FROM kanban k
    LEFT JOIN users u 
        ON u.roll_no = k.assigned_to
    WHERE k.project_id = $project_id
");

while ($row = $res->fetch_assoc()) {
    $tasks[$row['status']][] = $row;
}


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kanban Board</title>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Kanban Board</title>

<style>
:root{
    --todo:#fde68a;
    --progress:#bfdbfe;
    --review:#fecaca;
    --done:#bbf7d0;
    --primary:#2563eb;
}

body{
    font-family:Segoe UI,system-ui;
     background-image: url("kanabn_background.jpg");
    padding:20px;
}

h2{
    text-align:center;
    margin-bottom:12px;
    color:#1e3a8a;
}

.file-box{
    text-align:center;
    margin-bottom:25px;
}

.file-box button{
    background:var(--primary);
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
}

.board{
    display:flex;
    gap:16px;
}

.column{
    flex:1;
    background:#fff;
    border-radius:18px;
    padding:14px;
    box-shadow:0 18px 40px rgba(0,0,0,.08);
    transition:.25s;
}

.column.drag-over{
    box-shadow:0 0 0 3px rgba(37,99,235,.35);
}

.column h3{
    position:sticky;
    top:0;
    background:#fff;
    padding:8px 0 10px;
    text-align:center;
    font-size:17px;
    color:#111827;
    border-bottom:1px solid #e5e7eb;
    margin-bottom:12px;
}

.count{
    font-size:12px;
    background:#e5e7eb;
    padding:2px 8px;
    border-radius:999px;
    margin-left:6px;
}

.task{
    padding:12px;
    border-radius:14px;
    margin-bottom:12px;
    cursor:grab;
    transition:.2s;
    border-left:6px solid transparent;
}

.task:hover{ transform:scale(1.02); }
.task:active{ cursor:grabbing; }

.todo{background:var(--todo);border-color:#ca8a04}
.progress{background:var(--progress);border-color:#2563eb}
.review{background:var(--review);border-color:#dc2626}
.done{background:var(--done);border-color:#16a34a}

.task b{
    display:block;
    margin-bottom:6px;
    color:#111827;
}

.task small{
    display:block;
    font-size:13px;
    color:#374151;
}

@media(max-width:900px){
    .board{flex-direction:column}
}
</style>

<script>
function allowDrop(ev){
    ev.preventDefault();
    ev.currentTarget.classList.add('drag-over');
}

function drag(ev){
    ev.dataTransfer.setData("task_id", ev.target.dataset.id);
}

function drop(ev,status){
    ev.preventDefault();
    ev.currentTarget.classList.remove('drag-over');

    const id = ev.dataTransfer.getData("task_id");

    fetch("kanban.php?project_id=<?= $project_id ?>",{
        method:"POST",
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:`task_id=${id}&status=${status}`
    }).then(()=>location.reload());
}

document.querySelectorAll('.column').forEach(col=>{
    col.addEventListener('dragleave',()=>{
        col.classList.remove('drag-over');
    });
});
</script>

</head>
<body>

<h2>Kanban Board – <?= htmlspecialchars($project['title']) ?></h2>

<div class="file-box">
<?php if(!empty($project['project_file'])): ?>
    <a href="<?= htmlspecialchars($project['project_file']) ?>" target="_blank">
        <button>View Submitted Project File</button>
    </a>
<?php else: ?>
    <em>No project file uploaded</em>
<?php endif; ?>
</div>

<div class="board">

<!-- TODO -->
<div class="column" ondragover="allowDrop(event)" ondrop="drop(event,'Todo')">
<h3>To Do <span class="count"><?= count($tasks['Todo']) ?></span></h3>
<?php foreach($tasks['Todo'] as $t): ?>
<div class="task todo" draggable="true" ondragstart="drag(event)" data-id="<?= $t['task_id'] ?>">
<b><?= htmlspecialchars($t['task_title']) ?></b>
<small>Assigned: <?= htmlspecialchars($t['assignee_name'] ?? $t['assigned_to']) ?></small>
</div>
<?php endforeach; ?>
</div>

<!-- IN PROGRESS -->
<div class="column" ondragover="allowDrop(event)" ondrop="drop(event,'InProgress')">
<h3>In Progress <span class="count"><?= count($tasks['InProgress']) ?></span></h3>
<?php foreach($tasks['InProgress'] as $t): ?>
<div class="task progress" draggable="true" ondragstart="drag(event)" data-id="<?= $t['task_id'] ?>">
<b><?= htmlspecialchars($t['task_title']) ?></b>
<small>Assigned: <?= htmlspecialchars($t['assignee_name'] ?? $t['assigned_to']) ?></small>
</div>
<?php endforeach; ?>
</div>

<!-- REVIEW -->
<div class="column" ondragover="allowDrop(event)" ondrop="drop(event,'Review')">
<h3>Review <span class="count"><?= count($tasks['Review']) ?></span></h3>
<?php foreach($tasks['Review'] as $t): ?>
<div class="task review" draggable="true" ondragstart="drag(event)" data-id="<?= $t['task_id'] ?>">
<b><?= htmlspecialchars($t['task_title']) ?></b>
<small>Assigned: <?= htmlspecialchars($t['assignee_name'] ?? $t['assigned_to']) ?></small>
<small>Waiting for faculty review</small>
</div>
<?php endforeach; ?>
</div>

<!-- DONE -->
<div class="column" ondragover="allowDrop(event)" ondrop="drop(event,'Done')">
<h3>Done <span class="count"><?= count($tasks['Done']) ?></span></h3>
<?php foreach($tasks['Done'] as $t): ?>
<div class="task done" draggable="true" ondragstart="drag(event)" data-id="<?= $t['task_id'] ?>">
<b><?= htmlspecialchars($t['task_title']) ?></b>
<small>Assigned: <?= htmlspecialchars($t['assignee_name'] ?? $t['assigned_to']) ?></small>
</div>
<?php endforeach; ?>
</div>

</div>

</body>
</html>