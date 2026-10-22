
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "danger";

$team_name = "";
$sport_type = "Football";
$department = "";
$representative_id = "";

$sports = [
    "Football",
    "Volleyball",
    "Basketball",
    "Athletics",
    "Tennis",
    "Other"
];

// Get users for the representative dropdown
$users = [];
$userResult = $conn->query(
    "SELECT user_id, full_name FROM users ORDER BY full_name"
);

if ($userResult) {
    while ($row = $userResult->fetch_assoc()) {
        $users[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team_name = trim($_POST["team_name"] ?? "");
    $sport_type = $_POST["sport_type"] ?? "";
    $department = trim($_POST["department"] ?? "");
    $representative_id = $_POST["representative_id"] ?? "";

    if (
        $team_name === "" ||
        $department === "" ||
        !in_array($sport_type, $sports, true) ||
        !ctype_digit($representative_id)
    ) {
        $message = "Please fill in all fields correctly.";
    } else {
        // Confirm that the selected representative exists
        $check = $conn->prepare(
            "SELECT user_id FROM users WHERE user_id = ?"
        );
        $check->bind_param("i", $representative_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows !== 1) {
            $message = "The selected representative was not found.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO teams
                 (team_name, sport_type, department, representative_id)
                 VALUES (?, ?, ?, ?)"
            );

            $repId = (int) $representative_id;
            $stmt->bind_param(
                "sssi",
                $team_name,
                $sport_type,
                $department,
                $repId
            );

            if ($stmt->execute()) {
                $message = "Team added successfully!";
                $messageType = "success";

                $team_name = "";
                $sport_type = "Football";
                $department = "";
                $representative_id = "";
            } else {
                $message = "Could not add the team. Please check the database settings.";
            }

            $stmt->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Team - University Sports</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="mb-2">Add New Team</h2>
                    <p class="text-muted mb-4">
                        Register a team for any department and sport.
                    </p>

                    <?php if ($message !== ""): ?>
                        <div class="alert alert-<?php
                            echo htmlspecialchars($messageType);
                        ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">

                        <div class="mb-3">
                            <label class="form-label">Team Name</label>
                            <input
                                type="text"
                                name="team_name"
                                class="form-control"
                                placeholder="e.g. Computer Science Football Team"
                                value="<?php echo htmlspecialchars($team_name); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sport Type</label>
                            <select name="sport_type" class="form-select" required>
                                <?php foreach ($sports as $sport): ?>
                                    <option
                                        value="<?php echo htmlspecialchars($sport); ?>"
                                        <?php echo $sport_type === $sport ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars($sport); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input
                                type="text"
                                name="department"
                                class="form-control"
                                placeholder="e.g. Computer Science"
                                value="<?php echo htmlspecialchars($department); ?>"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Team Representative</label>
                            <select
                                name="representative_id"
                                class="form-select"
                                required
                            >
                                <option value="">Select representative</option>

                                <?php foreach ($users as $user): ?>
                                    <option
                                        value="<?php echo (int) $user["user_id"]; ?>"
                                        <?php echo (string) $representative_id === (string) $user["user_id"] ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars($user["full_name"]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <?php if (count($users) === 0): ?>
                                <small class="text-danger">
                                    No users found. Register a user before adding a team.
                                </small>
                            <?php endif; ?>
                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                            <?php echo count($users) === 0 ? "disabled" : ""; ?>
                        >
                            Add Team
                        </button>

                        <a
                            href="teams.php"
                            class="btn btn-outline-secondary w-100 mt-2"
                        >
                            View Teams
                        </a>

                        <a
                            href="dashboard.php"
                            class="btn btn-link w-100 mt-2"
                        >
                            Back to Dashboard
                        </a>

                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
