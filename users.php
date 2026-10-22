
<?php
require_once "auth.php";
requireRole(["admin"]);
require_once "db.php";

$message = "";
$error = "";

$allowedRoles = ["admin", "coordinator", "team_manager"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = filter_input(INPUT_POST, "user_id", FILTER_VALIDATE_INT);
    $newRole = $_POST["role"] ?? "";

    if (!$userId || !in_array($newRole, $allowedRoles, true)) {
        $error = "Invalid user or role.";
    } elseif ((int)$userId === (int)$_SESSION["user_id"]
        && $newRole !== "admin") {
        $error = "You cannot remove your own admin role.";
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET role = ? WHERE user_id = ?"
        );

        if ($stmt) {
            $stmt->bind_param("si", $newRole, $userId);

            if ($stmt->execute() && $stmt->affected_rows >= 0) {
                $message = "User role updated successfully.";
            } else {
                $error = "Could not update user role.";
            }

            $stmt->close();
        } else {
            $error = "Database error. Please try again.";
        }
    }
}

$sql = "SELECT user_id, full_name, email, role, created_at
        FROM users ORDER BY user_id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f6fa;
            margin: 0;
            padding: 25px;
        }

        .container {
            max-width: 1200px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000012;
            overflow-x: auto;
        }

        h2 { color: #17365d; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #17365d;
            color: white;
        }

        tr:nth-child(even) { background: #f4f7fb; }

        .back, .save {
            display: inline-block;
            padding: 9px 13px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }

        .back {
            background: #17365d;
            color: white;
        }

        .save {
            background: #198754;
            color: white;
        }

        .message {
            padding: 10px;
            background: #d1e7dd;
            color: #0f5132;
            margin-top: 15px;
        }

        .error {
            padding: 10px;
            background: #f8d7da;
            color: #842029;
            margin-top: 15px;
        }

        select { padding: 7px; }
    </style>
</head>

<body>
<div class="container">
    <h2>User Management</h2>
    <p>View users and update their roles.</p>

    <a class="back" href="dashboard.php">Back to Dashboard</a>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message) ?>
        </p>
    <?php endif; ?>

    <?php if ($error !== ""): ?>
        <p class="error">
            <?= htmlspecialchars($error) ?>
        </p>
    <?php endif; ?>

    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Current Role</th>
            <th>Change Role</th>
            <th>Created At</th>
        </tr>
        </thead>

        <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$user["user_id"]) ?></td>
                    <td><?= htmlspecialchars($user["full_name"]) ?></td>
                    <td><?= htmlspecialchars($user["email"]) ?></td>
                    <td><?= htmlspecialchars($user["role"]) ?></td>

                    <td>
                        <form method="POST" action="users.php">
                            <input
                                type="hidden"
                                name="user_id"
                                value="<?= (int)$user["user_id"] ?>"
                            >

                            <select name="role" required>
                                <?php foreach ($allowedRoles as $role): ?>
                                    <option
                                        value="<?= htmlspecialchars($role) ?>"
                                        <?= $user["role"] === $role
                                            ? "selected" : "" ?>
                                    >
                                        <?= htmlspecialchars($role) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button class="save" type="submit">
                                Save
                            </button>
                        </form>
                    </td>

                    <td>
                        <?= htmlspecialchars($user["created_at"] ?? "") ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6">No users found.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
