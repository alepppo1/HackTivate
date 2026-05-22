<?php
include "config.php";
include "functions.php";

$profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;

if (!$profile_id && isset($_SESSION['profile_id'])) {
    $profile_id = (int)$_SESSION['profile_id'];
}

if (!$profile_id) {
    header("Location: salary.php?error=salary_required&from=simulator");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM salary_profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    unset($_SESSION['profile_id']);
    header("Location: salary.php?error=salary_required&from=simulator");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM commitments WHERE profile_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$commitments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT * FROM life_goals WHERE profile_id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$currentHealth = calculateHealth($profile, $commitments, $goals);

function convertDurationToMonths($durationNumber, $durationUnit) {
    $durationNumber = max(1, (int)$durationNumber);

    if ($durationUnit == "weeks") {
        return max(1, round($durationNumber / 4.345));
    }

    if ($durationUnit == "years") {
        return $durationNumber * 12;
    }

    return $durationNumber;
}

function getDurationPenalty($durationMonths) {
    if ($durationMonths <= 1) {
        return 0;
    } elseif ($durationMonths <= 3) {
        return 2;
    } elseif ($durationMonths <= 12) {
        return 5;
    } elseif ($durationMonths <= 36) {
        return 9;
    } elseif ($durationMonths <= 60) {
        return 13;
    } else {
        return 18;
    }
}

$simulatedHealth = null;
$simCommitment = null;
$suggestion = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $durationNumber = isset($_POST['duration_number']) ? (int)$_POST['duration_number'] : 1;
    $durationUnit = isset($_POST['duration_unit']) ? $_POST['duration_unit'] : "months";

    $durationMonths = convertDurationToMonths($durationNumber, $durationUnit);
    $durationPenalty = getDurationPenalty($durationMonths);

    $originalMonthlyAmount = (float)$_POST['monthly_amount'];
    $durationText = $durationNumber . " " . $durationUnit;
    $totalObligation = $originalMonthlyAmount * $durationMonths;

    $simCommitment = [
        "commitment_name" => $_POST['commitment_name'],
        "category" => $_POST['category'],
        "monthly_amount" => $originalMonthlyAmount,
        "duration_text" => $durationText,
        "duration_number" => $durationNumber,
        "duration_unit" => $durationUnit,
        "duration_months" => $durationMonths,
        "duration_penalty" => $durationPenalty,
        "total_obligation" => $totalObligation
    ];

    if (isset($_POST['save_commitment'])) {
        $stmt = $conn->prepare("INSERT INTO commitments (profile_id, commitment_name, category, monthly_amount, duration_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issds",
            $profile_id,
            $simCommitment['commitment_name'],
            $simCommitment['category'],
            $simCommitment['monthly_amount'],
            $simCommitment['duration_text']
        );
        $stmt->execute();

        header("Location: commitments.php?profile_id=" . $profile_id);
        exit;
    }

    $testCommitments = $commitments;

    $testCommitments[] = [
        "commitment_name" => $simCommitment['commitment_name'],
        "category" => $simCommitment['category'],
        "monthly_amount" => $simCommitment['monthly_amount'],
        "duration_text" => $simCommitment['duration_text']
    ];

    $simulatedHealth = calculateHealth($profile, $testCommitments, $goals);

    // Extra simulator-only duration penalty
    // This makes longer commitments affect the final simulation score.
    $simulatedHealth['score_after'] = max(0, $simulatedHealth['score_after'] - $durationPenalty);

    if ($simulatedHealth['score_after'] >= 80) {
        $simulatedHealth['status'] = "Safe";
    } elseif ($simulatedHealth['score_after'] >= 50) {
        $simulatedHealth['status'] = "Caution";
    } else {
        $simulatedHealth['status'] = "Risky";
    }

    $status = $simulatedHealth['status'];
    $safeLimit = $currentHealth['max_recommended'];

    if ($status == "Safe") {
        $suggestion = "You can proceed. This commitment still keeps your financial score in a safe range.";
    } elseif ($status == "Caution") {
        if ($safeLimit > 0) {
            $suggestion = "You may proceed carefully, but reducing the amount to around " . money($safeLimit) . "/month would be safer.";
        } else {
            $suggestion = "You may proceed carefully, but your current profile has very limited room for a new monthly commitment.";
        }
    } else {
        if ($safeLimit > 0) {
            $suggestion = "Avoid or delay this commitment for now. Try reducing the amount below " . money($safeLimit) . "/month.";
        } else {
            $suggestion = "Avoid or delay this commitment for now. Based on your current salary profile, CashCue does not recommend adding a new commitment yet.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commitment Simulator - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include "header.php"; ?>

<main>
    <div class="card" style="margin-bottom:20px;">
        <div class="clean-row" style="align-items:flex-start;flex-wrap:wrap;">
            <div>
                <div class="pill">What-if Simulator</div>
                <h2 style="margin-top:14px;">Commitment Simulator</h2>
                <p class="muted">
                    Test a new monthly commitment before adding it to your real commitment list.
                </p>
            </div>

            <span class="badge d<?php echo $currentHealth['status']; ?>">
                Current Status: <?php echo $currentHealth['status']; ?>
            </span>
        </div>

        <div class="grid three" style="margin-top:16px;">
            <div class="stat">
                Current Score
                <b><?php echo $currentHealth['score_after']; ?>/100</b>
            </div>

            <div class="stat">
                Current Commitment
                <b><?php echo money($currentHealth['total_new_commitment']); ?></b>
            </div>

            <div class="stat">
                Safe Monthly Limit
                <b><?php echo money($currentHealth['max_recommended']); ?></b>
            </div>
        </div>
    </div>

    <div class="grid two">
        <div class="card">
            <h2>Try New Commitment</h2>

            <p class="muted">
                Enter one possible commitment such as phone instalment, car loan,
                subscription, or family support.
            </p>

            <form method="POST" class="form">
                <div>
                    <label>Commitment Name</label>
                    <input 
                        name="commitment_name" 
                        placeholder="e.g. Phone instalment" 
                        required
                    >
                </div>

                <div>
                    <label>Monthly Amount</label>
                    <input 
                        type="number" 
                        step="0.01" 
                        name="monthly_amount" 
                        placeholder="e.g. 250" 
                        required
                    >
                </div>

                <div>
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Financing">Financing</option>
                        <option value="Takaful">Takaful</option>
                        <option value="Family">Family</option>
                        <option value="Education">Education</option>
                        <option value="Installment">Installment</option>
                        <option value="Lifestyle">Lifestyle</option>
                        <option value="Custom">Custom</option>
                    </select>
                </div>

                <div>
                    <label>Duration</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                        <input 
                            type="number" 
                            name="duration_number" 
                            min="1" 
                            placeholder="e.g. 12" 
                            required
                        >

                        <select name="duration_unit" required>
                            <option value="weeks">Weeks</option>
                            <option value="months" selected>Months</option>
                            <option value="years">Years</option>
                        </select>
                    </div>
                </div>

                <div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn primary" type="submit" name="simulate">
                        Simulate Impact
                    </button>

                    <a class="btn secondary" href="commitments.php?profile_id=<?php echo $profile_id; ?>">
                        Back to Commitments
                    </a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2>Simulation Result</h2>

            <?php if (!$simulatedHealth): ?>
                <p class="muted">
                    Your result will appear here after you enter a possible new commitment.
                </p>

                <div class="feature">
                    <b>Example</b>
                    <p class="muted" style="margin-bottom:0;">
                        If you test RM250/month for 12 months, CashCue will show
                        your before vs after score and recommendation.
                    </p>
                </div>
            <?php else: ?>
                <div class="grid two">
                    <div class="scorebox <?php echo getScoreClass($currentHealth['score_after']); ?>">
                        <b>Before</b>
                        <div class="score">
                            <?php echo $currentHealth['score_after']; ?>
                            <span style="font-size:18px;">/100</span>
                        </div>
                        <span><?php echo $currentHealth['status']; ?></span>
                    </div>

                    <div class="scorebox <?php echo getScoreClass($simulatedHealth['score_after']); ?>">
                        <b>After</b>
                        <div class="score">
                            <?php echo $simulatedHealth['score_after']; ?>
                            <span style="font-size:18px;">/100</span>
                        </div>
                        <span><?php echo $simulatedHealth['status']; ?></span>
                    </div>
                </div>

                <div class="feature" style="margin-top:14px;">
                    <b>New Commitment Tested</b>
                    <p class="muted" style="margin-bottom:0;">
                        <?php echo htmlspecialchars($simCommitment['commitment_name']); ?> —
                        <?php echo money($simCommitment['monthly_amount']); ?>/month
                        for <?php echo htmlspecialchars($simCommitment['duration_text']); ?>
                        <br>
                        Total commitment value:
                        <?php echo money($simCommitment['total_obligation']); ?>
                        <br>
                        Duration impact:
                        <?php echo $simCommitment['duration_months']; ?> months
                        <br>
                        Duration risk penalty:
                        <?php echo $simCommitment['duration_penalty']; ?> points
                    </p>
                </div>

                <div class="coach <?php echo $simulatedHealth['status'] == 'Safe' ? 'good' : ($simulatedHealth['status'] == 'Caution' ? 'warn' : 'bad'); ?>">
                    <b>CashCue Suggestion</b>
                    <p style="margin-bottom:0;">
                        <?php echo htmlspecialchars($suggestion); ?>
                    </p>
                </div>

                <form method="POST" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="hidden" name="commitment_name" value="<?php echo htmlspecialchars($simCommitment['commitment_name']); ?>">
                    <input type="hidden" name="monthly_amount" value="<?php echo htmlspecialchars($simCommitment['monthly_amount']); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($simCommitment['category']); ?>">
                    <input type="hidden" name="duration_number" value="<?php echo htmlspecialchars($simCommitment['duration_number']); ?>">
                    <input type="hidden" name="duration_unit" value="<?php echo htmlspecialchars($simCommitment['duration_unit']); ?>">

                    <button class="btn primary" type="submit" name="save_commitment">
                        Save This Commitment
                    </button>

                    <a class="btn secondary" href="simulator.php?profile_id=<?php echo $profile_id; ?>">
                        Try Another Amount
                    </a>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <div style="margin-top:20px;">
        <a class="btn primary" href="result.php?profile_id=<?php echo $profile_id; ?>" data-en="View Final Result" data-bm="Lihat Keputusan Akhir">
            View Final Result
        </a>
    </div>
</main>

</body>
</html>