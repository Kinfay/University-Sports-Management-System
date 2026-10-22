
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "danger";

// Get available teams
$teams = [];
$teamResult = $conn->query(
    "SELECT team_id, team_name, sport_type, department
     FROM teams ORDER BY team_name"
);

if ($teamResult) {
    while ($row = $teamResult->fetch_assoc()) {
        $teams[] = $row;
    }
}

$student_id = "";
$full_name = "";
$team_id = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = trim($_POST["student_id"] ?? "");
    $full_name = trim($_POST["full_name"] ?? "");
    $team_id = $_POST["team_id"] ?? "";

    if (
        $student_id === "" ||
        $full_name === "" ||
        !ctype_digit($team_id)
    ) {
        $message = "Please fill in all fields.";
    } else {
        // Check whether the selected team exists
        $check = $conn->prepare(
            "SELECT team_id FROM teams WHERE team_id = ?"
        );
        $selectedTeam = (int) $team_id;
        $check->bind_param("i", $selectedTeam);
        $check->execute();
        $check->store_result();

        if ($check->num_rows !== 1) {
            $message = "Please select a valid team.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO players (student_id, full_name, team_id)
                 VALUES (?, ?, ?)"
            );

            $stmt->bind_param(
                "ssi",
                $student_id,
                $full_name,
                $selectedTeam
            );

            if ($stmt->execute()) {
                $message = "Player registered successfully!";
                $messageType = "success";
                $student_id = "";
                $full_name = "";
                $team_id = "";
            } else {
                if ($stmt->errno === 1062) {
                    $message = "This student ID may already be registered.";
                } else {
                    $message = "Could not register the player. Please check the database structure.";
                }
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
    <title>Add Player - University Sports</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">

                    <h2 class="mb-2">Register a Player</h2>
                    <p class="text-muted mb-4">
                        Add a student to a sports team.
                    </p>

                    <?php if ($message !== ""): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($teams) === 0): ?>
                        <div class="alert alert-warning">
                            No teams found. Please add a team first.
                        </div>

                        <a href="add_team.php" class="btn btn-primary">
                            Add a Team
                        </a>
                    <?php else: ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input
                                type="text"
                                name="student_id"
                                class="form-control"
                                placeholder="Enter student ID"
                                value="<?php echo htmlspecialchars($student_id); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                placeholder="Enter student's full name"
                                value="<?php echo htmlspecialchars($full_name); ?>"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Sports Team</label>
                            <select name="team_id" class="form-select" required>
                                <option value="">Select a team</option>

                                <?php foreach ($teams as $team): ?>
                                    <option
                                        value="<?php echo (int) $team["team_id"]; ?>"
                                        <?php echo (string) $team_id === (string) $team["team_id"] ? "selected" : ""; ?>
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $team["team_name"] . " - " .
                                            $team["sport_type"] . " (" .
                                            $team["department"] . ")"
                                        );
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Register Player
                        </button>
                    </form>

                    <?php endif; ?>

                    <a href="dashboard.php"
                       class="btn btn-outline-secondary w-100 mt-3">
                        Back to Dashboard
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
