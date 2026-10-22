
<?php
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $new_password = $_POST["new_password"] ?? "";

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($new_password) < 8) {
        $message = "Enter a valid email and a password of at least 8 characters.";
    } else {
        $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows === 1) {
            $hash = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare(
                "UPDATE users SET password_hash = ? WHERE email = ?"
            );
            $update->bind_param("ss", $hash, $email);

            if ($update->execute() && $update->affected_rows === 1) {
                $message = "Password updated successfully. You can now log in.";
            } else {
                $message = "Password was not changed. Please try again.";
            }
            $update->close();
        } else {
            $message = "Email not found.";
        }

        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="card shadow-sm mx-auto p-4" style="max-width: 450px;">
        <h2 class="text-center mb-4">Set New Password</h2>

        <?php if ($message !== ""): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label">Registered Email</label>
            <input class="form-control mb-3" type="email" name="email" required>

            <label class="form-label">New Password (minimum 8 characters)</label>
            <input class="form-control mb-3" type="password" name="new_password" minlength="8" required>

            <button class="btn btn-primary w-100" type="submit">Update Password</button>
        </form>

        <a href="login.php" class="text-center mt-3">Go to Login</a>
    </div>
</div>
</body>
</html>
