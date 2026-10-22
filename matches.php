
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT
            m.match_id,
            m.match_date,
            m.venue,
            m.status,
            c.name AS competition_name,
            t1.team_name AS team1_name,
            t2.team_name AS team2_name
        FROM matches m
        LEFT JOIN competitions c
            ON m.competition_id = c.competition_id
        LEFT JOIN teams t1
            ON m.team1_id = t1.team_id
        LEFT JOIN teams t2
            ON m.team2_id = t2.team_id
        ORDER BY m.match_date ASC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - University Sports</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2>Sports Matches</h2>
            <p class="text-muted mb-0">
                View scheduled university sports matches.
            </p>
        </div>

        <a href="add_match.php" class="btn btn-primary">
            + Schedule Match
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Competition</th>
                            <th>Team 1</th>
                            <th>Team 2</th>
                            <th>Date & Time</th>
                            <th>Venue</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($match = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo (int) $match["match_id"]; ?></td>

                                <td>
                                    <?php echo htmlspecialchars($match["competition_name"] ?? "Not assigned"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($match["team1_name"] ?? "Not assigned"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($match["team2_name"] ?? "Not assigned"); ?>
                                </td>

                                <td>
                                    <?php
                                    echo $match["match_date"]
                                        ? htmlspecialchars(date("M d, Y h:i A", strtotime($match["match_date"])))
                                        : "-";
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($match["venue"] ?? "-"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($match["status"] ?? "Scheduled"); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                No matches scheduled yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

            <a href="dashboard.php" class="btn btn-outline-secondary">
                Back to Dashboard
            </a>

        </div>
    </div>

</div>

</body>
</html>
