
<?php
require_once "auth.php";
requireRole(["admin", "coordinator"]);
require_once "db.php";

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $sport_type = trim($_POST["sport_type"] ?? "");
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $status = $_POST["status"] ?? "Upcoming";

    $allowed_statuses = ["Upcoming", "Ongoing", "Completed"];

    if (
        $name === "" || $sport_type === "" ||
        $start_date === "" || $end_date === ""
    ) {
        $error = "Please fill in all fields.";
    } elseif ($end_date < $start_date) {
        $error = "End date must be on or after the start date.";
    } elseif (!in_array($status, $allowed_statuses, true)) {
        $error = "Invalid competition status.";
    } else {
        $stmt = $conn->prepare(
            "INSERT INTO competitions
            (name, sport_type, start_date, end_date, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $name,
            $sport_type,
            $start_date,
            $end_date,
            $status
        );

        if ($stmt->execute()) {
            $message = "Competition added successfully!";
        } else {
            $error = "Could not save the competition.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Competition</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow-sm border-0 mx-auto p-4"
         style="max-width: 650px;">

        <h1 class="h3 mb-4">🏆 Add Sports Competition</h1>

        <?php if ($message !== ""): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($message) ?>
                <a href="competitions.php" class="alert-link">
                    View competitions
                </a>
            </div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label class="form-label">Competition Name</label>
            <input type="text" name="name" class="form-control mb-3"
                   required maxlength="150"
                   value="<?= htmlspecialchars($_POST["name"] ?? "") ?>"
                   placeholder="e.g. Annual University Sports Competition">

            <label class="form-label">Sport Type</label>
            <select name="sport_type" class="form-select mb-3" required>
                <option value="">Select sport</option>
                <?php
                $sports = ["Football", "Volleyball", "Basketball",
                           "Athletics", "Tennis", "Other"];
                foreach ($sports as $sport):
                ?>
                    <option value="<?= htmlspecialchars($sport) ?>"
                        <?= (($_POST["sport_type"] ?? "") === $sport)
                            ? "selected" : "" ?>>
                        <?= htmlspecialchars($sport) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label class="form-label">Start Date</label>
            <input type="date" name="start_date"
                   class="form-control mb-3" required
                   value="<?= htmlspecialchars($_POST["start_date"] ?? "") ?>">

            <label class="form-label">End Date</label>
            <input type="date" name="end_date"
                   class="form-control mb-3" required
                   value="<?= htmlspecialchars($_POST["end_date"] ?? "") ?>">

            <label class="form-label">Status</label>
            <select name="status" class="form-select mb-4" required>
                <?php foreach (["Upcoming", "Ongoing", "Completed"] as $s): ?>
                    <option value="<?= $s ?>"
                        <?= (($_POST["status"] ?? "Upcoming") === $s)
                            ? "selected" : "" ?>>
                        <?= $s ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn btn-primary w-100">
                Save Competition
            </button>
        </form>

        <a href="dashboard.php" class="btn btn-outline-secondary mt-3">
            Back to Dashboard
        </a>
    </div>
</div>

</body>
</html>
