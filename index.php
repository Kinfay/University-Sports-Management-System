
<?php
$pageTitle = "University Sports Competition Management Platform";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <style>
        body {
            background: #f4f7fb;
            font-family: Arial, sans-serif;
        }
        .navbar {
            background: #102a43;
        }
        .hero {
            background: linear-gradient(135deg, #102a43, #147d64);
            color: white;
            padding: 65px 25px;
            border-radius: 18px;
        }
        .sport-card {
            border: 0;
            border-radius: 15px;
            transition: transform .2s;
        }
        .sport-card:hover {
            transform: translateY(-5px);
        }
        .sport-icon {
            font-size: 42px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            🏆 University Sports
        </a>
        <a class="btn btn-outline-light" href="login.php">Login</a>
    </div>
</nav>

<main class="container py-5">
    <section class="hero text-center shadow">
        <h1 class="display-5 fw-bold">
            University Sports Competition Platform
        </h1>
        <p class="lead mt-3">
            Manage competitions, register teams, schedule matches,
            and follow results in one place.
        </p>
        <a href="#sports" class="btn btn-warning btn-lg mt-3">
            Explore Sports
        </a>
    </section>

    <section id="sports" class="py-5">
        <h2 class="text-center fw-bold mb-4">Sports Categories</h2>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card sport-card shadow-sm h-100 text-center p-4">
                    <div class="sport-icon">⚽</div>
                    <h3 class="h5 mt-3">Football</h3>
                    <p class="text-secondary">
                        Manage football teams and matches.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card sport-card shadow-sm h-100 text-center p-4">
                    <div class="sport-icon">🏐</div>
                    <h3 class="h5 mt-3">Volleyball</h3>
                    <p class="text-secondary">
                        Organize volleyball competitions.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card sport-card shadow-sm h-100 text-center p-4">
                    <div class="sport-icon">🏀</div>
                    <h3 class="h5 mt-3">Basketball</h3>
                    <p class="text-secondary">
                        Track basketball fixtures and results.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="text-center bg-white rounded-4 shadow-sm p-4">
        <h2 class="h4">One Platform, Many Sports</h2>
        <p class="text-secondary mb-0">
            Competition schedules, team registration, and results
            will be managed through the platform.
        </p>
    </section>
</main>

<footer class="text-center text-secondary py-4">
    &copy; <?php echo date("Y"); ?>
    University Sports Competition Management Platform
</footer>

</body>
</html>
