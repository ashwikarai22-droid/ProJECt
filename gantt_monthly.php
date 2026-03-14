<?php
session_start();
include "db.php";

if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* FETCH PROJECT */
$project = $conn->query(
    "SELECT title FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* FETCH TASKS */
$res = $conn->query("
    SELECT task_title, status, created_at, due_date
    FROM project_tasks
    WHERE project_id = $project_id
    ORDER BY created_at
");

$tasks = [];
while ($row = $res->fetch_assoc()) {
    $start = new DateTime($row['created_at']);
    $end   = new DateTime($row['due_date'] ?? $row['created_at']);

    $tasks[] = [
        'name'  => $row['task_title'],
        'start' => (int)$start->format('n'), // month number
        'end'   => (int)$end->format('n'),
        'status'=> $row['status']
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Monthly Gantt Chart</title>

<style>
body{
    font-family: "Segoe UI", system-ui;
    background:#f9fafb;
    padding:30px;
}
h1{
    margin-bottom:5px;
}
.subtitle{
    color:#6b7280;
    margin-bottom:20px;
}

/* ===== Gantt ===== */
.gantt{
    background:#fff;
    padding:24px;
    border-radius:14px;
    box-shadow:0 15px 35px rgba(0,0,0,.08);
}

/* Header */
.header{
    display:grid;
    grid-template-columns:220px repeat(12, 1fr);
    font-size:12px;
    font-weight:600;
    color:#374151;
    margin-bottom:12px;
}
.header div{
    text-align:center;
}

/* Rows */
.row{
    display:grid;
    grid-template-columns:220px repeat(12, 1fr);
    align-items:center;
    margin-bottom:14px;
}
.task{
    font-weight:600;
    font-size:14px;
}

/* Timeline */
.cell{
    height:20px;
    position:relative;
}
.bar{
    height:100%;
    border-radius:10px;
    color:#fff;
    font-size:11px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:600;
}

/* Status colors */
.done{ background:#16a34a; }
.progress{ background:#2563eb; }
.pending{ background:#9ca3af; }

/* Legend */
.legend{
    display:flex;
    gap:16px;
    margin-bottom:16px;
    font-size:13px;
}
.legend span{
    display:flex;
    align-items:center;
    gap:6px;
}
.legend i{
    width:14px;
    height:14px;
    border-radius:4px;
}
</style>
</head>

<body>

<h1><?= htmlspecialchars($project['title']) ?></h1>
<p class="subtitle">Project Timeline (Monthly Gantt)</p>

<div class="legend">
    <span><i style="background:#16a34a"></i> Completed</span>
    <span><i style="background:#2563eb"></i> In Progress</span>
    <span><i style="background:#9ca3af"></i> Pending</span>
</div>

<div class="gantt">

<!-- MONTH HEADER -->
<div class="header">
    <div>Task List</div>
    <?php
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    foreach ($months as $m) echo "<div>$m</div>";
    ?>
</div>

<!-- TASK ROWS -->
<?php foreach ($tasks as $t): ?>
<div class="row">
    <div class="task"><?= htmlspecialchars($t['name']) ?></div>

    <?php for ($m=1; $m<=12; $m++): ?>
        <div class="cell">
            <?php if ($m >= $t['start'] && $m <= $t['end']): ?>
                <div class="bar 
                    <?= $t['status']=='Completed'?'done':($t['status']=='In Progress'?'progress':'pending') ?>">
                    <?= $t['status']=='Completed'?'Done':'On Progress' ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>
<?php endforeach; ?>

</div>

</body>
</html>
