
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$message = "";
$messageType = "danger";

// Load matches
$matches = [];
$sql = "SELECT
            m.match_id,
            m.match_date,
            m.status,
            t1.team_name AS team1_name,
            t2.team_name AS team2_name,
            c.name AS competition_name
        FROM matches m
        LEFT JOIN teams t1 ON m.team1_id = t1.team_id
        LEFT JOIN teams t2 ON m.team2_id = t2.team_id
        LEFT JOIN competitions c ON m.competition_id = c.competition_id
        ORDER BY m.match_date DESC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $matches[] = $row;
    }
}

// Save a result
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $match_id = $_POST["match_id"] ?? "";
    $team1_score = $_POST["team1_score"] ?? "";
    $team2_score = $_POST["team2_score"] ?? "";
    $remarks = trim($_POST["remarks"] ?? "");

    if (
        !ctype_digit($match_id) ||
        $team1_score === "" ||
        $team2_score === "" ||
        !ctype_digit($team1_score) ||
        !ctype_digit($team2_score) ||
        (int)$team1_score < 0 ||
        (int)$team2_score < 0
    ) {
        $message = "Please select a match and enter valid non-negative scores.";
    } else {
        $mid = (int)$match_id;
        $score1 = (int)$team1_score;
        $score2 = (int)$team2_score;
        $user_id = (int)$_SESSION["user_id"];

        // Verify that the match exists
        $check = $conn->prepare(
            "SELECT match_id FROM matches WHERE match_id = ?"
        );
        $check->bind_param("i", $mid);
        $check->execute();
        $check->store_result();

        if ($check->num_rows !== 1) {
            $message = "Selected match was not found.";
        } else {
            // Update an existing result or insert a new one
            $existing = $conn->prepare(
                "SELECT result_id FROM match_results WHERE match_id = ?"
            );
            $existing->bind_param("i", $mid);
            $existing->execute();
            $existing->store_result();

            if ($existing->num_rows > 0) {
                $existing->close();

                $stmt = $conn->prepare(
                    "UPDATE match_results
                     SET team1_score = ?, team2_score = ?,
                         recorded_by = ?, remarks = ?,
                         recorded_at = NOW()
                     WHERE match_id = ?"
                );
                $stmt->bind_param(
                    "iiisi",
                    $score1,
                    $score2,
                    $user_id,
                    $remarks,
                    $mid
                );
            } else {
                $existing->close();

                $stmt = $conn->prepare(
                    "INSERT INTO match_results
                     (match_id, team1_score, team2_score, recorded_by, remarks, recorded_at)
                     VALUES (?, ?, ?, ?, ?, NOW())"
                );
                $stmt->bind_param(
                    "iiiis",
                    $mid,
                    $score1,
                    $score2,
                    $user_id,
                    $remarks
                );
            }

            if ($stmt->execute()) {
                $stmt->close();

                // Mark the match as completed
                $update = $conn->prepare(
                    "UPDATE matches SET status = 'Completed' WHERE match_id = ?"
                );
                $update->bind_param("i", $mid);
                $update->execute();
                $update->close();

                header("Location: match_results.php?saved=1");
                exit;
            } else {
                $message = "Could not save the result. Please check your database.";
                $stmt->close();
            }
        }

        $check->close();
    }
}

if (isset($_GET["saved"])) {
    $message = "Match result saved successfully!";
    $messageType = "success";
}

// Load recorded results
$results = [];
$sql = "SELECT
            r.result_id,
            r.match_id,
            r.team1_score,
            r.team2_score,
            r.remarks,
            m.match_date,
            t1.team_name AS team1_name,
            t2.team_name AS team2_name,
            c.name AS competition_name
        FROM match_results r
        JOIN matches m ON r.match_id = m.match_id
        LEFT JOIN teams t1 ON m.team1_id = t1.team_id
        LEFT JOIN teams t2 ON m.team2_id = t2.team_id
        LEFT JOIN competitions c ON m.competition_id = c.competition_id
        ORDER BY m.match_date DESC";

$resultQuery = $conn->query($sql);

if ($resultQuery) {
    while ($row = $resultQuery->fetch_assoc()) {
        $results[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Results - University Sports</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="mb-4">
        <h2>Match Results</h2>
        <p class="text-muted">
            Record scores and view completed match results.
        </p>
    </div>

    <?php if ($message !== ""): ?>
        <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-4">
            <h4 class="mb-3">Record Match Score</h4>

            <?php if (count($matches) === 0): ?>
                <div class="alert alert-warning">
                    No matches found. Schedule a match first.
                </div>
                <a href="add_match.php" class="btn btn-primary">
                    Schedule Match
                </a>
            <?php else: ?>

            <form method="POST">

                <div class="mb-3">
                    <label class="form-label">Select Match</label>
                    <select name="match_id" class="form-select" required>
                        <option value="">Choose a match</option>

                        <?php foreach ($matches as $match): ?>
                            <option value="<?php echo (int)$match["match_id"]; ?>">
                                <?php echo htmlspecialchars(
                                    ($match["competition_name"] ?? "Competition") .
                                    " | " .
                                    ($match["team1_name"] ?? "Team 1") .
                                    " vs " .
                                    ($match["team2_name"] ?? "Team 2") .
                                    " | " .
                                    ($match["match_date"] ?? "")
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Team 1 Score</label>
                        <input
                            type="number"
                            name="team1_score"
                            class="form-control"
                            min="0"
                            step="1"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Team 2 Score</label>
                        <input
                            type="number"
                            name="team2_score"
                            class="form-control"
                            min="0"
                            step="1"
                            required
                        >
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Remarks (Optional)</label>
                    <textarea
                        name="remarks"
                        class="form-control"
                        rows="3"
                        placeholder="Add notes about the match"
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Save Match Result
                </button>

            </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4 class="mb-3">Recorded Results</h4>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Competition</th>
                            <th>Match</th>
                            <th>Score</th>
                            <th>Date</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $item): ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($item["competition_name"] ?? "-"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["team1_name"] ?? "Team 1"); ?>
                                    vs
                                    <?php echo htmlspecialchars($item["team2_name"] ?? "Team 2"); ?>
                                </td>

                                <td>
                                    <strong>
                                        <?php echo (int)$item["team1_score"]; ?>
                                        -
                                        <?php echo (int)$item["team2_score"]; ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["match_date"] ?? "-"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($item["remarks"] ?? ""); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No results recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <a href="matches.php" class="btn btn-outline-primary">
                View Matches
            </a>

            <a href="dashboard.php" class="btn btn-outline-secondary">
                Back to Dashboard
            </a>
        </div>
    </div>

</div>

</body>
</html>
