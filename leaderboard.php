
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "
    SELECT
        t.team_id,
        t.team_name,
        t.sport_type,
        t.department,
        COUNT(mr.result_id) AS played,
        SUM(
            CASE
                WHEN mr.team1_score > mr.team2_score
                     AND m.team1_id = t.team_id THEN 1
                WHEN mr.team2_score > mr.team1_score
                     AND m.team2_id = t.team_id THEN 1
                ELSE 0
            END
        ) AS wins,
        SUM(
            CASE
                WHEN mr.team1_score < mr.team2_score
                     AND m.team1_id = t.team_id THEN 1
                WHEN mr.team2_score < mr.team1_score
                     AND m.team2_id = t.team_id THEN 1
                ELSE 0
            END
        ) AS losses,
        SUM(
            CASE
                WHEN mr.team1_score = mr.team2_score
                     AND (m.team1_id = t.team_id OR m.team2_id = t.team_id)
                THEN 1
                ELSE 0
            END
        ) AS draws,
        SUM(
            CASE
                WHEN m.team1_id = t.team_id THEN mr.team1_score
                WHEN m.team2_id = t.team_id THEN mr.team2_score
                ELSE 0
            END
        ) AS goals_for,
        SUM(
            CASE
                WHEN m.team1_id = t.team_id THEN mr.team2_score
                WHEN m.team2_id = t.team_id THEN mr.team1_score
                ELSE 0
            END
        ) AS goals_against
    FROM teams t
    LEFT JOIN matches m
        ON (m.team1_id = t.team_id OR m.team2_id = t.team_id)
    LEFT JOIN match_results mr
        ON mr.match_id = m.match_id
       AND m.status = 'Completed'
    GROUP BY
        t.team_id,
        t.team_name,
        t.sport_type,
        t.department
    ORDER BY
        (COALESCE(SUM(
            CASE
                WHEN (m.team1_id = t.team_id AND mr.team1_score > mr.team2_score)
                  OR (m.team2_id = t.team_id AND mr.team2_score > mr.team1_score)
                THEN 3
                WHEN mr.team1_score = mr.team2_score
                     AND (m.team1_id = t.team_id OR m.team2_id = t.team_id)
                THEN 1
                ELSE 0
            END
        ), 0)) DESC,
        (COALESCE(SUM(
            CASE
                WHEN m.team1_id = t.team_id THEN mr.team1_score - mr.team2_score
                WHEN m.team2_id = t.team_id THEN mr.team2_score - mr.team1_score
                ELSE 0
            END
        ), 0)) DESC,
        t.team_name ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("Unable to load leaderboard. Please check your database tables.");
}

$teams = [];

while ($row = $result->fetch_assoc()) {
    $row["points"] = ((int)$row["wins"] * 3) + (int)$row["draws"];
    $row["goal_difference"] = (int)$row["goals_for"] - (int)$row["goals_against"];
    $teams[] = $row;
}

usort($teams, function ($a, $b) {
    if ($a["points"] !== $b["points"]) {
        return $b["points"] <=> $a["points"];
    }

    if ($a["goal_difference"] !== $b["goal_difference"]) {
        return $b["goal_difference"] <=> $a["goal_difference"];
    }

    return strcmp($a["team_name"], $b["team_name"]);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - University Sports</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            University Sports Management
        </a>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2>Competition Leaderboard</h2>
            <p class="text-muted mb-0">
                Teams are ranked by points, then goal difference.
            </p>
        </div>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Rank</th>
                            <th>Team</th>
                            <th>Sport</th>
                            <th>Department</th>
                            <th>Played</th>
                            <th>Won</th>
                            <th>Drawn</th>
                            <th>Lost</th>
                            <th>GF</th>
                            <th>GA</th>
                            <th>GD</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (count($teams) > 0): ?>
                        <?php foreach ($teams as $index => $team): ?>
                            <tr>
                                <td>
                                    <strong><?php echo $index + 1; ?></strong>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($team["team_name"]); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($team["sport_type"] ?? ""); ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($team["department"]); ?>
                                </td>
                                <td><?php echo (int)$team["played"]; ?></td>
                                <td><?php echo (int)$team["wins"]; ?></td>
                                <td><?php echo (int)$team["draws"]; ?></td>
                                <td><?php echo (int)$team["losses"]; ?></td>
                                <td><?php echo (int)$team["goals_for"]; ?></td>
                                <td><?php echo (int)$team["goals_against"]; ?></td>
                                <td><?php echo (int)$team["goal_difference"]; ?></td>
                                <td>
                                    <span class="badge bg-success fs-6">
                                        <?php echo (int)$team["points"]; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center py-4">
                                No teams have been registered yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="text-muted small mb-0">
                Scoring: Win = 3 points, Draw = 1 point, Loss = 0 points.
                GF = Goals For, GA = Goals Against, GD = Goal Difference.
            </p>
        </div>
    </div>
</div>

</body>
</html>
