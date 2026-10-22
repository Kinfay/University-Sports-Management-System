
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "db.php";

$sql = "SELECT competition_id, name, sport_type,
               start_date, end_date, status
        FROM competitions
        ORDER BY start_date DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Competitions - University Sports</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark" style="background:#102a43;">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">
            🏆 University Sports
        </a>
        <a href="dashboard.php" class="btn btn-outline-light">
            Back to Dashboard
        </a>
    </div>
</nav>

<div class="container py-5">
    <h1 class="mb-2">Sports Competitions</h1>
    <p class="text-secondary">
        View university sports competitions and their schedules.
    </p>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Competition Name</th>
                            <th>Sport</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row["competition_id"] ?></td>
                                <td>
                                    <?= htmlspecialchars($row["name"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row["sport_type"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row["start_date"]) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($row["end_date"]) ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?= htmlspecialchars($row["status"]) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                No competitions found.
                                Add a competition to the database first.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <a href="dashboard.php" class="btn btn-secondary mt-3">
        Back to Dashboard
    </a>
</div>

</body>
</html>
