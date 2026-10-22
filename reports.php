
<?php
require_once "auth.php";
requireLogin();
require_once "db.php";

function countRows($conn, $sql) {
    $result = $conn->query($sql);

    if ($result) {
        $row = $result->fetch_assoc();
        return (int)($row["total"] ?? 0);
    }

    return 0;
}

$totalCompetitions = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM competitions"
);

$totalTeams = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM teams"
);

$totalPlayers = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM players"
);

$totalMatches = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM matches"
);

$completedMatches = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM matches WHERE status = 'Completed'"
);

$pendingMatches = countRows(
    $conn,
    "SELECT COUNT(*) AS total FROM matches WHERE status <> 'Completed'"
);


$sportFilter = $_GET["sport_type"] ?? "";
$statusFilter = $_GET["status"] ?? "";

$sql = "
    SELECT
        c.competition_id,
        c.name,
        c.sport_type,
        c.start_date,
        c.end_date,
        c.status,
        COUNT(DISTINCT r.team_id) AS registered_teams,
        COUNT(DISTINCT m.match_id) AS total_matches
    FROM competitions c
    LEFT JOIN registrations r
        ON c.competition_id = r.competition_id
    LEFT JOIN matches m
        ON c.competition_id = m.competition_id
    WHERE
        (? = '' OR c.sport_type = ?)
        AND (? = '' OR c.status = ?)
    GROUP BY
        c.competition_id,
        c.name,
        c.sport_type,
        c.start_date,
        c.end_date,
        c.status
    ORDER BY c.start_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssss",
    $sportFilter,
    $sportFilter,
    $statusFilter,
    $statusFilter
);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Reports</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f3f6fa;
        }

        .report-card {
            border: 0;
            border-radius: 12px;
            box-shadow: 0 3px 12px #00000010;
        }

        .report-title {
            color: #17365d;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
            }

            .report-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark bg-primary no-print">
    <div class="container">
        <span class="navbar-brand">University Sports Management</span>
        <a href="dashboard.php" class="btn btn-outline-light btn-sm">
            Back to Dashboard
        </a>
    </div>
</nav>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="report-title">Competition Reports</h2>
            <p class="text-muted mb-0">
                Summary of competitions, teams, players and matches.
            </p>
        </div>

        <button class="btn btn-success no-print"
                onclick="window.print()">
            Print / Save PDF
        </button>


        <a href="export_reports.php"
   class="btn btn-primary no-print">
    Download CSV
</a>
    </div>

    <div class="row g-3 mb-4">

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Competitions</p>
                    <h3><?= $totalCompetitions ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Teams</p>
                    <h3><?= $totalTeams ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Players</p>
                    <h3><?= $totalPlayers ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">All Matches</p>
                    <h3><?= $totalMatches ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Completed</p>
                    <h3><?= $completedMatches ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-lg-2">
            <div class="card report-card">
                <div class="card-body">
                    <p class="text-muted mb-1">Not Completed</p>
                    <h3><?= $pendingMatches ?></h3>
                </div>
            </div>
        </div>

    </div>

    <div class="card report-card">
        <div class="card-body">
            
<h4 class="mb-3">Competition Details</h4>

<form method="GET" class="row g-3 mb-4 no-print">

    <div class="col-md-4">
        <label class="form-label">Sport Type</label>
        <select name="sport_type" class="form-select">
            <option value="">All Sports</option>
            <option value="Football"
                <?= $sportFilter === "Football" ? "selected" : "" ?>>
                Football
            </option>
            <option value="Basketball"
                <?= $sportFilter === "Basketball" ? "selected" : "" ?>>
                Basketball
            </option>
            <option value="Volleyball"
                <?= $sportFilter === "Volleyball" ? "selected" : "" ?>>
                Volleyball
            </option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Competition Status</label>
        <select name="status" class="form-select">
            <option value="">All Statuses</option>
            <option value="Upcoming"
                <?= $statusFilter === "Upcoming" ? "selected" : "" ?>>
                Upcoming
            </option>
            <option value="Ongoing"
                <?= $statusFilter === "Ongoing" ? "selected" : "" ?>>
                Ongoing
            </option>
            <option value="Completed"
                <?= $statusFilter === "Completed" ? "selected" : "" ?>>
                Completed
            </option>
        </select>
    </div>

    <div class="col-md-4 d-flex align-items-end gap-2">
        <button type="submit" class="btn btn-primary">
            Filter Reports
        </button>

        <a href="reports.php" class="btn btn-secondary">
            Reset
        </a>
    </div>

</form>


            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Competition</th>
                            <th>Sport</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Registered Teams</th>
                            <th>Matches</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= (int)$row["competition_id"] ?></td>
                                <td><?= htmlspecialchars($row["name"]) ?></td>
                                <td><?= htmlspecialchars($row["sport_type"]) ?></td>
                                <td><?= htmlspecialchars($row["start_date"]) ?></td>
                                <td><?= htmlspecialchars($row["end_date"]) ?></td>
                                <td><?= htmlspecialchars($row["status"]) ?></td>
                                <td><?= (int)$row["registered_teams"] ?></td>
                                <td><?= (int)$row["total_matches"] ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                No competitions found.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

</body>
</html>
