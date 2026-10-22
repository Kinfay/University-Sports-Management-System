
<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $conn->prepare(
        "SELECT user_id, full_name, password_hash, role
         FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $full_name, $password_hash, $role);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $user_id;
            $_SESSION["full_name"] = $full_name;
            $_SESSION["role"] = $role;

            header("Location: dashboard.php");
            exit;
        }
    }

    $message = "Invalid email or password.";
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - University Sports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto p-4" style="max-width: 450px;">
        <h2 class="text-center mb-4">Login</h2>

        <?php if ($message !== ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label">Email</label>
            <input class="form-control mb-3" type="email" name="email" required>

            <label class="form-label">Password</label>
            <input class="form-control mb-3" type="password" name="password" required>

            <button class="btn btn-primary w-100" type="submit">Login</button>
        </form>

        <p class="text-center mt-3">
            Don't have an account? <a href="register.php">Register</a>
        </p>
        <a href="index.php" class="text-center">Back to Home</a>
    </div>
</div>
</body>
</html>
