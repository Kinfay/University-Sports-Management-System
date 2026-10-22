
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "danger";

$competition_id = "";
$team1_id = "";
$team2_id = "";
$match_date = "";
$venue = "";
$status = "Scheduled";

// Load competitions
$competitions = [];
$result = $conn->query(
    "SELECT competition_id, name
     FROM competitions
     ORDER BY start_date DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $competitions[] = $row;
    }
}

// Load teams
$teams = [];
$result = $conn->query(
    "SELECT team_id, team_name, sport_type, department
     FROM teams
     ORDER BY team_name"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $teams[] = $row;
    }
}

$statuses = ["Scheduled", "Ongoing", "Completed", "Cancelled"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $competition_id = $_POST["competition_id"] ?? "";
    $team1_id = $_POST["team1_id"] ?? "";
    $team2_id = $_POST["team2_id"] ?? "";
    $match_date = $_POST["match_date"] ?? "";
    $venue = trim($_POST["venue"] ?? "");
    $status = $_POST["status"] ?? "Scheduled";

    if (
        !ctype_digit($competition_id) ||
        !ctype_digit($team1_id) ||
        !ctype_digit($team2_id) ||
        (int)$team1_id === (int)$team2_id ||
        $match_date === "" ||
        $venue === "" ||
        !in_array($status, $statuses, true)
    ) {
        $message = "Please complete all fields correctly and choose two different teams.";
    } else {
        // Verify competition and both teams exist
        $check = $conn->prepare(
            "SELECT
                (SELECT COUNT(*) FROM competitions WHERE competition_id = ?),
                (SELECT COUNT(*) FROM teams WHERE team_id = ?),
                (SELECT COUNT(*) FROM teams WHERE team_id = ?)"
        );

        $cid = (int)$competition_id;
        $t1 = (int)$team1_id;
        $t2 = (int)$team2_id;

        $check->bind_param("iii", $cid, $t1, $t2);
        $check->execute();
        $check->bind_result($competitionExists, $team1Exists, $team2Exists);
        $check->fetch();
        $check->close();

        if (!$competitionExists || !$team1Exists || !$team2Exists) {
            $message = "Please select an existing competition and two existing teams.";
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO matches
                 (competition_id, team1_id, team2_id, match_date, venue, status)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "iiisss",
                $cid,
                $t1,
                $t2,
                $match_date,
                $venue,
                $status
            );

            if ($stmt->execute()) {
                header("Location: matches.php");
                exit;
            } else {
                $message = "Could not schedule the match. Please check the database structure.";
            }

            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Match - University Sports</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <h2 class="mb-2">Schedule a Match</h2>
                    <p class="text-muted mb-4">
                        Create a match between two teams.
                    </p>

                    <?php if ($message !== ""): ?>
                        <div class="alert alert-<?php echo $messageType; ?>">
                            <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (count($competitions) === 0): ?>
                        <div class="alert alert-warning">
                            No competitions found. Please create a competition first.
                        </div>
                        <a href="add_competition.php" class="btn btn-primary">
                            Add Competition
                        </a>

                    <?php elseif (count($teams) < 2): ?>
                        <div class="alert alert-warning">
                            At least two teams are required to schedule a match.
                        </div>
                        <a href="add_team.php" class="btn btn-primary">
                            Add Team
                        </a>

                    <?php else: ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Competition</label>
                            <select name="competition_id" class="form-select" required>
                                <option value="">Select competition</option>
                                <?php foreach ($competitions as $competition): ?>
                                    <option
                                        value="<?php echo (int)$competition["competition_id"]; ?>"
                                        <?php echo (string)$competition_id === (string)$competition["competition_id"] ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars($competition["name"]); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">First Team</label>
                            <select name="team1_id" class="form-select" required>
                                <option value="">Select first team</option>
                                <?php foreach ($teams as $team): ?>
                                    <option
                                        value="<?php echo (int)$team["team_id"]; ?>"
                                        <?php echo (string)$team1_id === (string)$team["team_id"] ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars(
                                            $team["team_name"] . " - " .
                                            $team["sport_type"] . " (" .
                                            $team["department"] . ")"
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Second Team</label>
                            <select name="team2_id" class="form-select" required>
                                <option value="">Select second team</option>
                                <?php foreach ($teams as $team): ?>
                                    <option
                                        value="<?php echo (int)$team["team_id"]; ?>"
                                        <?php echo (string)$team2_id === (string)$team["team_id"] ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars(
                                            $team["team_name"] . " - " .
                                            $team["sport_type"] . " (" .
                                            $team["department"] . ")"
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Choose a different team from the first team.
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Match Date and Time</label>
                            <input
                                type="datetime-local"
                                name="match_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($match_date); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Venue</label>
                            <input
                                type="text"
                                name="venue"
                                class="form-control"
                                placeholder="e.g. Main University Field"
                                value="<?php echo htmlspecialchars($venue); ?>"
                                required
                            >
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <?php foreach ($statuses as $option): ?>
                                    <option
                                        value="<?php echo htmlspecialchars($option); ?>"
                                        <?php echo $status === $option ? "selected" : ""; ?>
                                    >
                                        <?php echo htmlspecialchars($option); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            Schedule Match
                        </button>
                    </form>

                    <?php endif; ?>

                    <a href="matches.php" class="btn btn-outline-secondary w-100 mt-3">
                        View Matches
                    </a>

                    <a href="dashboard.php" class="btn btn-link w-100 mt-2">
                        Back to Dashboard
                    </a>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
