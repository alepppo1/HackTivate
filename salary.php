<?php
include "config.php";
include "functions.php";

$demoProfiles = [
    "aina" => [
        "user_name" => "Aina",
        "monthly_salary" => 3200,
        "fixed_needs" => 1350,
        "existing_debt" => 250,
        "lifestyle_spending" => 520,
        "monthly_savings" => 450,
        "emergency_fund" => 1800,
        "dependents" => 0,
        "label" => "Fresh Graduate",
        "description" => "Aina just started her first job and wants to know if she can safely add a new monthly commitment."
    ],
    "hafiz" => [
        "user_name" => "Hafiz",
        "monthly_salary" => 2800,
        "fixed_needs" => 1200,
        "existing_debt" => 350,
        "lifestyle_spending" => 650,
        "monthly_savings" => 250,
        "emergency_fund" => 900,
        "dependents" => 1,
        "label" => "Gig Worker",
        "description" => "Hafiz earns from delivery and freelance work, so he needs to be extra careful before adding commitments."
    ],
    "mei" => [
        "user_name" => "Mei Ling",
        "monthly_salary" => 4100,
        "fixed_needs" => 1650,
        "existing_debt" => 400,
        "lifestyle_spending" => 780,
        "monthly_savings" => 700,
        "emergency_fund" => 4200,
        "dependents" => 0,
        "label" => "First Job Employee",
        "description" => "Mei Ling is planning a lifestyle purchase and wants to check whether it affects her financial safety."
    ]
];

$selectedDemo = $_GET['demo'] ?? "";
$demo = $demoProfiles[$selectedDemo] ?? null;

function oldOrDemo($field, $demo) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[$field])) {
        return htmlspecialchars($_POST[$field]);
    }

    if ($demo && isset($demo[$field])) {
        return htmlspecialchars($demo[$field]);
    }

    return "";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO salary_profiles (user_name, monthly_salary, fixed_needs, existing_debt, lifestyle_spending, monthly_savings, emergency_fund, dependents) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "sddddddi",
        $_POST['user_name'],
        $_POST['monthly_salary'],
        $_POST['fixed_needs'],
        $_POST['existing_debt'],
        $_POST['lifestyle_spending'],
        $_POST['monthly_savings'],
        $_POST['emergency_fund'],
        $_POST['dependents']
    );
    $stmt->execute();
    $profile_id = $stmt->insert_id;
    $_SESSION['profile_id'] = $profile_id;
    header("Location: commitments.php?profile_id=" . $profile_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary Profile - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>

<main>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'salary_required'): ?>
        <div class="alert" role="alert">
            <b data-en="Salary profile required" data-bm="Profil gaji diperlukan">Salary profile required</b>
            <span data-en="Please submit your salary details first before opening Goals, Result, or Coach." data-bm="Sila isi maklumat gaji dahulu sebelum buka Matlamat, Keputusan, atau Nasihat.">
                Please submit your salary details first before opening Goals, Result, or Coach.
            </span>
        </div>
    <?php endif; ?>

    <?php if ($demo): ?>
        <div class="alert" role="alert">
            <b>Demo Persona Loaded: <?php echo htmlspecialchars($demo['user_name']); ?> — <?php echo htmlspecialchars($demo['label']); ?></b>
            <span><?php echo htmlspecialchars($demo['description']); ?></span>
        </div>
    <?php endif; ?>

    <div class="grid two">
        <div class="card">
            <div class="pill" data-en="Step 1 of 4" data-bm="Langkah 1 daripada 4">Step 1 of 4</div>

            <h2 style="margin-top:14px;" data-en="Salary Profile" data-bm="Profil Gaji">
                Salary Profile
            </h2>

            <p class="muted" data-en="Only enter the basic numbers needed for the affordability check." data-bm="Masukkan nombor asas yang diperlukan untuk semakan kemampuan.">
                Only enter the basic numbers needed for the affordability check.
            </p>

            <form method="POST" class="form">
                <div>
                    <label data-en="Name" data-bm="Nama">Name</label>
                    <input type="text" name="user_name" placeholder="e.g. Aina" value="<?php echo oldOrDemo('user_name', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Monthly Net Salary" data-bm="Gaji Bersih Bulanan">Monthly Net Salary</label>
                    <input type="number" name="monthly_salary" placeholder="e.g. 4200" step="0.01" value="<?php echo oldOrDemo('monthly_salary', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Fixed Needs" data-bm="Keperluan Tetap">Fixed Needs</label>
                    <input type="number" name="fixed_needs" placeholder="rent, food, bills" step="0.01" value="<?php echo oldOrDemo('fixed_needs', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Existing Debt" data-bm="Hutang Sedia Ada">Existing Debt</label>
                    <input type="number" name="existing_debt" placeholder="e.g. 700" step="0.01" value="<?php echo oldOrDemo('existing_debt', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Lifestyle Spending" data-bm="Perbelanjaan Gaya Hidup">Lifestyle Spending</label>
                    <input type="number" name="lifestyle_spending" placeholder="e.g. 850" step="0.01" value="<?php echo oldOrDemo('lifestyle_spending', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Monthly Savings" data-bm="Simpanan Bulanan">Monthly Savings</label>
                    <input type="number" name="monthly_savings" placeholder="e.g. 600" step="0.01" value="<?php echo oldOrDemo('monthly_savings', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Emergency Fund Now" data-bm="Dana Kecemasan Sekarang">Emergency Fund Now</label>
                    <input type="number" name="emergency_fund" placeholder="e.g. 3500" step="0.01" value="<?php echo oldOrDemo('emergency_fund', $demo); ?>" required>
                </div>

                <div>
                    <label data-en="Dependents" data-bm="Tanggungan">Dependents</label>
                    <input type="number" name="dependents" placeholder="e.g. 1" value="<?php echo oldOrDemo('dependents', $demo); ?>" required>
                </div>

                <div style="grid-column:1/-1;">
                    <button class="btn primary" type="submit" data-en="Save & Continue" data-bm="Simpan & Teruskan">
                        Save & Continue
                    </button>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 data-en="What the system checks" data-bm="Apa sistem semak">
                What the system checks
            </h2>

            <div class="grid two" style="margin-top:16px;">
                <div class="feature"><b data-en="Commitment ratio" data-bm="Nisbah komitmen">Commitment ratio</b></div>
                <div class="feature"><b data-en="Saving rate" data-bm="Kadar simpanan">Saving rate</b></div>
                <div class="feature"><b data-en="Emergency cover" data-bm="Perlindungan kecemasan">Emergency cover</b></div>
                <div class="feature"><b data-en="Goal affordability" data-bm="Kemampuan matlamat">Goal affordability</b></div>
            </div>

            <div class="feature" style="margin-top:16px;">
                <b data-en="Shariah-aware note" data-bm="Nota mesra Syariah">Shariah-aware note</b>
                <p class="muted" style="margin:8px 0 0;" data-en="The check focuses on affordability, risk, and responsible planning." data-bm="Semakan fokus pada kemampuan, risiko, dan perancangan bertanggungjawab.">
                    The check focuses on affordability, risk, and responsible planning.
                </p>
            </div>

            <?php if ($demo): ?>
                <div class="feature" style="margin-top:16px;">
                    <b>Why this demo matters</b>
                    <p class="muted" style="margin:8px 0 0;">
                        This persona helps judges quickly understand how CashCue supports real young Malaysians before they make a financial commitment.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>