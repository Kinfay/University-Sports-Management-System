
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT
            p.player_id,
            p.student_id,
            p.full_name,
            t.team_name,
            t.sport_type,
            t.department
        FROM players p
        LEFT JOIN teams t ON p.team_id = t.team_id
        ORDER BY p.player_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Players - University Sports</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h2>Registered Players</h2>
            <p class="text-muted mb-0">
                View students registered in sports teams.
            </p>
        </div>

        <a href="add_player.php" class="btn btn-primary">
            + Register Player
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Team</th>
                            <th>Sport</th>
                            <th>Department</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($player = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo (int) $player["player_id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($player["student_id"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($player["full_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($player["team_name"] ?? "No team"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($player["sport_type"] ?? "-"); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($player["department"] ?? "-"); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No players registered yet.
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
