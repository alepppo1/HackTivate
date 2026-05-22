<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CashCue | AI Financial Readiness Checker</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include "header.php"; ?>

<main>
    <section class="hero">
        <div>
            <div class="pill" data-en="Track 1: Reimagine Money" data-bm="Trek 1: Bayangkan Semula Wang">Track 1: Reimagine Money</div>
            <h2 class="big" data-en="Know your limit before you commit." data-bm="Tahu had sebelum tambah komitmen.">Know your limit before you commit.</h2>
            <p class="muted lead" data-en="Check whether a new payment or life goal is safe for your salary." data-bm="Semak sama ada bayaran baharu atau matlamat hidup selamat untuk gaji anda.">
                Check whether a new payment or life goal is safe for your salary.
            </p>

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px;">
                <a class="btn primary" href="salary.php" data-en="Start Check" data-bm="Mula Semak">Start Check</a>
                <a class="btn secondary" href="#how" data-en="View Flow" data-bm="Lihat Aliran">View Flow</a>
            </div>
        </div>

        <div class="card">
            <div class="dark">
                <span class="pill" style="background:#ffffff18;color:white;border-color:#ffffff26;" data-en="Demo Preview" data-bm="Pratonton Demo">Demo Preview</span>
                <h2 style="color:white;margin-top:14px;" data-en="Clear decision after input" data-bm="Keputusan jelas selepas input">Clear decision after input</h2>
                <p style="color:#dbe7ef;margin-bottom:0;" data-en="One simple question: can you afford this safely?" data-bm="Satu soalan mudah: mampu atau tidak dengan selamat?">
                    One simple question: can you afford this safely?
                </p>
            </div>

            <div class="grid three" style="margin-top:14px;">
                <div class="stat">Score<b>--/100</b></div>
                <div class="stat">Ratio<b>--%</b></div>
                <div class="stat">Decision<b>--</b></div>
            </div>
        </div>
    </section>

    <section id="how" class="grid three" style="margin-top:24px;">
        <div class="feature step-card">
            <span class="step-no">1</span>
            <h3 data-en="Add salary" data-bm="Masukkan gaji">Add salary</h3>
            <p class="muted" data-en="Only the numbers needed for checking." data-bm="Hanya nombor penting untuk semakan.">Only the numbers needed for checking.</p>
        </div>
        <div class="feature step-card">
            <span class="step-no">2</span>
            <h3 data-en="Test a plan" data-bm="Uji perancangan">Test a plan</h3>
            <p class="muted" data-en="Add one payment or goal to test." data-bm="Tambah satu bayaran atau matlamat untuk diuji.">Add one payment or goal to test.</p>
        </div>
        <div class="feature step-card">
            <span class="step-no">3</span>
            <h3 data-en="Get advice" data-bm="Dapat nasihat">Get advice</h3>
            <p class="muted" data-en="Get Safe, Caution, or Risky advice." data-bm="Dapat nasihat Safe, Caution, atau Risky.">Get Safe, Caution, or Risky advice.</p>
        </div>
    </section>

    <section class="card" style="margin-top:24px;">
        <div class="clean-row" style="align-items:center;flex-wrap:wrap;">
            <div>
                <h2 data-en="Shariah-aware by design" data-bm="Mesra Syariah sejak reka bentuk">Shariah-aware by design</h2>
                <p class="muted" style="margin-bottom:0;" data-en="The prototype gives responsible affordability guidance, not official bank approval." data-bm="Prototaip ini memberi panduan kemampuan bertanggungjawab, bukan kelulusan rasmi bank.">
                    The prototype gives responsible affordability guidance, not official bank approval.
                </p>
            </div>
            <a class="btn primary" href="salary.php" data-en="Try Prototype" data-bm="Cuba Prototaip">Try Prototype</a>
        </div>
    </section>
</main>
</body>
</html>
