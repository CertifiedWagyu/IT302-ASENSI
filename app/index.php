<?php

$host = "mysql";
$dbname = "student_management";
$username = "root";
$password = "it302_asensi";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| GET DEPARTMENTS FROM MICROSERVICE API
|--------------------------------------------------------------------------
*/

$apiUrl = "http://microservice-nginx/microservice/departments.php";

$context = stream_context_create([
    "http" => [
        "timeout" => 5
    ]
]);

$apiResponse = @file_get_contents($apiUrl, false, $context);

if ($apiResponse === false) {
    $departments = [];
    $apiError = "Could not connect to Microservice API.";
} else {
    $departments = json_decode($apiResponse, true);

    if (!is_array($departments)) {
        $departments = [];
        $apiError = "Invalid response from Microservice API.";
    } else {
        $apiError = "";
    }
}


/*
|--------------------------------------------------------------------------
| CREATE / UPDATE / DELETE
|--------------------------------------------------------------------------
*/

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    if ($action === "create") {

        $studentNumber = trim($_POST["student_number"] ?? "");
        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $age = intval($_POST["age"] ?? 0);
        $departmentId = intval($_POST["department_id"] ?? 0);

        if (
            $studentNumber !== "" &&
            $firstName !== "" &&
            $lastName !== "" &&
            $age > 0 &&
            $departmentId > 0
        ) {

            $stmt = $pdo->prepare("
                INSERT INTO students
                (
                    student_number,
                    first_name,
                    last_name,
                    age,
                    department_id
                )
                VALUES (?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $studentNumber,
                $firstName,
                $lastName,
                $age,
                $departmentId
            ]);

            $message = "Student created successfully!";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    elseif ($action === "update") {

        $id = intval($_POST["id"] ?? 0);
        $studentNumber = trim($_POST["student_number"] ?? "");
        $firstName = trim($_POST["first_name"] ?? "");
        $lastName = trim($_POST["last_name"] ?? "");
        $age = intval($_POST["age"] ?? 0);
        $departmentId = intval($_POST["department_id"] ?? 0);

        if (
            $id > 0 &&
            $studentNumber !== "" &&
            $firstName !== "" &&
            $lastName !== "" &&
            $age > 0 &&
            $departmentId > 0
        ) {

            $stmt = $pdo->prepare("
                UPDATE students
                SET
                    student_number = ?,
                    first_name = ?,
                    last_name = ?,
                    age = ?,
                    department_id = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $studentNumber,
                $firstName,
                $lastName,
                $age,
                $departmentId,
                $id
            ]);

            $message = "Student updated successfully!";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    elseif ($action === "delete") {

        $id = intval($_POST["id"] ?? 0);

        if ($id > 0) {

            $stmt = $pdo->prepare("
                DELETE FROM students
                WHERE id = ?
            ");

            $stmt->execute([$id]);

            $message = "Student deleted successfully!";
        }
    }
}


/*
|--------------------------------------------------------------------------
| READ STUDENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        students.id,
        students.student_number,
        students.first_name,
        students.last_name,
        students.age,
        students.department_id,
        students.created_at,
        departments.department_name
    FROM students
    LEFT JOIN departments
        ON students.department_id = departments.id
    ORDER BY students.id DESC
");

$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Management System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            margin-bottom: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        h2 {
            margin-top: 0;
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            margin-top: 15px;
            padding: 10px 18px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .create-btn {
            background: #198754;
            color: white;
        }

        .update-btn {
            background: #0d6efd;
            color: white;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .message {
            background: #d1e7dd;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error {
            background: #f8d7da;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f1f1f1;
        }

        .empty {
            text-align: center;
            padding: 20px;
        }

        .update-form input,
        .update-form select {
            margin-bottom: 5px;
        }

    </style>

</head>

<body>

<div class="container">

    <h1>Student Management System</h1>


    <?php if ($message !== ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($apiError !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($apiError) ?>
        </div>

    <?php endif; ?>


    <!-- CREATE STUDENT -->

    <div class="card">

        <h2>Create Student</h2>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="create"
            >

            <label>Student Number</label>

            <input
                type="text"
                name="student_number"
                placeholder="2026-0001"
                required
            >


            <label>First Name</label>

            <input
                type="text"
                name="first_name"
                required
            >


            <label>Last Name</label>

            <input
                type="text"
                name="last_name"
                required
            >


            <label>Age</label>

            <input
                type="number"
                name="age"
                min="1"
                required
            >


            <label>Department</label>

            <select
                name="department_id"
                required
            >

                <option value="">
                    Select Department
                </option>

                <?php foreach ($departments as $department): ?>

                    <option value="<?= $department["id"] ?>">

                        <?= htmlspecialchars(
                            $department["department_name"]
                        ) ?>

                    </option>

                <?php endforeach; ?>

            </select>


            <button
                class="create-btn"
                type="submit"
            >
                Create Student
            </button>

        </form>

    </div>


    <!-- STUDENT LIST -->

    <div class="card">

        <h2>Students</h2>

        <?php if (count($students) === 0): ?>

            <div class="empty">
                No students found.
            </div>

        <?php else: ?>

            <table>

                <tr>

                    <th>ID</th>

                    <th>Student Number</th>

                    <th>Name</th>

                    <th>Age</th>

                    <th>Department</th>

                    <th>Actions</th>

                </tr>


                <?php foreach ($students as $student): ?>

                    <tr>

                        <td>
                            <?= $student["id"] ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student["student_number"]
                            ) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student["first_name"]
                            ) ?>

                            <?= htmlspecialchars(
                                $student["last_name"]
                            ) ?>
                        </td>


                        <td>
                            <?= $student["age"] ?>
                        </td>


                        <td>
                            <?= htmlspecialchars(
                                $student["department_name"]
                                ?? "Unknown"
                            ) ?>
                        </td>


                        <td>

                            <!-- UPDATE -->

                            <form
                                method="POST"
                                class="update-form"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="update"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $student["id"] ?>"
                                >


                                <input
                                    type="text"
                                    name="student_number"
                                    value="<?= htmlspecialchars(
                                        $student["student_number"]
                                    ) ?>"
                                    required
                                >


                                <input
                                    type="text"
                                    name="first_name"
                                    value="<?= htmlspecialchars(
                                        $student["first_name"]
                                    ) ?>"
                                    required
                                >


                                <input
                                    type="text"
                                    name="last_name"
                                    value="<?= htmlspecialchars(
                                        $student["last_name"]
                                    ) ?>"
                                    required
                                >


                                <input
                                    type="number"
                                    name="age"
                                    value="<?= $student["age"] ?>"
                                    min="1"
                                    required
                                >


                                <!-- DEPARTMENT DROPDOWN
                                     FROM MICROSERVICE -->

                                <select
                                    name="department_id"
                                    required
                                >

                                    <?php foreach (
                                        $departments
                                        as $department
                                    ): ?>

                                        <option
                                            value="<?= $department["id"] ?>"
                                            <?= $department["id"]
                                                == $student["department_id"]
                                                ? "selected"
                                                : "" ?>
                                        >

                                            <?= htmlspecialchars(
                                                $department["department_name"]
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>


                                <button
                                    class="update-btn"
                                    type="submit"
                                >
                                    Update
                                </button>

                            </form>


                            <!-- DELETE -->

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="action"
                                    value="delete"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $student["id"] ?>"
                                >

                                <button
                                    class="delete-btn"
                                    type="submit"
                                    onclick="return confirm('Delete this student?');"
                                >
                                    Delete
                                </button>

                            </form>

                        </td>

                    </tr>

                <?php endforeach; ?>

            </table>

        <?php endif; ?>

    </div>

</div>

</body>

</html>