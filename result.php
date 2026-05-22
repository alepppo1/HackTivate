<?php
include "config.php";
include "functions.php";

$profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;
if (!$profile_id && isset($_SESSION['profile_id'])) {
    $profile_id = (int)$_SESSION['profile_id'];
}

if (!$profile_id) {
    header("Location: salary.php?error=salary_required&from=result");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM salary_profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    unset($_SESSION['profile_id']);
    header("Location: salary.php?error=salary_required&from=result");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM commitments WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$commitments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM life_goals WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$health = calculateHealth($profile, $commitments, $goals);
$decisionLine = "You can proceed, but keep monitoring your spending.";
if ($health['status'] == "Caution") $decisionLine = "Proceed only after reducing amount or extending timeline.";
if ($health['status'] == "Risky") $decisionLine = "Not recommended now. Reduce or delay the commitment.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Can I Commit? - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>
<main>
    <div class="card">
        <div class="clean-row" style="align-items:flex-start;flex-wrap:wrap;">
            <div>
                <div class="pill" data-en="Step 4 of 4" data-bm="Langkah 4 daripada 4">Step 4 of 4</div>
                <h2 style="margin-top:14px;" data-en="Can I Commit?" data-bm="Boleh Saya Komit?">Can I Commit?</h2>
                <p class="muted"><?php echo htmlspecialchars($decisionLine); ?></p>
            </div>
            <span class="badge d<?php echo $health['status']; ?>">Decision: <?php echo $health['status']; ?></span>
        </div>

        <div class="grid two" style="margin-top:18px;">
            <div class="scorebox <?php echo getScoreClass($health['score_after']); ?>">
                <b data-en="Affordability Score" data-bm="Skor Kemampuan">Affordability Score</b>
                <div class="score"><?php echo $health['score_after']; ?><span style="font-size:18px;">/100</span></div>
                <span>After commitment + goals</span>
            </div>

            <div class="grid two">
                <div class="stat">New Commitment<b><?php echo money($health['total_new_commitment']); ?></b></div>
                <div class="stat">Goal Saving<b><?php echo money($health['total_goal_saving']); ?></b></div>
                <div class="stat">Commitment Ratio<b><?php echo percent($health['dti_after']); ?></b></div>
                <div class="stat">Emergency Cover<b><?php echo number_format($health['emergency_after'], 1); ?> months</b></div>
            </div>
        </div>
    </div>

    <div class="grid two" style="margin-top:20px;">
        <div class="card">
            <h2 data-en="Before vs After" data-bm="Sebelum vs Selepas">Before vs After</h2>
            <div class="grid two">
                <div class="feature">
                    <h3>Before</h3>
                    <div class="stat">Score<b><?php echo $health['score_before']; ?>/100</b></div>
                    <div class="stat" style="margin-top:10px;">Saving Rate<b><?php echo percent($health['save_before']); ?></b></div>
                </div>
                <div class="feature">
                    <h3>After</h3>
                    <div class="stat">Score<b><?php echo $health['score_after']; ?>/100</b></div>
                    <div class="stat" style="margin-top:10px;">Saving Rate<b><?php echo percent($health['save_after']); ?></b></div>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 data-en="Simple Summary" data-bm="Ringkasan Mudah">Simple Summary</h2>
            <div class="feature">
                <b data-en="Recommended monthly limit" data-bm="Had bulanan dicadangkan">Recommended monthly limit</b>
                <h2 style="margin-top:8px;"><?php echo money($health['max_recommended']); ?>/month</h2>
            </div>
            <p class="muted" style="margin-top:14px;" data-en="Use this limit as a safer guide before taking another monthly payment." data-bm="Gunakan had ini sebagai panduan lebih selamat sebelum mengambil bayaran bulanan baharu.">
                Use this limit as a safer guide before taking another monthly payment.
            </p>
            <a class="btn primary" href="coach.php?profile_id=<?php echo $profile_id; ?>" data-en="Open AI Coach" data-bm="Buka Nasihat AI">Open AI Coach</a>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <h2 data-en="Items Tested" data-bm="Item Diuji">Items Tested</h2>
        <div class="grid two">
            <div>
                <h3 data-en="Commitments" data-bm="Komitmen">Commitments</h3>
                <?php if (empty($commitments)): ?>
                    <p class="muted">No commitments added.</p>
                <?php endif; ?>
                <?php foreach ($commitments as $c): ?>
                    <div class="item"><b><?php echo htmlspecialchars($c['commitment_name']); ?></b><b><?php echo money($c['monthly_amount']); ?>/month</b></div>
                <?php endforeach; ?>
            </div>
            <div>
                <h3 data-en="Goals" data-bm="Matlamat">Goals</h3>
                <?php if (empty($goals)): ?>
                    <p class="muted">No goals added.</p>
                <?php endif; ?>
                <?php foreach ($goals as $g): ?>
                    <div class="item"><b><?php echo htmlspecialchars($g['goal_name']); ?></b><b><?php echo money(calculateMonthlyGoal($g)); ?>/month</b></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</main>
</body>
</html>
