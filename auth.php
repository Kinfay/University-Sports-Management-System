
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin(): void
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: login.php");
        exit;
    }
}

function requireRole(array $allowedRoles): void
{
    requireLogin();

    $userRole = $_SESSION["role"] ?? "";

    if (!in_array($userRole, $allowedRoles, true)) {
        http_response_code(403);
        exit(
            "Access Denied: You do not have permission to access this page."
        );
    }
}
