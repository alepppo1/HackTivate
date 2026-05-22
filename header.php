<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="wrap topbar">
        <a class="brand" href="index.php" aria-label="CashCue home">
            <div class="logo">CC</div>
            <div>
                <h1>CashCue</h1>
                <p data-en="AI financial readiness checker" data-bm="Semakan kesediaan kewangan AI">AI financial readiness checker</p>
            </div>
        </a>

        <nav aria-label="Main navigation">
            <a class="<?php echo $current == 'index.php' ? 'active' : ''; ?>" href="index.php" data-en="Home" data-bm="Utama">Home</a>
            <a class="<?php echo $current == 'salary.php' ? 'active' : ''; ?>" href="salary.php" data-en="Salary" data-bm="Gaji">Salary</a>
            <a class="<?php echo $current == 'commitments.php' ? 'active' : ''; ?>" href="commitments.php" data-en="Commitments" data-bm="Komitmen">Commitments</a>
            <a class="<?php echo $current == 'goals.php' ? 'active' : ''; ?>" href="goals.php" data-en="Goals" data-bm="Matlamat">Goals</a>
            <a class="<?php echo $current == 'result.php' ? 'active' : ''; ?>" href="result.php" data-en="Result" data-bm="Keputusan">Result</a>
            <a class="<?php echo $current == 'coach.php' ? 'active' : ''; ?>" href="coach.php" data-en="Coach" data-bm="Nasihat">Coach</a>
        </nav>

        <div class="header-actions">
            <span class="shariah-pill" data-en="Shariah-aware" data-bm="Mesra Syariah">Shariah-aware</span>
            <div class="lang-toggle" aria-label="Language toggle">
                <button type="button" data-lang="en" class="active">ENG</button>
                <button type="button" data-lang="bm">BM</button>
            </div>
        </div>
    </div>
</header>
<script src="script.js"></script>
