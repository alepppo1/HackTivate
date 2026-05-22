<?php
include "config.php";
include "functions.php";

$profile_id = isset($_GET['profile_id']) ? (int)$_GET['profile_id'] : null;
if (!$profile_id && isset($_SESSION['profile_id'])) {
    $profile_id = (int)$_SESSION['profile_id'];
}

if (!$profile_id) {
    header("Location: salary.php?error=salary_required&from=goals");
    exit;
}

$stmt = $conn->prepare("SELECT id FROM salary_profiles WHERE id = ?");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

if (!$profile) {
    unset($_SESSION['profile_id']);
    header("Location: salary.php?error=salary_required&from=goals");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_id'])) {
        $delete_id = (int)$_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM life_goals WHERE id = ? AND profile_id = ?");
        $stmt->bind_param("ii", $delete_id, $profile_id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO life_goals (profile_id, goal_name, target_amount, current_saved, target_months) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isddi", $profile_id, $_POST['goal_name'], $_POST['target_amount'], $_POST['current_saved'], $_POST['target_months']);
        $stmt->execute();
    }
}

$stmt = $conn->prepare("SELECT * FROM life_goals WHERE profile_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $profile_id);
$stmt->execute();
$goals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Life Goals - CashCue</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>
<main>
    <div class="grid two">
        <div class="card">
            <div class="pill" data-en="Step 3 of 4" data-bm="Langkah 3 daripada 4">Step 3 of 4</div>
            <h2 style="margin-top:14px;" data-en="Life Goals" data-bm="Matlamat Hidup">Life Goals</h2>
            <p class="muted" data-en="Add non-loan goals like Umrah, study, deposit, or emergency fund." data-bm="Tambah matlamat bukan pinjaman seperti Umrah, belajar, deposit, atau dana kecemasan.">Add non-loan goals like Umrah, study, deposit, or emergency fund.</p>

            <form method="POST" class="form">
                <div>
                    <label data-en="Goal Name" data-bm="Nama Matlamat">Goal Name</label>
                    <input name="goal_name" placeholder="e.g. Umrah / Study" required>
                </div>
                <div>
                    <label data-en="Target Amount" data-bm="Jumlah Sasaran">Target Amount</label>
                    <input type="number" step="0.01" name="target_amount" placeholder="e.g. 6000" required>
                </div>
                <div>
                    <label data-en="Current Saved" data-bm="Simpanan Semasa">Current Saved</label>
                    <input type="number" step="0.01" name="current_saved" placeholder="e.g. 800" required>
                </div>
                <div>
                    <label data-en="Target Months" data-bm="Tempoh Bulan">Target Months</label>
                    <input type="number" name="target_months" placeholder="e.g. 18" required>
                </div>
                <div style="grid-column:1/-1;display:flex;gap:10px;flex-wrap:wrap;">
                    <button class="btn primary" type="submit" data-en="Add Goal" data-bm="Tambah Matlamat">Add Goal</button>
                    <a class="btn secondary" href="result.php?profile_id=<?php echo $profile_id; ?>" data-en="See Result" data-bm="Lihat Keputusan">See Result</a>
                </div>
            </form>
        </div>

        <div class="card">
            <h2 data-en="Goal List" data-bm="Senarai Matlamat">Goal List</h2>
            <?php if (empty($goals)): ?>
                <p class="muted" data-en="No goal added yet." data-bm="Belum ada matlamat ditambah.">No goal added yet.</p>
            <?php endif; ?>

            <?php foreach ($goals as $g): ?>
                <?php $monthly = calculateMonthlyGoal($g); ?>
                <div class="item">
                    <div>
                        <b><?php echo htmlspecialchars($g['goal_name']); ?></b>
                        <div class="muted">
                            Target <?php echo money($g['target_amount']); ?> • Saved <?php echo money($g['current_saved']); ?> • <?php echo (int)$g['target_months']; ?> months
                        </div>
                        <div class="muted"><b><?php echo money($monthly); ?>/month</b></div>
                    </div>
                    <div class="item-actions">
                        <form method="POST" style="margin:0;">
                            <input type="hidden" name="delete_id" value="<?php echo $g['id']; ?>">
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
