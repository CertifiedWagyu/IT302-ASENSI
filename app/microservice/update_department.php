<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"]) || !isset($data["department_name"])) {
    http_response_code(400);
    echo json_encode([
        "error" => "id and department_name are required"
    ]);
    exit;
}

$id = $data["id"];
$department_name = trim($data["department_name"]);

if (empty($department_name)) {
    http_response_code(400);
    echo json_encode([
        "error" => "department_name cannot be empty"
    ]);
    exit;
}

$sql = "UPDATE departments 
        SET department_name = :department_name 
        WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":department_name" => $department_name,
    ":id" => $id
]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode([
        "error" => "Department not found or no changes made"
    ]);
    exit;
}

echo json_encode([
    "message" => "Department updated successfully",
    "id" => $id,
    "department_name" => $department_name
]);

?>