<?php
/**
 * Namaz Progress – prayers/progress.php
 * Shows daily / weekly / monthly prayer completion for the logged-in user.
 */

session_start();
require_once '../config/db.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Validate view (whitelist) ─────────────────────────────────────────────────
$allowed_views = ['daily', 'weekly', 'monthly'];
$view = in_array($_GET['view'] ?? '', $allowed_views, true)
    ? $_GET['view']
    : 'monthly';

// ── Build query — all views use prepared statements ───────────────────────────
// Qaza is intentionally excluded from all scoring (matches dashboard logic).
switch ($view) {
    case 'daily':
        $sql = "SELECT
                    COALESCE(SUM(fajr+zuhar+asar+maghrib+isha), 0) AS done,
                    1                                               AS days
                FROM prayers
                WHERE user_id     = ?
                  AND prayer_date = CURDATE()";
        break;

    case 'weekly':
        $sql = "SELECT
                    COALESCE(SUM(fajr+zuhar+asar+maghrib+isha), 0) AS done,
                    COUNT(*)                                        AS days
                FROM prayers
                WHERE user_id = ?
                  AND YEARWEEK(prayer_date, 1) = YEARWEEK(CURDATE(), 1)";
        break;

    default: // monthly
        $sql = "SELECT
                    COALESCE(SUM(fajr+zuhar+asar+maghrib+isha), 0) AS done,
                    COUNT(*)                                        AS days
                FROM prayers
                WHERE user_id          = ?
                  AND MONTH(prayer_date) = MONTH(CURDATE())
                  AND YEAR(prayer_date)  = YEAR(CURDATE())";
        break;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Stats ─────────────────────────────────────────────────────────────────────
// Total possible = 5 prayers × number of days recorded in this period
$done    = (int) ($result['done'] ?? 0);
$days    = (int) ($result['days'] ?? 0);
$total   = $days * 5;
$missed  = max(0, $total - $done);
$percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;

// ── Period label ──────────────────────────────────────────────────────────────
$period = match($view) {
    'daily'  => 'Today',
    'weekly' => 'This Week',
    default  => date('F Y'),
};

// ── Percent color ─────────────────────────────────────────────────────────────
$badge_color = match(true) {
    $percent >= 80 => '#22c55e',
    $percent >= 50 => '#f59e0b',
    default        => '#ef4444',
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Namaz Progress – <?= htmlspecialchars($period) ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js" defer></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:          #0a0a0f;
            --surface:     #12121a;
            --surface-2:   #1c1c28;
            --border:      rgba(255,255,255,0.07);
            --text:        #e8e8f0;
            --muted:       #6e6e85;
            --accent:      #22c55e;
            --accent-glow: rgba(34,197,94,0.15);
            --green:       #22c55e;
            --amber:       #f59e0b;
            --red:         #ef4444;
            --radius:      16px;
        }

        body {
            background:      var(--bg);
            color:           var(--text);
            font-family:     'DM Sans', sans-serif;
            min-height:      100vh;
            display:         flex;
            align-items:     center;
            justify-content: center;
            padding:         1.5rem;
        }

        /* ── Card ─────────────────────────────────────────────────────────── */
        .card {
            background:    var(--surface);
            border:        1px solid var(--border);
            border-radius: var(--radius);
            width:         100%;
            max-width:     440px;
            padding:       2rem 1.75rem;
            animation:     fadeUp .45s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Header ───────────────────────────────────────────────────────── */
        .header {
            display:       flex;
            align-items:   center;
            gap:           10px;
            margin-bottom: 1.5rem;
        }

        .header-icon {
            width:           36px;
            height:          36px;
            background:      var(--accent-glow);
            border:          1px solid rgba(34,197,94,0.3);
            border-radius:   10px;
            display:         flex;
            align-items:     center;
            justify-content: center;
            font-size:       18px;
        }

        .header h1 {
            font-family:    'Syne', sans-serif;
            font-size:      1.2rem;
            font-weight:    700;
            letter-spacing: -.01em;
        }

        .period-badge {
            margin-left:   auto;
            font-size:     11px;
            font-weight:   500;
            color:         var(--accent);
            background:    var(--accent-glow);
            border:        1px solid rgba(34,197,94,0.25);
            padding:       3px 10px;
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
            flex:            1;
            text-align:      center;
            padding:         7px 0;
            border-radius:   9px;
            color:           var(--muted);
            text-decoration: none;
            font-size:       13px;
            font-weight:     500;
            transition:      color .2s, background .2s;
        }

        .tab:hover { color: var(--text); }

        .tab.active {
            background: var(--surface);
            color:      var(--text);
            box-shadow: 0 1px 4px rgba(0,0,0,0.4);
        }

        /* ── Doughnut wrapper ─────────────────────────────────────────────── */
        .donut-wrap {
            position:       relative;
            width:          180px;
            height:         180px;
            margin:         0 auto 1.5rem;
        }

        /* Percent label is drawn by Chart.js plugin — no fragile absolute overlay */

        /* ── Stat cards ───────────────────────────────────────────────────── */
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

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size:   1.4rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size:      11px;
            color:          var(--muted);
            margin-top:     4px;
            font-weight:    500;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .stat-box.done  .stat-value { color: var(--accent); }
        .stat-box.total .stat-value { color: var(--text);   }
        .stat-box.pct   .stat-value { color: <?= $badge_color ?>; }

        /* ── Progress bar ─────────────────────────────────────────────────── */
        .progress-wrap { margin-bottom: 1.5rem; }

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

        /* ── Days info ────────────────────────────────────────────────────── */
        .days-note {
            font-size:     12px;
            color:         var(--muted);
            text-align:    center;
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

        .empty-state .icon {
            font-size:     2rem;
            display:       block;
            margin-bottom: .5rem;
        }

        /* ── Back link ────────────────────────────────────────────────────── */
        .back-link {
            display:         flex;
            align-items:     center;
            gap:             6px;
            color:           var(--muted);
            text-decoration: none;
            font-size:       13px;
            transition:      color .2s;
        }

        .back-link:hover { color: var(--text); }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 400px) {
            .card         { padding: 1.25rem; }
            .stats-grid   { grid-template-columns: 1fr 1fr; }
            .stat-box.pct { grid-column: span 2; }
            .donut-wrap   { width: 150px; height: 150px; }
        }
    </style>
</head>
<body>
<div class="card">

    <!-- Header -->
    <div class="header">
        <div class="header-icon">🕌</div>
        <h1>Namaz Progress</h1>
        <span class="period-badge"><?= htmlspecialchars($period) ?></span>
    </div>

    <!-- Tabs -->
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
            <span class="icon">🕌</span>
            No prayer records found for <?= htmlspecialchars($period) ?>.
            <br>Start logging in the tracker!
        </div>

    <?php else: ?>

        <!-- Doughnut chart -->
        <div class="donut-wrap">
            <canvas id="progressChart"
                    aria-label="Prayer completion doughnut chart"
                    role="img"></canvas>
        </div>

        <!-- Stat cards -->
        <div class="stats-grid">
            <div class="stat-box done">
                <div class="stat-value"><?= $done ?></div>
                <div class="stat-label">Prayed</div>
            </div>
            <div class="stat-box total">
                <div class="stat-value"><?= $missed ?></div>
                <div class="stat-label">Missed</div>
            </div>
            <div class="stat-box pct">
                <div class="stat-value"><?= $percent ?>%</div>
                <div class="stat-label">Rate</div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="progress-wrap"
             role="progressbar"
             aria-valuenow="<?= $percent ?>"
             aria-valuemin="0"
             aria-valuemax="100"
             aria-label="Prayer completion rate <?= $percent ?>%">
            <div class="progress-header">
                <span>Completion rate</span>
                <span><?= $percent ?>%</span>
            </div>
            <div class="progress-track">
                <div class="progress-fill"></div>
            </div>
        </div>

        <!-- Days note -->
        <?php if ($view !== 'daily'): ?>
            <p class="days-note">
                Based on <?= $days ?> day<?= $days !== 1 ? 's' : '' ?> recorded
                (<?= $done ?> of <?= $total ?> possible prayers)
            </p>
        <?php endif; ?>

    <?php endif; ?>

    <!-- Back link -->
    <a class="back-link" href="index.php">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
            <path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.5"
                  stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Back to Tracker
    </a>

</div>

<?php if ($total > 0): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const done    = <?= (int) $done ?>;
    const missed  = <?= (int) $missed ?>;
    const percent = <?= (int) $percent ?>;
    const color   = '<?= $badge_color ?>';

    // Inline center-text plugin — no fragile CSS overlay
    const centerTextPlugin = {
        id: 'centerText',
        beforeDraw(chart) {
            const { ctx, chartArea: { top, width, height } } = chart;
            ctx.save();
            ctx.font         = '700 2rem "Syne", sans-serif';
            ctx.fillStyle    = color;
            ctx.textAlign    = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(percent + '%', width / 2, top + height / 2);
            ctx.restore();
        }
    };

    new Chart(document.getElementById('progressChart').getContext('2d'), {
        type: 'doughnut',
        plugins: [centerTextPlugin],
        data: {
            labels: ['Prayed', 'Missed'],
            datasets: [{
                data:            [done, missed || 1], // prevent empty chart on 100%
                backgroundColor: [color, 'rgba(255,255,255,0.05)'],
                borderWidth:     0,
                hoverOffset:     6,
            }]
        },
        options: {
            cutout:      '82%',
            responsive:  true,
            animation:   { duration: 700, easing: 'easeOutQuart' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed} prayer${ctx.parsed !== 1 ? 's' : ''}`
                    }
                }
            }
        }
    });
});
</script>
<?php endif; ?>

</body>
</html>