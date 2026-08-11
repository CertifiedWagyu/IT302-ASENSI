<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["department_name"]) || empty(trim($data["department_name"]))) {
    http_response_code(400);
    echo json_encode([
        "error" => "department_name is required"
    ]);
    exit;
}

$department_name = trim($data["department_name"]);

$sql = "INSERT INTO departments (department_name) VALUES (:department_name)";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":department_name" => $department_name
]);

echo json_encode([
    "message" => "Department created successfully",
    "id" => $pdo->lastInsertId(),
    "department_name" => $department_name
]);
?>