<?php

header("Content-Type: application/json");

require "db.php";

$sql = "SELECT * FROM departments ORDER BY department_name ASC";

$stmt = $pdo->prepare($sql);

$stmt->execute();

$departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($departments);

?>