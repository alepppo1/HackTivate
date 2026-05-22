<?php
include "config.php";
include "functions.php";

$profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;
if (!$profile_id && isset($_SESSION['profile_id'])) {
    $profile_id = (int)$_SESSION['profile_id'];
}

if (!$profile_id) {
    header("Location: salary.php?error=salary_required&from=coach");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM salary_profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    unset($_SESSION['profile_id']);
    header("Location: salary.php?error=salary_required&from=coach");
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
$advice = generateCoachAdvice($health);

$title = "This plan looks manageable.";
if ($health['status'] == "Caution") $title = "Possible, but your margin is thin.";
if ($health['status'] == "Risky") $title = "This plan may hurt your salary.";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Coach - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>
<main>
    <div class="grid two">
        <div class="card">
            <div class="pill" data-en="AI Coach" data-bm="Nasihat AI">AI Coach</div>
            <h2 style="margin-top:14px;"><?php echo $title; ?></h2>
            <p class="muted" data-en="Clear advice without financial jargon." data-bm="Nasihat jelas tanpa istilah kewangan yang berat.">Clear advice without financial jargon.</p>

            <div class="scorebox <?php echo getScoreClass($health['score_after']); ?>" style="margin:18px 0;">
                <b>Decision: <?php echo $health['status']; ?></b>
                <div class="score"><?php echo $health['score_after']; ?><span style="font-size:18px;">/100</span></div>
                <span><?php echo money($health['total_new_commitment']); ?> commitments + <?php echo money($health['total_goal_saving']); ?> goals</span>
            </div>

            <?php foreach ($advice as $a): ?>
                <div class="coach <?php echo $a['type']; ?>">
                    <b><?php echo $a['type'] == 'good' ? '✓' : '!' ; ?> <?php echo $a['title']; ?></b>
                    <p class="muted" style="margin:8px 0 0;"><?php echo $a['text']; ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 data-en="Safer Options" data-bm="Pilihan Lebih Selamat">Safer Options</h2>

            <div class="coach good">
                <b data-en="Lower the monthly amount" data-bm="Kurangkan jumlah bulanan">Lower the monthly amount</b>
                <p class="muted" style="margin:8px 0 0;">Target around <?php echo money($health['max_recommended']); ?>/month.</p>
            </div>

            <div class="coach good">
                <b data-en="Extend the timeline" data-bm="Panjangkan tempoh">Extend the timeline</b>
                <p class="muted" style="margin:8px 0 0;" data-en="A longer goal timeline reduces monthly pressure." data-bm="Tempoh matlamat yang lebih panjang mengurangkan tekanan bulanan.">A longer goal timeline reduces monthly pressure.</p>
            </div>

            <div class="coach good">
                <b data-en="Build emergency fund first" data-bm="Bina dana kecemasan dahulu">Build emergency fund first</b>
                <p class="muted" style="margin:8px 0 0;">Aim for at least <?php echo money($health['after_commitment'] * 3); ?>.</p>
            </div>

            <div class="coach good">
                <b data-en="Delay big commitments" data-bm="Tangguh komitmen besar">Delay big commitments</b>
                <p class="muted" style="margin:8px 0 0;" data-en="Wait 3–6 months while reducing existing debt." data-bm="Tunggu 3–6 bulan sambil kurangkan hutang sedia ada.">Wait 3–6 months while reducing existing debt.</p>
            </div>
        </div>
    </div>
</main>
</body>
</html>
