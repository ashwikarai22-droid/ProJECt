<?php
include "db.php";

$branch = $_GET['branch'] ?? '';

$res = $conn->query("
    SELECT mentor_id, name 
    FROM mentors 
    WHERE department = '$branch'
");

$options = "<option value=''>-- Select Faculty --</option>";

while ($row = $res->fetch_assoc()) {
    $options .= "<option value='{$row['name']}'>{$row['name']}</option>";
}

echo $options;
