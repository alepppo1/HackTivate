<?php
include "config.php";
include "functions.php";

$profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;
if (!$profile_id && isset($_SESSION['profile_id'])) {
    $profile_id = (int)$_SESSION['profile_id'];
}

if (!$profile_id) {
    header("Location: salary.php?error=salary_required&from=commitments");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM commitments WHERE id = ? AND profile_id = ?");
        $stmt->bind_param("ii", $delete_id, $profile_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO commitments (profile_id, commitment_name, category, monthly_amount, duration_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "issds",
            $profile_id,
            $_POST['commitment_name'],
            $_POST['category'],
            $_POST['monthly_amount'],
            $_POST['duration_text']
        );
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT * FROM salary_profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    unset($_SESSION['profile_id']);
    header("Location: salary.php?error=salary_required&from=commitments");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM commitments WHERE profile_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$commitments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$salaryPlan = calculateHealth($profile, [], []);

$salary = (float)$profile['monthly_salary'];
$needsDebt = (float)$profile['fixed_needs'] + (float)$profile['existing_debt'];
$wants = (float)$profile['lifestyle_spending'];
$futureMoney = (float)$profile['monthly_savings'];

$needsDebtPercent = $salary > 0 ? ($needsDebt / $salary) * 100 : 0;
$wantsPercent = $salary > 0 ? ($wants / $salary) * 100 : 0;
$futurePercent = $salary > 0 ? ($futureMoney / $salary) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commitments - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>

<main>
    <div class="card" style="margin-bottom:20px;">
        <div class="clean-row" style="align-items:flex-start;flex-wrap:wrap;">
            <div>
                <div class="pill" data-en="Step 2 of 4" data-bm="Langkah 2 daripada 4">Step 2 of 4</div>
                <h2 style="margin-top:14px;" data-en="Current Salary Snapshot" data-bm="Ringkasan Gaji Semasa">Current Salary Snapshot</h2>
                <p class="muted" data-en="A quick view before adding any new commitment." data-bm="Paparan ringkas sebelum tambah komitmen baharu.">A quick view before adding any new commitment.</p>
            </div>
            <span class="badge d<?php echo getScoreStatus($salaryPlan['score_before']); ?>">Status: <?php echo getScoreStatus($salaryPlan['score_before']); ?></span>
        </div>

        <div class="grid two" style="margin-top:16px;">
            <div class="scorebox <?php echo getScoreClass($salaryPlan['score_before']); ?>">
                <b data-en="Financial Health" data-bm="Kesihatan Kewangan">Financial Health</b>
                <div class="score"><?php echo $salaryPlan['score_before']; ?><span style="font-size:18px;">/100</span></div>
                <span data-en="Before new commitment" data-bm="Sebelum komitmen baharu">Before new commitment</span>
            </div>

            <div class="grid two">
                <div class="stat">Salary<b><?php echo money($profile['monthly_salary']); ?></b></div>
                <div class="stat">Commitment Ratio<b><?php echo percent($salaryPlan['dti_before']); ?></b></div>
                <div class="stat">Saving Rate<b><?php echo percent($salaryPlan['save_before']); ?></b></div>
                <div class="stat">Emergency Cover<b><?php echo number_format($salaryPlan['emergency_before'], 1); ?> months</b></div>
            </div>
        </div>

        <div class="grid three" style="margin-top:16px;">
            <div class="feature">
                <div class="clean-row"><span>Needs + Debt</span><b><?php echo percent($needsDebtPercent); ?></b></div>
                <div class="bar"><div class="fill <?php echo $needsDebtPercent > 55 ? 'red' : ''; ?>" style="width:<?php echo min(100, $needsDebtPercent); ?>%;"></div></div>
            </div>
            <div class="feature">
                <div class="clean-row"><span>Wants</span><b><?php echo percent($wantsPercent); ?></b></div>
                <div class="bar"><div class="fill <?php echo $wantsPercent > 35 ? 'red' : ''; ?>" style="width:<?php echo min(100, $wantsPercent); ?>%;"></div></div>
            </div>
            <div class="feature">
                <div class="clean-row"><span>Future Money</span><b><?php echo percent($futurePercent); ?></b></div>
                <div class="bar"><div class="fill <?php echo $futurePercent < 10 ? 'red' : ''; ?>" style="width:<?php echo min(100, $futurePercent); ?>%;"></div></div>
            </div>
        </div>
    </div>

    <div class="grid two">
        <div class="card">
            <h2 data-en="Add Commitment" data-bm="Tambah Komitmen">Add Commitment</h2>
            <p class="muted" data-en="Test one monthly payment at a time." data-bm="Uji satu bayaran bulanan pada satu masa.">Test one monthly payment at a time.</p>

            <form method="POST" class="form">
                <div>
                    <label data-en="Name" data-bm="Nama">Name</label>
                    <input name="commitment_name" placeholder="e.g. Car financing" required>
                </div>

                <div>
                    <label data-en="Monthly Amount" data-bm="Jumlah Bulanan">Monthly Amount</label>
                    <input type="text" inputmode="decimal" name="monthly_amount" placeholder="e.g. 750" required>
                </div>

                <div>
                    <label data-en="Category" data-bm="Kategori">Category</label>
                    <select name="category">
                        <option>Financing</option>
                        <option>Takaful</option>
                        <option>Family</option>
                        <option>Education</option>
                        <option>Installment</option>
                        <option>Custom</option>
                    </select>
                </div>

                <div>
                    <label data-en="Duration" data-bm="Tempoh">Duration</label>
                    <input name="duration_text" placeholder="e.g. 7 years / Ongoing">
                </div>

                <div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn primary" type="submit" data-en="Add" data-bm="Tambah">Add</button>
                    <a class="btn secondary" href="goals.php?profile_id=<?php echo $profile_id; ?>" data-en="Continue to Goals" data-bm="Terus ke Matlamat">Continue to Goals</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 data-en="To Test" data-bm="Untuk Diuji">To Test</h2>

            <?php if (empty($commitments)): ?>
                <p class="muted" data-en="No commitment added yet." data-bm="Belum ada komitmen ditambah.">No commitment added yet.</p>
            <?php endif; ?>

            <?php foreach ($commitments as $c): ?>
                <div class="item">
                    <div>
                        <b><?php echo htmlspecialchars($c['commitment_name']); ?></b>
                        <div class="muted">
                            <?php echo htmlspecialchars($c['category']); ?> • <?php echo htmlspecialchars($c['duration_text']); ?>
                        </div>
                    </div>

                    <div class="item-actions">
                        <b><?php echo money($c['monthly_amount']); ?></b>
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="delete_id" value="<?php echo $c['id']; ?>">
                            <button class="delete" type="submit" data-en="Delete" data-bm="Padam">Delete</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</main>
</body>
</html>
