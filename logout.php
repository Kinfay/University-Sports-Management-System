
<?php
session_start();

// Clear all session variables
$_SESSION = [];

// Destroy the current session
session_destroy();

// Return to the home page
header("Location: index.php");
exit;
?>
