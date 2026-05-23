# CashCue: Pre-Commitment Financial Safety Checker

## Track
Track 1 – Reimagine Money

## Problem Statement
Many young salary earners in Malaysia make monthly financial commitments such as phone instalments, vehicle loans, subscriptions, takaful, or family support without clearly knowing whether the commitment is safe for their income level. Most budgeting apps only track spending after money has already been used. By that time, the user may already be locked into a recurring payment that affects their savings, emergency fund, and life goals.

This problem is especially common among fresh graduates, first-job employees, and gig workers who may have unstable income, limited financial knowledge, and pressure to make lifestyle or family-related commitments. They need a simple way to check, before committing, whether a new monthly payment is Safe, Caution, or Risky.

CashCue solves this by acting as a pre-commitment financial readiness checker. Instead of only recording expenses, CashCue helps users test commitments before they happen, understand the impact on their financial safety score, and receive simple next-step advice.

## Target Users
- Fresh graduates starting their first job
- Young salary earners planning new commitments
- Gig workers with uncertain monthly income
- Students or young adults learning financial planning
- Users who want simple Shariah-aware financial guidance

## Project Idea
CashCue helps users check whether a new monthly commitment or life goal is financially safe before they decide.

Simple flow:
1. Submit salary profile
2. Add existing commitments
3. Add life goals
4. View Safe / Caution / Risky financial readiness result
5. Use Commitment Simulator to test a new commitment
6. Open AI Coach for simple advice

## Proposed Solution
CashCue calculates a financial readiness score using salary, commitments, savings, emergency fund, goals, and simulated future commitments. The system gives users a clear result instead of complicated financial terms.

The main output is:
- CashCue Safety Score
- Safe / Caution / Risky status
- Commitment ratio
- Saving rate
- Emergency fund check
- Goal pressure check
- Before vs after score in the simulator
- Next Best Action recommendation

## Key Features
- Salary affordability score
- Commitment ratio and saving rate check
- Emergency fund coverage check
- Life goal monthly saving calculator
- Commitment Simulator for “what-if” testing
- Duration-based commitment impact
- Before vs after safety score
- Safe / Caution / Risky result logic
- Next Best Action recommendation
- Shariah-aware responsible planning note
- ENG/BM language toggle
- Minimalist pastel banking-style interface
- Smooth hover animation on navigation and buttons
- Salary-required error message before Goals / Result / Coach can be used
- Auto database/table setup when MySQL is running

## What Makes CashCue Different
Most budgeting tools help users after they spend. CashCue helps users before they commit.

Example:
A user wants to buy a motorcycle for RM500/month for 5 years. CashCue shows the current score, the after-commitment score, the total commitment value, and whether the user should proceed, reduce, delay, or avoid.

## AI Logic
CashCue uses explainable rule-based AI logic. It calculates:
- commitment ratio
- saving rate
- emergency fund coverage
- monthly goal saving
- duration pressure
- before vs after score

The AI Coach converts the calculation result into simple advice that users can understand.

## Responsible AI Note
CashCue does not replace professional financial advice, bank approval, or official Shariah advisory. It provides simple affordability guidance based on user-entered data. If the user enters incomplete or inaccurate data, the result may also be inaccurate.

## Tech Stack
- PHP
- MySQL
- HTML
- CSS
- JavaScript

## Setup Instructions

### 1. Put project folder in XAMPP
Copy the project folder into:

```text
C:/xampp/htdocs/
