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
    "SELECT title FROM projects WHERE project_id=$project_id"
)->fetch_assoc();

/* FETCH KANBAN TASKS */
$res = $conn->query("
    SELECT 
        task_title,
        status,
        DATE(created_at) AS start_date,
        due_date
    FROM kanban
    WHERE project_id = $project_id
    ORDER BY created_at
");

$tasks = [];
while ($row = $res->fetch_assoc()) {

    // Auto progress mapping
    switch ($row['status']) {
        case 'Done':        $progress = 100; break;
        case 'Review':      $progress = 80;  break;
        case 'InProgress':  $progress = 50;  break;
        default:            $progress = 0;
    }

    $start = $row['start_date'];
$end   = $row['due_date'] ?: $row['start_date']; // ✅ fallback

if (strtotime($end) < strtotime($start)) {
    $end = $start;
}

$tasks[] = [
    'name'     => $row['task_title'],
    'start'    => $start,
    'end'      => $end,
    'progress' => $progress
];

}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Gantt Chart</title>


<style>
:root{
    --bg:#f5f7fb;
    --card:#ffffff;
    --primary:#2563eb;
    --success:#16a34a;
    --warning:#f59e0b;
    --muted:#9ca3af;
    --danger:#dc2626;
    --text:#111827;
}

body{
    margin:0;
    padding:32px;
    font-family:"Inter","Segoe UI",system-ui;
    background:var(--bg);
    color:var(--text);
}

/* ===== Header ===== */
.header{
    max-width:1200px;
    margin:0 auto 28px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.header h1{
    margin:0;
    font-size:28px;
    font-weight:700;
}
.subtitle{
    margin-top:4px;
    color:#6b7280;
    font-size:14px;
}

.export-btn{
    background:var(--danger);
    color:#fff;
    border:none;
    padding:10px 20px;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
    transition:.25s;
}
.export-btn:hover{
    transform:translateY(-1px);
    box-shadow:0 10px 22px rgba(220,38,38,.35);
}

/* ===== Legend ===== */
.legend{
    max-width:1200px;
    margin:0 auto 14px;
    display:flex;
    gap:18px;
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

/* ===== Timeline Header ===== */
.time-scale{
    max-width:1200px;
    margin:0 auto 10px;
    display:flex;
    justify-content:space-between;
    font-size:12px;
    color:#6b7280;
    font-weight:500;
}

/* ===== Chart Card ===== */
.chart{
    max-width:1200px;
    margin:auto;
    background:var(--card);
    padding:26px;
    border-radius:18px;
    box-shadow:0 20px 45px rgba(0,0,0,.08);
}

/* ===== Rows ===== */
.row{
    display:grid;
    grid-template-columns:260px 1fr;
    align-items:center;
    margin-bottom:22px;
}
.task-name{
    font-weight:600;
    font-size:14px;
}

/* ===== Timeline ===== */
.timeline{
    position:relative;
    height:24px;
    background:#e5e7eb;
    border-radius:999px;
    overflow:hidden;
}

/* ===== Gantt Bar ===== */
.bar{
    position:absolute;
    height:100%;
    border-radius:999px;
    background:#c7d2fe;
    overflow:hidden;
}

/* ===== Progress ===== */
.progress-bar{
    height:100%;
    display:flex;
    align-items:center;
    justify-content:flex-end;
    padding-right:8px;
    font-size:12px;
    font-weight:600;
    color:#fff;
}

/* Status colors */
.done     { background:var(--success); }
.review   { background:var(--warning); }
.progress { background:var(--primary); }
.todo     { background:var(--muted); }

/* ===== Tooltip ===== */
.bar:hover::after{
    content: attr(data-tip);
    position:absolute;
    top:-42px;
    left:12px;
    background:#111827;
    color:#fff;
    padding:6px 10px;
    border-radius:8px;
    font-size:12px;
    white-space:nowrap;
}

/* ===== Today Marker ===== */
.today{
    position:absolute;
    top:-14px;
    bottom:-14px;
    width:2px;
    background:var(--danger);
}
</style>

</head>

<body>

<div class="header">
    <div>
        <h1><?= htmlspecialchars($project['title']) ?></h1>
        <p class="subtitle">Project Timeline & Progress Overview</p>
    </div>
    <button class="export-btn" onclick="exportPDF()">📄 Export PDF</button>
</div>

<div style="text-align:center; margin-bottom:20px;">
    <button onclick="exportPDF()" style="
        background:#dc2626;
        color:#fff;
        border:none;
        padding:10px 18px;
        border-radius:10px;
        font-weight:600;
        cursor:pointer;
    ">
        📄 Export Gantt as PDF
    </button>
</div>

<div class="legend">
    <span><i style="background:#16a34a"></i> Completed</span>
    <span><i style="background:#2563eb"></i> In Progress</span>
    <span><i style="background:#f59e0b"></i> Review</span>
    <span><i style="background:#9ca3af"></i> Not Started</span>
</div>

<div class="time-scale">
    <span>Project Start</span>
    <span style="float:right">Project End</span>
</div>

<div class="chart" id="chart"></div>


<script>
const tasks = <?= json_encode($tasks) ?>;

// Global date range
const dates = tasks.flatMap(t => [new Date(t.start), new Date(t.end)]);
const minDate = new Date(Math.min(...dates));
const maxDate = new Date(Math.max(...dates));
const totalDays = (maxDate - minDate) / (1000*60*60*24) + 1;

const chart = document.getElementById('chart');

// TODAY MARKER
const today = new Date();
const todayOffset = (today - minDate) / (1000*60*60*24);
if (todayOffset >= 0 && todayOffset <= totalDays) {
    const todayLine = document.createElement('div');
    todayLine.className = 'today';
    todayLine.style.left = (todayOffset / totalDays) * 100 + '%';
    chart.appendChild(todayLine);
}

tasks.forEach(t => {

    const startOffset = (new Date(t.start) - minDate) / (1000*60*60*24);
    const duration = (new Date(t.end) - new Date(t.start)) / (1000*60*60*24) + 1;

    const width = Math.max((duration / totalDays) * 100, 3);

    const left  = (startOffset / totalDays) * 100;

    let cls = 'todo';
    if (t.progress === 100) cls = 'done';
    else if (t.progress >= 80) cls = 'review';
    else if (t.progress > 0) cls = 'progress';

    const row = document.createElement('div');
    row.className = 'row';

    row.innerHTML = `
        <div class="task-name">${t.name}</div>
        <div class="timeline">
            <div class="bar" style="width:${width}%; left:${left}%"
                 data-tip="Start: ${t.start} | End: ${t.end}">
                <div class="progress-bar ${cls}" style="width:${t.progress}%">
                    ${t.progress}%
                </div>
            </div>
        </div>
    `;

    chart.appendChild(row);
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
async function exportPDF() {
    const { jsPDF } = window.jspdf;

    const chart = document.querySelector('.chart');

    const canvas = await html2canvas(chart, {
        scale: 2,        // higher quality
        useCORS: true
    });

    const imgData = canvas.toDataURL('image/png');

    const pdf = new jsPDF('landscape', 'mm', 'a4');

    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (canvas.height * pdfWidth) / canvas.width;

    pdf.addImage(imgData, 'PNG', 0, 10, pdfWidth, pdfHeight);

    pdf.save('Gantt_Chart_Project.pdf');
}
</script>

</body>
</html>
