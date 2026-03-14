<?php
session_start();
include "db.php";

if (!isset($_GET['project_id'])) {
    die("Project not selected");
}

$project_id = (int)$_GET['project_id'];

/* Fetch project */
$project = $conn->query(
    "SELECT title FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* Fetch kanban tasks */
$res = $conn->query("
    SELECT task_title, status, created_at, due_date
    FROM kanban
    WHERE project_id = $project_id
    ORDER BY created_at
");

$tasks = [];
while ($row = $res->fetch_assoc()) {

    $start = new DateTime($row['created_at']);
    $end   = new DateTime($row['due_date'] ?? $row['created_at']);

    $tasks[] = [
        'name'   => $row['task_title'],
        'start'  => (int)$start->format('n'), // month number
        'end'    => (int)$end->format('n'),
        'status' => $row['status']
    ];
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Gantt Chart</title>

<style>
body{
    font-family:"Segoe UI",system-ui;
    background:#f8fafc;
    padding:32px;
}

/* Header */
h1{ margin-bottom:4px; }
.subtitle{ color:#6b7280; margin-bottom:20px; }

/* Legend */
.legend{
    display:flex;
    gap:18px;
    margin-bottom:14px;
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

/* Gantt Card */
.gantt{
    background:#fff;
    padding:24px;
    border-radius:16px;
    box-shadow:0 18px 40px rgba(0,0,0,.08);
}

/* Header Row */
.header{
    display:grid;
    grid-template-columns:220px repeat(12,1fr);
    font-size:12px;
    font-weight:600;
    margin-bottom:12px;
    color:#374151;
}

/* Task Row */
.row{
    display:grid;
    grid-template-columns:220px repeat(12,1fr);
    align-items:center;
    margin-bottom:14px;
}
.task{
    font-weight:600;
    font-size:14px;
}

/* Cells */
.cell{
    height:22px;
}

/* Bar */
.bar{
    height:100%;
    border-radius:999px;
    font-size:11px;
    font-weight:600;
    display:flex;
    align-items:center;
    justify-content:center;
    color:#fff;
}

/* Status colors */
.todo{ background:#9ca3af; }
.progress{ background:#2563eb; }
.review{ background:#f59e0b; }
.done{ background:#16a34a; }
</style>
</head>

<body>

<h1><?= htmlspecialchars($project['title']) ?></h1>
<p class="subtitle">Project Timeline (Kanban → Gantt)</p>

<div class="legend">
    <span><i style="background:#16a34a"></i> Done</span>
    <span><i style="background:#2563eb"></i> In Progress</span>
    <span><i style="background:#f59e0b"></i> Review</span>
    <span><i style="background:#9ca3af"></i> Todo</span>
</div>

<div class="gantt">

<!-- Month Header -->
<div class="header">
    <div>Task List</div>
    <?php
    $months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    foreach ($months as $m) echo "<div style='text-align:center'>$m</div>";
    ?>
</div>

<!-- Tasks -->
<?php foreach ($tasks as $t): ?>
<div class="row">
    <div class="task"><?= htmlspecialchars($t['name']) ?></div>

    <?php for ($m=1; $m<=12; $m++): ?>
        <div class="cell">
            <?php if ($m >= $t['start'] && $m <= $t['end']): ?>
                <div class="bar
                    <?= $t['status']=='Done' ? 'done' :
                       ($t['status']=='Review' ? 'review' :
                       ($t['status']=='InProgress' ? 'progress' : 'todo')) ?>">
                    <?= $t['status'] ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>
<?php endforeach; ?>

</div>

</body>
</html>
