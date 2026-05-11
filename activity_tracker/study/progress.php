<?php
/**
 * Study Progress Dashboard
 * Displays daily / weekly / monthly study stats for the logged-in user.
 */

session_start();
require_once '../config/db.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Validate view mode (whitelist) ────────────────────────────────────────────
$allowed_views = ['daily', 'weekly', 'monthly'];
$view = in_array($_GET['view'] ?? '', $allowed_views, true)
    ? $_GET['view']
    : 'monthly';

// ── Build query using prepared statements for ALL views ───────────────────────
$params      = [$user_id];
$param_types = 'i';

switch ($view) {
    case 'daily':
        $sql = "SELECT
                    COALESCE(SUM(completed), 0) AS total_done,
                    COUNT(*)                    AS total_records
                FROM study
                WHERE user_id    = ?
                  AND study_date = CURDATE()";
        break;

    case 'weekly':
        $sql = "SELECT
                    COALESCE(SUM(completed), 0) AS total_done,
                    COUNT(*)                    AS total_records
                FROM study
                WHERE user_id = ?
                  AND YEARWEEK(study_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;

    default: // monthly
        $params[]    = (int) date('m');
        $params[]    = (int) date('Y');
        $param_types .= 'ii';

        $sql = "SELECT
                    COALESCE(SUM(completed), 0) AS total_done,
                    COUNT(*)                    AS total_records
                FROM study
                WHERE user_id          = ?
                  AND MONTH(study_date) = ?
                  AND YEAR(study_date)  = ?";
        break;
}

// ── Execute ───────────────────────────────────────────────────────────────────
$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Compute stats ─────────────────────────────────────────────────────────────
$done    = (int) ($data['total_done']    ?? 0);
$total   = (int) ($data['total_records'] ?? 0);
$missed  = $total - $done;
$percent = $total > 0 ? round(($done / $total) * 100) : 0;

// ── Period label ──────────────────────────────────────────────────────────────
$period_labels = [
    'daily'   => 'Today',
    'weekly'  => 'This Week',
    'monthly' => date('F Y'),
];
$period = $period_labels[$view];

// ── Color for percent badge ───────────────────────────────────────────────────
$badge_color = match(true) {
    $percent >= 75 => '#22c55e',   // green
    $percent >= 40 => '#f59e0b',   // amber
    default        => '#ef4444',   // red
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Progress – <?= htmlspecialchars($period) ?></title>

    <!-- Google Fonts: display + body pairing -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>

    <style>
        /* ── Reset & base ─────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #0a0a0f;
            --surface:     #12121a;
            --surface-2:   #1c1c28;
            --border:      rgba(255,255,255,0.07);
            --text:        #e8e8f0;
            --muted:       #6e6e85;
            --accent:      #7c6dfa;
            --accent-glow: rgba(124,109,250,0.2);
            --green:       #22c55e;
            --amber:       #f59e0b;
            --red:         #ef4444;
            --radius:      16px;
        }

        body {
            background: var(--bg);
            color:       var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height:  100vh;
            display:     flex;
            align-items: center;
            justify-content: center;
            padding:     1.5rem;
        }

        /* ── Card ─────────────────────────────────────────────────────────── */
        .card {
            background:    var(--surface);
            border:        1px solid var(--border);
            border-radius: var(--radius);
            width:         100%;
            max-width:     480px;
            padding:       2rem 1.75rem;
            animation:     fadeUp .45s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Header ───────────────────────────────────────────────────────── */
        .header {
            display:     flex;
            align-items: center;
            gap:         10px;
            margin-bottom: 1.5rem;
        }

        .header-icon {
            width:  36px;
            height: 36px;
            background:    var(--accent-glow);
            border:        1px solid rgba(124,109,250,0.3);
            border-radius: 10px;
            display:       flex;
            align-items:   center;
            justify-content: center;
            font-size:     18px;
        }

        .header h1 {
            font-family: 'Syne', sans-serif;
            font-size:   1.2rem;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .period-badge {
            margin-left:  auto;
            font-size:    11px;
            font-weight:  500;
            color:        var(--accent);
            background:   var(--accent-glow);
            border:       1px solid rgba(124,109,250,0.25);
            padding:      3px 10px;
            border-radius: 20px;
        }

        /* ── Tabs ─────────────────────────────────────────────────────────── */
        .tabs {
            display:       flex;
            gap:           6px;
            margin-bottom: 1.5rem;
            background:    var(--surface-2);
            padding:       4px;
            border-radius: 12px;
        }

        .tab {
            flex:           1;
            text-align:     center;
            padding:        7px 0;
            border-radius:  9px;
            color:          var(--muted);
            text-decoration: none;
            font-size:      13px;
            font-weight:    500;
            transition:     color .2s, background .2s;
        }

        .tab:hover { color: var(--text); }

        .tab.active {
            background: var(--surface);
            color:      var(--text);
            box-shadow: 0 1px 4px rgba(0,0,0,0.4);
        }

        /* ── Stats row ────────────────────────────────────────────────────── */
        .stats-grid {
            display:               grid;
            grid-template-columns: repeat(3, 1fr);
            gap:                   10px;
            margin-bottom:         1.5rem;
        }

        .stat-box {
            background:    var(--surface-2);
            border:        1px solid var(--border);
            border-radius: 12px;
            padding:       .75rem;
            text-align:    center;
        }

        .stat-box .stat-value {
            font-family: 'Syne', sans-serif;
            font-size:   1.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-box .stat-label {
            font-size:   11px;
            color:       var(--muted);
            margin-top:  4px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-box.done   .stat-value { color: var(--green); }
        .stat-box.missed .stat-value { color: var(--red);   }
        .stat-box.pct    .stat-value { color: <?= $badge_color ?>; }

        /* ── Progress bar ─────────────────────────────────────────────────── */
        .progress-wrap {
            margin-bottom: 1.5rem;
        }

        .progress-header {
            display:         flex;
            justify-content: space-between;
            font-size:       12px;
            color:           var(--muted);
            margin-bottom:   6px;
        }

        .progress-track {
            height:        6px;
            background:    var(--surface-2);
            border-radius: 99px;
            overflow:      hidden;
        }

        .progress-fill {
            height:        100%;
            border-radius: 99px;
            background:    <?= $badge_color ?>;
            width:         <?= $percent ?>%;
            transition:    width .6s cubic-bezier(.4,0,.2,1);
        }

        /* ── Chart ────────────────────────────────────────────────────────── */
        .chart-wrap {
            position:   relative;
            height:     180px;
            margin-bottom: 1.5rem;
        }

        /* ── Empty state ──────────────────────────────────────────────────── */
        .empty-state {
            text-align:    center;
            padding:       2rem 0;
            color:         var(--muted);
            font-size:     14px;
            margin-bottom: 1.5rem;
        }

        .empty-state .empty-icon { font-size: 2rem; display: block; margin-bottom: .5rem; }

        /* ── Back link ────────────────────────────────────────────────────── */
        .back-link {
            display:     flex;
            align-items: center;
            gap:         6px;
            color:       var(--muted);
            text-decoration: none;
            font-size:   13px;
            transition:  color .2s;
        }

        .back-link:hover { color: var(--text); }

        .back-link svg { flex-shrink: 0; }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 400px) {
            .card { padding: 1.25rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
            .stat-box.pct { grid-column: span 2; }
        }
    </style>
</head>

<body>
<div class="card">

    <!-- Header -->
    <div class="header">
        <div class="header-icon">📚</div>
        <h1>Study Progress</h1>
        <span class="period-badge"><?= htmlspecialchars($period) ?></span>
    </div>

    <!-- View Tabs -->
    <div class="tabs" role="tablist" aria-label="View period">
        <?php foreach ($allowed_views as $v): ?>
            <a  class="tab<?= $v === $view ? ' active' : '' ?>"
                href="?view=<?= $v ?>"
                role="tab"
                aria-selected="<?= $v === $view ? 'true' : 'false' ?>">
                <?= ucfirst($v) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($total === 0): ?>

        <!-- Empty state -->
        <div class="empty-state">
            <span class="empty-icon">🗂️</span>
            No study records found for <?= htmlspecialchars($period) ?>.
        </div>

    <?php else: ?>

        <!-- Stats grid -->
        <div class="stats-grid">
            <div class="stat-box done">
                <div class="stat-value"><?= $done ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-box missed">
                <div class="stat-value"><?= $missed ?></div>
                <div class="stat-label">Missed</div>
            </div>
            <div class="stat-box pct">
                <div class="stat-value"><?= $percent ?>%</div>
                <div class="stat-label">Rate</div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="progress-wrap" role="progressbar"
             aria-valuenow="<?= $percent ?>"
             aria-valuemin="0" aria-valuemax="100"
             aria-label="Completion rate <?= $percent ?>%">
            <div class="progress-header">
                <span>Completion rate</span>
                <span><?= $percent ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>
        </div>

        <!-- Bar chart -->
        <div class="chart-wrap">
            <canvas id="barChart" aria-label="Study progress bar chart"></canvas>
        </div>

    <?php endif; ?>

    <!-- Back link -->
    <a class="back-link" href="index.php">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.5"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back to Study
    </a>

</div>

<?php if ($total > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const done   = <?= (int) $done ?>;
    const missed = <?= (int) $missed ?>;

    const ctx = document.getElementById('barChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Completed', 'Missed'],
            datasets: [{
                data: [done, missed],
                backgroundColor: ['rgba(34,197,94,0.85)', 'rgba(239,68,68,0.85)'],
                hoverBackgroundColor: ['#22c55e', '#ef4444'],
                borderRadius: 8,
                barThickness: 56,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 600, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} session${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                x: {
                    ticks: { color: '#6e6e85', font: { family: 'DM Sans', size: 12 } },
                    grid:  { display: false },
                    border: { display: false }
                },
                y: {
                    ticks: {
                        color: '#6e6e85',
                        font:  { family: 'DM Sans', size: 12 },
                        stepSize: 1,
                        precision: 0
                    },
                    grid:  { color: 'rgba(255,255,255,0.05)' },
                    border: { display: false },
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
<?php endif; ?>
</body>
</html>