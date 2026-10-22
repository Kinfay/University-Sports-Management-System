
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$sql = "SELECT
            t.team_id,
            t.team_name,
            t.sport_type,
            t.department,
            u.full_name AS representative
        FROM teams t
        LEFT JOIN users u
            ON t.representative_id = u.user_id
        ORDER BY t.team_id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams - University Sports</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2>University Sports Teams</h2>
            <p class="text-muted mb-0">
                View and manage registered teams.
            </p>
        </div>

        <a href="add_team.php" class="btn btn-primary">
            + Add New Team
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">

                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Team Name</th>
                            <th>Sport</th>
                            <th>Department</th>
                            <th>Representative</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($team = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo (int) $team["team_id"]; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($team["team_name"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($team["sport_type"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($team["department"]); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($team["representative"] ?? "Not assigned"); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No teams found. Click "Add New Team" to register one.
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
