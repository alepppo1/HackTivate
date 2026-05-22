CREATE DATABASE IF NOT EXISTS cashcue_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cashcue_db;

CREATE TABLE IF NOT EXISTS salary_profiles (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS commitments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    commitment_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    duration_text VARCHAR(100) DEFAULT 'Ongoing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(profile_id),
    CONSTRAINT commitments_profile_fk FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS life_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_id INT NOT NULL,
    goal_name VARCHAR(100) NOT NULL,
    target_amount DECIMAL(10,2) NOT NULL,
    current_saved DECIMAL(10,2) NOT NULL DEFAULT 0,
    target_months INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(profile_id),
    CONSTRAINT goals_profile_fk FOREIGN KEY (profile_id) REFERENCES salary_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
