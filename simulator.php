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

$simulatedHealth = null;
$simCommitment = null;
$suggestion = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $simCommitment = [
        "commitment_name" => $_POST['commitment_name'],
        "category" => $_POST['category'],
        "monthly_amount" => (float)$_POST['monthly_amount'],
        "duration_text" => $_POST['duration_text'] ?: "Ongoing"
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
    $testCommitments[] = $simCommitment;

    $simulatedHealth = calculateHealth($profile, $testCommitments, $goals);

    $status = $simulatedHealth['status'];
    $amount = $simCommitment['monthly_amount'];
    $safeLimit = $currentHealth['max_recommended'];

    if ($status == "Safe") {
        $suggestion = "You can proceed. This commitment still keeps your financial score in a safe range.";
    } elseif ($status == "Caution") {
        $suggestion = "You may proceed carefully, but reducing the amount to around " . money($safeLimit) . "/month would be safer.";
    } else {
        $suggestion = "Avoid or delay this commitment for now. Try reducing the amount below " . money($safeLimit) . "/month.";
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
            <div class="stat">Current Score<b><?php echo $currentHealth['score_after']; ?>/100</b></div>
            <div class="stat">Current Commitment<b><?php echo money($currentHealth['total_new_commitment']); ?></b></div>
            <div class="stat">Safe Monthly Limit<b><?php echo money($currentHealth['max_recommended']); ?></b></div>
        </div>
    </div>

    <div class="grid two">
        <div class="card">
            <h2>Try New Commitment</h2>
            <p class="muted">
                Enter one possible commitment such as phone instalment, car loan, subscription, or family support.
            </p>

            <form method="POST" class="form">
                <div>
                    <label>Commitment Name</label>
                    <input name="commitment_name" placeholder="e.g. Phone instalment" required>
                </div>

                <div>
                    <label>Monthly Amount</label>
                    <input type="number" step="0.01" name="monthly_amount" placeholder="e.g. 250" required>
                </div>

                <div>
                    <label>Category</label>
                    <select name="category">
                        <option>Financing</option>
                        <option>Takaful</option>
                        <option>Family</option>
                        <option>Education</option>
                        <option>Installment</option>
                        <option>Lifestyle</option>
                        <option>Custom</option>
                    </select>
                </div>

                <div>
                    <label>Duration</label>
                    <input name="duration_text" placeholder="e.g. 12 months / Ongoing">
                </div>

                <div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn primary" type="submit" name="simulate">Simulate Impact</button>
                    <a class="btn secondary" href="commitments.php?profile_id=<?php echo $profile_id; ?>">Back to Commitments</a>
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
                        If you test RM250/month, CashCue will show your before vs after score and recommendation.
                    </p>
                </div>
            <?php else: ?>
                <div class="grid two">
                    <div class="scorebox <?php echo getScoreClass($currentHealth['score_after']); ?>">
                        <b>Before</b>
                        <div class="score"><?php echo $currentHealth['score_after']; ?><span style="font-size:18px;">/100</span></div>
                        <span><?php echo $currentHealth['status']; ?></span>
                    </div>

                    <div class="scorebox <?php echo getScoreClass($simulatedHealth['score_after']); ?>">
                        <b>After</b>
                        <div class="score"><?php echo $simulatedHealth['score_after']; ?><span style="font-size:18px;">/100</span></div>
                        <span><?php echo $simulatedHealth['status']; ?></span>
                    </div>
                </div>

                <div class="feature" style="margin-top:14px;">
                    <b>New Commitment Tested</b>
                    <p class="muted" style="margin-bottom:0;">
                        <?php echo htmlspecialchars($simCommitment['commitment_name']); ?> —
                        <?php echo money($simCommitment['monthly_amount']); ?>/month
                        for <?php echo htmlspecialchars($simCommitment['duration_text']); ?>
                    </p>
                </div>

                <div class="coach <?php echo $simulatedHealth['status'] == 'Safe' ? 'good' : ($simulatedHealth['status'] == 'Caution' ? 'warn' : 'bad'); ?>">
                    <b>CashCue Suggestion</b>
                    <p style="margin-bottom:0;"><?php echo htmlspecialchars($suggestion); ?></p>
                </div>

                <form method="POST" style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                    <input type="hidden" name="commitment_name" value="<?php echo htmlspecialchars($simCommitment['commitment_name']); ?>">
                    <input type="hidden" name="monthly_amount" value="<?php echo htmlspecialchars($simCommitment['monthly_amount']); ?>">
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($simCommitment['category']); ?>">
                    <input type="hidden" name="duration_text" value="<?php echo htmlspecialchars($simCommitment['duration_text']); ?>">

                    <button class="btn primary" type="submit" name="save_commitment">Save This Commitment</button>
                    <a class="btn secondary" href="simulator.php?profile_id=<?php echo $profile_id; ?>">Try Another Amount</a>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>