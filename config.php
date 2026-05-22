<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CashCue database configuration
// Default XAMPP settings: user = root, password = empty.
$host = "localhost";
$user = "root";
$password = "";
$database = "cashcue_db";

// Avoid raw fatal mysqli screens. Show a clean setup error instead.
mysqli_report(MYSQLI_REPORT_OFF);

function cashcue_setup_error($title, $message) {
    http_response_code(500);
    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>";
    echo "<title>CashCue Setup</title><style>body{margin:0;font-family:Segoe UI,Arial,sans-serif;background:#fbf8f3;color:#18222f;padding:40px}.box{max-width:720px;margin:auto;background:white;border:1px solid #eadfd4;border-radius:20px;padding:24px;box-shadow:0 8px 20px rgba(16,42,67,.07)}code{background:#f7f3ee;padding:3px 6px;border-radius:8px}</style></head><body>";
    echo "<div class='box'><h2>" . htmlspecialchars($title) . "</h2><p>" . htmlspecialchars($message) . "</p>";
    echo "<p>Please start <b>Apache</b> and <b>MySQL</b> in XAMPP, then refresh this page.</p>";
    echo "<p>If you changed your MySQL password, update it in <code>config.php</code>.</p></div></body></html>";
    exit;
}

$conn = @new mysqli($host, $user, $password);

if ($conn->connect_errno) {
    cashcue_setup_error("Database connection failed", "CashCue cannot connect to MySQL right now.");
}

$conn->set_charset("utf8mb4");

if (!$conn->query("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
    cashcue_setup_error("Database setup failed", "CashCue could not create the database. Please check your MySQL permission.");
}

if (!$conn->select_db($database)) {
    cashcue_setup_error("Database selection failed", "CashCue could not select the database after creating it.");
}

$schema = [
    "CREATE TABLE IF NOT EXISTS salary_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_name VARCHAR(100) NOT NULL,
        monthly_salary DECIMAL(10,2) NOT NULL,
        fixed_needs DECIMAL(10,2) NOT NULL,
        existing_debt DECIMAL(10,2) NOT NULL,
        lifestyle_spending DECIMAL(10,2) NOT NULL,
        monthly_savings DECIMAL(10,2) NOT NULL,
        emergency_fund DECIMAL(10,2) NOT NULL,
        dependents INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS commitments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        profile_id INT NOT NULL,
        commitment_name VARCHAR(100) NOT NULL,
        category VARCHAR(50) NOT NULL,
        monthly_amount DECIMAL(10,2) NOT NULL,
        duration_text VARCHAR(100) DEFAULT 'Ongoing',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(profile_id),
        CONSTRAINT commitments_profile_fk FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS life_goals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        profile_id INT NOT NULL,
        goal_name VARCHAR(100) NOT NULL,
        target_amount DECIMAL(10,2) NOT NULL,
        current_saved DECIMAL(10,2) NOT NULL DEFAULT 0,
        target_months INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX(profile_id),
        CONSTRAINT goals_profile_fk FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($schema as $query) {
    if (!$conn->query($query)) {
        cashcue_setup_error("Table setup failed", "CashCue could not create one of the required tables. Please check phpMyAdmin or import database.sql manually.");
    }
}
?>
