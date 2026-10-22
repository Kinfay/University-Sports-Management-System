
<?php
require_once "auth.php";
requireRole(["admin", "coordinator"]);
require_once "db.php";

// Excel-compatible CSV download
header("Content-Type: text/csv; charset=UTF-8");
header('Content-Disposition: attachment; filename="competition_reports.csv"');

// Open output stream
$output = fopen("php://output", "w");

// UTF-8 BOM helps Excel display Amharic and other Unicode text
fwrite($output, "\xEF\xBB\xBF");

// CSV column headings
fputcsv($output, [
    "Competition ID",
    "Competition Name",
    "Sport Type",
    "Start Date",
    "End Date",
    "Status",
    "Registered Teams",
    "Total Matches"
]);

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
    GROUP BY
        c.competition_id,
        c.name,
        c.sport_type,
        c.start_date,
        c.end_date,
        c.status
    ORDER BY c.start_date DESC
";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row["competition_id"],
            $row["name"],
            $row["sport_type"],
            $row["start_date"],
            $row["end_date"],
            $row["status"],
            $row["registered_teams"],
            $row["total_matches"]
        ]);
    }
}

fclose($output);
$conn->close();
exit;
