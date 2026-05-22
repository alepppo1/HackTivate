# CashCue: AI Financial Readiness Checker

## Track
Track 1 – Reimagine Money

## Project Idea
CashCue helps salary earners check whether a new monthly commitment or life goal is safe before they decide.

Simple flow:
1. Submit salary profile
2. Add commitments
3. Add goals
4. View Safe / Caution / Risky result
5. Open AI Coach for simple advice

## Key Features
- Salary affordability score
- Commitment ratio and saving rate check
- Emergency fund coverage check
- Life goal monthly saving calculator
- Shariah-aware responsible planning note
- ENG/BM language toggle
- Minimalist pastel banking-style interface
- Smooth hover animation on navigation and buttons
- Salary-required error message before Goals/Result/Coach can be used
- Auto database/table setup when MySQL is running

## Tech Stack
- PHP
- MySQL
- HTML
- CSS
- JavaScript

## Setup Instructions

### 1. Put project folder in XAMPP
Copy the `cashcue_php` folder into:

```text
C:/xampp/htdocs/
```

### 2. Start XAMPP
Start:
- Apache
- MySQL

### 3. Run project
Open:

```text
http://localhost/cashcue_php/
```

CashCue will automatically create the database `cashcue_db` and the required tables when the website is opened.

### Optional manual database setup
If your lecturer/friend wants to import manually, open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Import this file:

```text
cashcue_php/database.sql
```

## AI Logic
CashCue uses explainable rule-based AI logic. It calculates:
- commitment ratio
- saving rate
- emergency fund coverage
- monthly goal saving
- before vs after score

The AI Coach converts the calculations into simple advice.

## AI Tools Used
- ChatGPT for idea development, prototype planning, UI refinement, and code assistance
- Rule-based AI logic in the prototype for explainable financial advice

## Notes for Judges
This is a hackathon prototype. It provides affordability guidance only and does not replace official financial advice or bank approval.
