<?php
/**
 * dashboard.php — Productivity Hub
 * Central overview: Namaz, Study, Skills progress for the logged-in user.
 */

session_start();
require_once 'config/db.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id   = (int) $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name'] ?? 'Student', ENT_QUOTES, 'UTF-8');

// ── Safe percentage fetchers (each uses a dedicated prepared statement) ────────
// Column and table names are NEVER dynamic — hardcoded to prevent SQL injection.

function getStudyPercent(mysqli $conn, int $user_id): int {
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(completed),0) AS done, COUNT(*) AS total
         FROM study
         WHERE user_id = ?"
    );
    if (!$stmt) return 0;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row   = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $total = (int) $row['total'];
    $done  = (int) $row['done'];
    return $total > 0 ? (int) round(($done / $total) * 100) : 0;
}

function getSkillPercent(mysqli $conn, int $user_id): int {
    $stmt = $conn->prepare(
        "SELECT COALESCE(SUM(completed),0) AS done, COUNT(*) AS total
         FROM skills
         WHERE user_id = ?"
    );
    if (!$stmt) return 0;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row   = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $total = (int) $row['total'];
    $done  = (int) $row['done'];
    return $total > 0 ? (int) round(($done / $total) * 100) : 0;
}

function getNamazPercent(mysqli $conn, int $user_id): int {
    // Each row = one day. Max possible prayers per day = 5 (fajr+zuhar+asar+maghrib+isha).
    // So total possible = COUNT(*) * 5, done = SUM of all 5 columns.
    $stmt = $conn->prepare(
        "SELECT
            COALESCE(SUM(fajr + zuhar + asar + maghrib + isha), 0) AS done,
            COUNT(*) * 5 AS total
         FROM prayers
         WHERE user_id = ?"
    );
    if (!$stmt) return 0;
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row   = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $total = (int) $row['total'];
    $done  = (int) $row['done'];
    return $total > 0 ? (int) round(($done / $total) * 100) : 0;
}

// ── Fetch all stats ───────────────────────────────────────────────────────────
$namazPercent = getNamazPercent($conn, $user_id);
$studyPercent = getStudyPercent($conn, $user_id);
$skillPercent = getSkillPercent($conn, $user_id);

$totalScore = (int) round(($namazPercent + $studyPercent + $skillPercent) / 3);

// ── Greeting ──────────────────────────────────────────────────────────────────
$hour = (int) date('H');
$greeting = match(true) {
    $hour < 12 => 'Good Morning',
    $hour < 17 => 'Good Afternoon',
    $hour < 21 => 'Good Evening',
    default    => 'Good Night',
};

// ── Score label ───────────────────────────────────────────────────────────────
$score_label = match(true) {
    $totalScore >= 80 => 'Excellent',
    $totalScore >= 60 => 'Good',
    $totalScore >= 40 => 'Fair',
    default           => 'Needs Work',
};

$score_color = match(true) {
    $totalScore >= 80 => '#22c55e',
    $totalScore >= 60 => '#f59e0b',
    $totalScore >= 40 => '#60a5fa',
    default           => '#ef4444',
};

$today = date('l, F j'); // e.g. "Saturday, April 18"
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productivity Hub</title>

    <!-- Syne (display) + DM Sans (body) — consistent with tracker pages -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ── Reset ────────────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:           #0a0a0f;
            --surface:      #12121a;
            --surface-2:    #1c1c28;
            --border:       rgba(255,255,255,0.07);
            --text:         #e8e8f0;
            --muted:        #6e6e85;

            --green:        #22c55e;
            --blue:         #60a5fa;
            --pink:         #f472b6;
            --amber:        #f59e0b;
            --red:          #ef4444;

            --green-glow:   rgba(34,197,94,0.12);
            --blue-glow:    rgba(96,165,250,0.12);
            --pink-glow:    rgba(244,114,182,0.12);
        }

        html { scroll-behavior: smooth; }

        body {
            background:  var(--bg);
            color:       var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height:  100vh;
            display:     flex;
            flex-direction: column;
        }

        /* ── Top nav bar ──────────────────────────────────────────────────── */
        .topbar {
            display:         flex;
            align-items:     center;
            justify-content: space-between;
            padding:         0 2rem;
            height:          56px;
            border-bottom:   1px solid var(--border);
            background:      var(--surface);
            position:        sticky;
            top:             0;
            z-index:         10;
        }

        .topbar-brand {
            font-family:    'Syne', sans-serif;
            font-size:      .95rem;
            font-weight:    700;
            letter-spacing: -.01em;
            display:        flex;
            align-items:    center;
            gap:            7px;
        }

        .topbar-brand .dot {
            width:         8px;
            height:        8px;
            border-radius: 50%;
            background:    var(--green);
            animation:     pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50%       { opacity: .5; transform: scale(.75); }
        }

        .topbar-date {
            font-size: 12px;
            color:     var(--muted);
        }

        .logout-link {
            display:         flex;
            align-items:     center;
            gap:             5px;
            font-size:       13px;
            color:           var(--muted);
            text-decoration: none;
            padding:         5px 12px;
            border:          1px solid var(--border);
            border-radius:   8px;
            transition:      color .2s, border-color .2s, background .2s;
        }

        .logout-link:hover {
            color:        var(--red);
            border-color: rgba(239,68,68,0.4);
            background:   rgba(239,68,68,0.06);
        }

        /* ── Hero section ─────────────────────────────────────────────────── */
        .hero {
            padding:    3rem 2rem 2rem;
            text-align: center;
            animation:  fadeUp .5s ease;
        }

        .hero-greeting {
            font-size:   13px;
            color:       var(--muted);
            font-weight: 500;
            letter-spacing: .06em;
            text-transform: uppercase;
            margin-bottom: .4rem;
        }

        .hero-name {
            font-family:    'Syne', sans-serif;
            font-size:      clamp(1.8rem, 5vw, 2.8rem);
            font-weight:    800;
            letter-spacing: -.03em;
            line-height:    1.1;
            margin-bottom:  1.5rem;
        }

        /* ── Score ring ───────────────────────────────────────────────────── */
        .score-ring-wrap {
            display:         inline-flex;
            flex-direction:  column;
            align-items:     center;
            gap:             .75rem;
        }

        .score-ring {
            width:           100px;
            height:          100px;
            border-radius:   50%;
            background:      conic-gradient(
                                 <?= $score_color ?> calc(<?= $totalScore ?> * 1%),
                                 var(--surface-2) 0
                             );
            display:         flex;
            align-items:     center;
            justify-content: center;
            position:        relative;
        }

        .score-ring::before {
            content:       '';
            position:      absolute;
            width:         80px;
            height:        80px;
            border-radius: 50%;
            background:    var(--bg);
        }

        .score-inner {
            position:    relative;
            z-index:     1;
            text-align:  center;
        }

        .score-number {
            font-family: 'Syne', sans-serif;
            font-size:   1.4rem;
            font-weight: 800;
            color:       <?= $score_color ?>;
            line-height: 1;
        }

        .score-max {
            font-size: 10px;
            color:     var(--muted);
        }

        .score-label-pill {
            font-size:     11px;
            font-weight:   500;
            color:         <?= $score_color ?>;
            background:    <?= $score_color ?>1a;
            border:        1px solid <?= $score_color ?>33;
            padding:       3px 12px;
            border-radius: 20px;
        }

        /* ── Cards grid ───────────────────────────────────────────────────── */
        .grid {
            display:               grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap:                   1.25rem;
            padding:               0 2rem 3rem;
            max-width:             1100px;
            margin:                0 auto;
            width:                 100%;
        }

        /* ── Tracker card ─────────────────────────────────────────────────── */
        .tcard {
            background:    var(--surface);
            border:        1px solid var(--border);
            border-radius: 20px;
            padding:       1.75rem;
            display:       flex;
            flex-direction: column;
            gap:           1.25rem;
            transition:    transform .25s ease, border-color .25s ease, box-shadow .25s ease;
            animation:     fadeUp .5s ease both;
        }

        .tcard:hover {
            transform:    translateY(-4px);
            border-color: var(--card-accent-color);
            box-shadow:   0 16px 40px -12px var(--card-glow);
        }

        .tcard:nth-child(1) { animation-delay: .1s; }
        .tcard:nth-child(2) { animation-delay: .2s; }
        .tcard:nth-child(3) { animation-delay: .3s; }

        /* ── Card top row ─────────────────────────────────────────────────── */
        .card-top {
            display:     flex;
            align-items: center;
            gap:         10px;
        }

        .card-icon {
            width:           40px;
            height:          40px;
            border-radius:   12px;
            display:         flex;
            align-items:     center;
            justify-content: center;
            font-size:       18px;
            background:      var(--card-glow);
            border:          1px solid var(--card-accent-color);
            flex-shrink:     0;
        }

        .card-title {
            font-family:    'Syne', sans-serif;
            font-size:      1.05rem;
            font-weight:    700;
            letter-spacing: -.01em;
        }

        .card-percent-badge {
            margin-left:   auto;
            font-family:   'Syne', sans-serif;
            font-size:     1.1rem;
            font-weight:   700;
            color:         var(--card-accent-color);
        }

        /* ── Progress bar (horizontal) ────────────────────────────────────── */
        .progress-track {
            height:        6px;
            background:    var(--surface-2);
            border-radius: 99px;
            overflow:      hidden;
        }

        .progress-fill {
            height:        100%;
            border-radius: 99px;
            background:    var(--card-accent-color);
            transition:    width .8s cubic-bezier(.4,0,.2,1);
        }

        .progress-meta {
            display:         flex;
            justify-content: space-between;
            font-size:       11px;
            color:           var(--muted);
            margin-top:      4px;
        }

        /* ── Card action buttons ──────────────────────────────────────────── */
        .card-actions {
            display: flex;
            gap:     8px;
        }

        .btn {
            flex:            1;
            padding:         9px 0;
            border-radius:   10px;
            text-decoration: none;
            font-family:     'DM Sans', sans-serif;
            font-size:       13px;
            font-weight:     500;
            text-align:      center;
            transition:      background .2s, color .2s, border-color .2s, transform .1s;
            border:          1px solid var(--border);
            color:           var(--muted);
        }

        .btn:active { transform: scale(.97); }

        .btn-track {
            background: var(--surface-2);
            color:      var(--text);
        }

        .btn-track:hover {
            background:   var(--card-accent-color);
            border-color: var(--card-accent-color);
            color:        #000;
        }

        .btn-stats:hover {
            border-color: var(--card-accent-color);
            color:        var(--card-accent-color);
        }

        /* ── Quote footer ─────────────────────────────────────────────────── */
        .quote-bar {
            margin-top:  auto;
            padding:     1.5rem 2rem;
            text-align:  center;
            border-top:  1px solid var(--border);
            color:       var(--muted);
            font-size:   13px;
            font-style:  italic;
        }

        /* ── Animations ───────────────────────────────────────────────────── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 600px) {
            .topbar    { padding: 0 1rem; }
            .hero      { padding: 2rem 1rem 1.5rem; }
            .grid      { padding: 0 1rem 2rem; gap: 1rem; }
            .tcard     { padding: 1.25rem; }
            .topbar-date { display: none; }
        }
    </style>
</head>
<body>

    <!-- ── Top bar ──────────────────────────────────────────────────────── -->
    <nav class="topbar">
        <div class="topbar-brand">
            <span class="dot"></span>
            Productivity Hub
        </div>
        <span class="topbar-date"><?= $today ?></span>
        <a class="logout-link" href="auth/logout.php">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                <path d="M5 2H2.5A.5.5 0 002 2.5v8a.5.5 0 00.5.5H5M8.5 9.5L11 6.5 8.5 3.5M11 6.5H5"
                      stroke="currentColor" stroke-width="1.3"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Logout
        </a>
    </nav>

    <!-- ── Hero ─────────────────────────────────────────────────────────── -->
    <div class="hero">
        <p class="hero-greeting"><?= $greeting ?></p>
        <h1 class="hero-name"><?= $user_name ?></h1>

        <div class="score-ring-wrap">
            <div class="score-ring">
                <div class="score-inner">
                    <div class="score-number"><?= $totalScore ?></div>
                    <div class="score-max">/100</div>
                </div>
            </div>
            <span class="score-label-pill"><?= $score_label ?> productivity</span>
        </div>
    </div>

    <!-- ── Cards ────────────────────────────────────────────────────────── -->
    <div class="grid">

        <!-- Namaz -->
        <div class="tcard" style="
            --card-accent-color: var(--green);
            --card-glow:         var(--green-glow);
        ">
            <div class="card-top">
                <div class="card-icon">🕌</div>
                <span class="card-title">Namaz</span>
                <span class="card-percent-badge"><?= $namazPercent ?>%</span>
            </div>

            <div>
                <div class="progress-track">
                    <div class="progress-fill" style="width:<?= $namazPercent ?>%"></div>
                </div>
                <div class="progress-meta">
                    <span>Daily prayers</span>
                    <span><?= $namazPercent >= 80 ? 'On track' : ($namazPercent >= 40 ? 'Getting there' : 'Needs attention') ?></span>
                </div>
            </div>

            <div class="card-actions">
                <a class="btn btn-track" href="prayers/index.php">Track</a>
                <a class="btn btn-stats"  href="prayers/progress.php">Progress →</a>
            </div>
        </div>

        <!-- Study -->
        <div class="tcard" style="
            --card-accent-color: var(--blue);
            --card-glow:         var(--blue-glow);
        ">
            <div class="card-top">
                <div class="card-icon">📖</div>
                <span class="card-title">Study</span>
                <span class="card-percent-badge"><?= $studyPercent ?>%</span>
            </div>

            <div>
                <div class="progress-track">
                    <div class="progress-fill" style="width:<?= $studyPercent ?>%"></div>
                </div>
                <div class="progress-meta">
                    <span>Sessions completed</span>
                    <span><?= $studyPercent >= 80 ? 'On track' : ($studyPercent >= 40 ? 'Getting there' : 'Needs attention') ?></span>
                </div>
            </div>

            <div class="card-actions">
                <a class="btn btn-track" href="study/index.php">Track</a>
                <a class="btn btn-stats"  href="study/progress.php">Progress →</a>
            </div>
        </div>

        <!-- Skills -->
        <div class="tcard" style="
            --card-accent-color: var(--pink);
            --card-glow:         var(--pink-glow);
        ">
            <div class="card-top">
                <div class="card-icon">🧠</div>
                <span class="card-title">Skills</span>
                <span class="card-percent-badge"><?= $skillPercent ?>%</span>
            </div>

            <div>
                <div class="progress-track">
                    <div class="progress-fill" style="width:<?= $skillPercent ?>%"></div>
                </div>
                <div class="progress-meta">
                    <span>Tasks done</span>
                    <span><?= $skillPercent >= 80 ? 'On track' : ($skillPercent >= 40 ? 'Getting there' : 'Needs attention') ?></span>
                </div>
            </div>

            <div class="card-actions">
                <a class="btn btn-track" href="skills/index.php">Track</a>
                <a class="btn btn-stats"  href="skills/progress.php">Progress →</a>
            </div>
        </div>

    </div>

    <!-- ── Quote ─────────────────────────────────────────────────────────── -->
    <div class="quote-bar">
        "Success is the sum of small efforts, repeated day in and day out."
    </div>

</body>
</html>