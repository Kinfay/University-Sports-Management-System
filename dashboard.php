
<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

function getCount($conn, $table) {
    $allowedTables = [
        "users",
        "competitions",
        "teams",
        "players",
        "matches"
    ];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if ($result) {
        return (int) $result->fetch_assoc()["total"];
    }

    return 0;
}

$userName = $_SESSION["full_name"] ?? "User";
$userRole = $_SESSION["role"] ?? "team_manager";
$competitionsCount = getCount($conn, "competitions");
$teamsCount = getCount($conn, "teams");
$playersCount = getCount($conn, "players");
$matchesCount = getCount($conn, "matches");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - University Sports</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <span class="navbar-brand">
            University Sports Management
        </span>

        <a href="logout.php" class="btn btn-outline-light btn-sm">
            Logout
        </a>
    </div>
</nav>

<div class="container py-5">

    <div class="mb-4">
        <h2>
            Welcome,
            <?php echo htmlspecialchars($userName); ?>!
        </h2>

        <p class="text-muted">
    Manage competitions, teams, players, and matches from one place.
</p>

<p>
    Your Role:
    <span class="badge bg-info text-dark">
        <?php echo htmlspecialchars($userRole); ?>
    </span>
</p>
    </div>

    <div class="row g-4 mb-5">

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Competitions</p>
                    <h2><?php echo $competitionsCount; ?></h2>
                    <a href="competitions.php" class="btn btn-outline-primary btn-sm">
                        View Competitions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Teams</p>
                    <h2><?php echo $teamsCount; ?></h2>
                    <a href="teams.php" class="btn btn-outline-primary btn-sm">
                        View Teams
                    </a>
                    <a href="add_team.php" class="btn btn-primary btn-sm mt-2">
                        Add Team
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Players</p>
                    <h2><?php echo $playersCount; ?></h2>
                    <a href="players.php" class="btn btn-outline-primary btn-sm">
                        View Players
                    </a>
                    <a href="add_player.php" class="btn btn-primary btn-sm mt-2">
                        Add Player
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Matches</p>
                    <h2><?php echo $matchesCount; ?></h2>
                    <a href="matches.php" class="btn btn-outline-primary btn-sm">
                        View Matches
                    </a>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h4>Quick Actions</h4>


            
   <?php if ($userRole === "admin"): ?>
    <a href="users.php" class="btn btn-secondary">
        Manage Users
    </a>
<?php endif; ?>

<a href="reports.php" class="btn btn-info">
    Competition Reports
</a>




            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="add_competition.php" class="btn btn-success">
                    + Add Competition
                </a>

                <a href="add_team.php" class="btn btn-primary">
                    + Add Team
                </a>

                <a href="add_player.php" class="btn btn-info">
                    + Register Player
                </a>

                <a href="matches.php" class="btn btn-dark">
                    Manage Matches
                </a>
                <a href="match_results.php" class="btn btn-success">
    Match Results
</a>
<a href="leaderboard.php" class="btn btn-warning">
    View Leaderboard
</a>

<a href="reports.php" class="btn btn-info">
    Competition Reports
</a>
            </div>
        </div>
    </div>

</div>

</body>
</html>
