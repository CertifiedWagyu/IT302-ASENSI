<?php

header("Content-Type: application/json");

require "db.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["id"])) {
    http_response_code(400);
    echo json_encode([
        "error" => "id is required"
    ]);
    exit;
}

$id = $data["id"];

$sql = "DELETE FROM departments WHERE id = :id";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    ":id" => $id
]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode([
        "error" => "Department not found"
    ]);
    exit;
}

echo json_encode([
    "message" => "Department deleted successfully",
    "id" => $id
]);

?>