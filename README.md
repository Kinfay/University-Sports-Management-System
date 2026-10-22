# University Sports Management System

## 1. Project Overview

The University Sports Management System is a web-based application designed to manage university sports competitions, teams, players, matches, results, and reports in one centralized system.

## 2. Project Objectives

- Manage sports competitions.
- Register and manage teams and players.
- Schedule and manage matches.
- Record match scores and results.
- Display competition leaderboards.
- Manage system users and roles.
- Generate competition reports.
- Export reports as CSV files and print or save them as PDF.

## 3. Technologies Used

- **Frontend:** HTML, CSS, Bootstrap, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Local Server:** Apache using XAMPP
- **Development Tool:** Visual Studio Code

## 4. Main Features

1. User Registration and Login
2. Dashboard
3. Competition Management
4. Team Management
5. Player Registration
6. Match Scheduling
7. Match Results Management
8. Leaderboard
9. User and Role Management
10. Competition Reports
11. CSV Export
12. Print and Save Reports as PDF

## 5. System Requirements

- Windows, Linux, or another supported operating system
- XAMPP with Apache, PHP, and MySQL
- Visual Studio Code or another code editor
- A modern web browser

## 6. Installation and Setup

1. Install XAMPP.
2. Copy the `university_sports` project folder into the XAMPP `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin in your browser.
5. Create or import the `sports_db` database using the project's SQL database file.
6. Check the database connection settings in `db.php`.
7. Open the application at:

   `http://localhost/university_sports/`

8. Register or log in with an authorized account.

## 7. Database Tables

The system uses the following main tables:

- `users`
- `competitions`
- `teams`
- `players`
- `registrations`
- `matches`
- `match_results`

## 8. Project Folder

The project is stored in:

`C:\xampp\htdocs\university_sports`

## 9. Security

The application includes login sessions and role-based access control. Use strong passwords and ensure that each account has only the permissions it needs.

## 10. Project Status

The system includes core competition management, team and player management, match management, results, leaderboard, user management, and reporting features. Further testing and improvements may be required before production deployment.

## 11. Author

Add your name and department here.

## 12. License

Add the appropriate license information if the project is distributed publicly.
