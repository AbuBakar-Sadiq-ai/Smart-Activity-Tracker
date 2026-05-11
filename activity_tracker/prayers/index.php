<?php
/**
 * Namaz Tracker – prayers/index.php
 * Log daily prayer completion for the logged-in user.
 */

session_start();
require_once '../config/db.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Helpers ───────────────────────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function is_valid_date(string $d): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt && $dt->format('Y-m-d') === $d;
}

// ── Prayer definitions ─────────────────────────────────────────────────────────
// Qaza is tracked separately and NOT included in the 5-prayer dashboard score.
$prayer_list = [
    'fajr'    => ['label' => 'Fajr',    'time' => 'Dawn'],
    'zuhar'   => ['label' => 'Zuhar',   'time' => 'Midday'],
    'asar'    => ['label' => 'Asar',    'time' => 'Afternoon'],
    'maghrib' => ['label' => 'Maghrib', 'time' => 'Sunset'],
    'isha'    => ['label' => 'Isha',    'time' => 'Night'],
    'qaza'    => ['label' => 'Qaza',    'time' => 'Missed makeup'],
];

// ── State ─────────────────────────────────────────────────────────────────────
$message       = '';
$message_type  = 'success';
$selected_date = date('Y-m-d');

// ── Validate GET date ─────────────────────────────────────────────────────────
if (isset($_GET['date']) && is_valid_date($_GET['date'])) {
    $selected_date = $_GET['date'];
}

// ── Handle POST ───────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = trim($_POST['date'] ?? '');

    if (!is_valid_date($date)) {
        $message      = 'Invalid date submitted.';
        $message_type = 'error';
    } else {
        $selected_date = $date;

        // Read checkbox values
        $fajr    = isset($_POST['fajr'])    ? 1 : 0;
        $zuhar   = isset($_POST['zuhar'])   ? 1 : 0;
        $asar    = isset($_POST['asar'])    ? 1 : 0;
        $maghrib = isset($_POST['maghrib']) ? 1 : 0;
        $isha    = isset($_POST['isha'])    ? 1 : 0;
        $qaza    = isset($_POST['qaza'])    ? 1 : 0;

        // Check if a record already exists for this user + date
        $chk = $conn->prepare(
            "SELECT id FROM prayers WHERE user_id = ? AND prayer_date = ?"
        );
        $chk->bind_param('is', $user_id, $date);
        $chk->execute();
        $chk->store_result();
        $exists = $chk->num_rows > 0;
        $chk->close();

        if ($exists) {
            $stmt = $conn->prepare(
                "UPDATE prayers
                 SET fajr=?, zuhar=?, asar=?, maghrib=?, isha=?, qaza=?
                 WHERE user_id = ? AND prayer_date = ?"
            );
            $stmt->bind_param('iiiiiisi', $fajr, $zuhar, $asar, $maghrib, $isha, $qaza, $user_id, $date);
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO prayers (user_id, prayer_date, fajr, zuhar, asar, maghrib, isha, qaza)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param('isiiiiiii', $user_id, $date, $fajr, $zuhar, $asar, $maghrib, $isha, $qaza);
        }

        if ($stmt->execute()) {
            $done_count  = $fajr + $zuhar + $asar + $maghrib + $isha;
            $message     = $exists
                ? "Record updated — $done_count/5 prayers logged."
                : "Record saved — $done_count/5 prayers logged.";
            $message_type = 'success';
        } else {
            $message      = 'Something went wrong. Please try again.';
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// ── Fetch current status for the selected date ────────────────────────────────
$status = array_fill_keys(array_keys($prayer_list), 0);

$fetch = $conn->prepare(
    "SELECT fajr, zuhar, asar, maghrib, isha, qaza
     FROM prayers
     WHERE user_id = ? AND prayer_date = ?"
);
$fetch->bind_param('is', $user_id, $selected_date);
$fetch->execute();
$row = $fetch->get_result()->fetch_assoc();
$fetch->close();

if ($row) {
    foreach (array_keys($prayer_list) as $key) {
        $status[$key] = (int) $row[$key];
    }
}

// ── Completion count for the mini progress bar ────────────────────────────────
// Qaza excluded from the 5-prayer count (matches dashboard scoring)
$prayers_done = $status['fajr'] + $status['zuhar'] + $status['asar']
              + $status['maghrib'] + $status['isha'];
$is_today     = ($selected_date === date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Namaz Tracker</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

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
            --green-bg:    rgba(34,197,94,0.1);
            --red:         #ef4444;
            --red-bg:      rgba(239,68,68,0.1);
            --amber:       #f59e0b;
            --radius:      16px;
        }

        body {
            background:      var(--bg);
            color:           var(--text);
            font-family:     'DM Sans', sans-serif;
            min-height:      100vh;
            display:         flex;
            align-items:     flex-start;
            justify-content: center;
            padding:         2rem 1rem;
        }

        /* ── Card ─────────────────────────────────────────────────────────── */
        .card {
            background:    var(--surface);
            border:        1px solid var(--border);
            border-radius: var(--radius);
            width:         100%;
            max-width:     420px;
            padding:       2rem 1.75rem;
            animation:     fadeUp .4s ease;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(14px); }
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
            font-size:      1.15rem;
            font-weight:    700;
            letter-spacing: -.01em;
        }

        .today-badge {
            margin-left:   auto;
            font-size:     11px;
            font-weight:   500;
            color:         var(--accent);
            background:    var(--accent-glow);
            border:        1px solid rgba(34,197,94,0.25);
            padding:       3px 10px;
            border-radius: 20px;
        }

        /* ── Date picker ──────────────────────────────────────────────────── */
        .date-row {
            margin-bottom: 1.25rem;
        }

        input[type="date"] {
            width:         100%;
            padding:       9px 12px;
            background:    var(--surface-2);
            border:        1px solid var(--border);
            border-radius: 10px;
            color:         var(--text);
            font-family:   'DM Sans', sans-serif;
            font-size:     14px;
            outline:       none;
            transition:    border-color .2s;
            cursor:        pointer;
        }

        input[type="date"]:focus { border-color: rgba(34,197,94,0.5); }

        /* ── Mini score bar ───────────────────────────────────────────────── */
        .score-bar-wrap {
            margin-bottom: 1.25rem;
        }

        .score-bar-header {
            display:         flex;
            justify-content: space-between;
            font-size:       12px;
            color:           var(--muted);
            margin-bottom:   5px;
        }

        .score-bar-track {
            height:        5px;
            background:    var(--surface-2);
            border-radius: 99px;
            overflow:      hidden;
        }

        .score-bar-fill {
            height:        100%;
            border-radius: 99px;
            background:    var(--accent);
            width:         <?= ($prayers_done / 5) * 100 ?>%;
            transition:    width .6s cubic-bezier(.4,0,.2,1);
        }

        /* ── Prayer table ─────────────────────────────────────────────────── */
        .prayer-table {
            width:           100%;
            border-collapse: collapse;
            margin-bottom:   1.25rem;
        }

        .prayer-table tr {
            border-bottom: 1px solid var(--border);
            transition:    background .15s;
        }

        .prayer-table tr:last-child { border-bottom: none; }
        .prayer-table tr:hover      { background: var(--surface-2); }

        .prayer-table td { padding: 11px 8px; }

        .prayer-table td:first-child {
            text-align: left;
        }

        .prayer-table td:last-child {
            text-align: right;
            width:      40px;
        }

        /* ── Prayer name + time ───────────────────────────────────────────── */
        .prayer-name {
            font-size:   14px;
            font-weight: 500;
            display:     block;
        }

        .prayer-time {
            font-size: 11px;
            color:     var(--muted);
        }

        /* ── Qaza row separator ───────────────────────────────────────────── */
        .qaza-row td {
            padding-top: 14px;
            border-top:  1px dashed var(--border) !important;
        }

        /* ── Custom checkbox ──────────────────────────────────────────────── */
        .check-wrap {
            display:         flex;
            align-items:     center;
            justify-content: flex-end;
        }

        .check-wrap input[type="checkbox"] {
            appearance:    none;
            width:         22px;
            height:        22px;
            border:        2px solid rgba(255,255,255,0.15);
            border-radius: 6px;
            background:    var(--surface-2);
            cursor:        pointer;
            position:      relative;
            flex-shrink:   0;
            transition:    background .2s, border-color .2s;
        }

        .check-wrap input[type="checkbox"]:checked {
            background:   var(--accent);
            border-color: var(--accent);
        }

        .check-wrap input[type="checkbox"]:checked::after {
            content:     '';
            position:    absolute;
            top: 3px; left: 7px;
            width: 5px; height: 10px;
            border:      2.5px solid #000;
            border-top:  none;
            border-left: none;
            transform:   rotate(45deg);
        }

        /* Qaza checkbox uses amber accent */
        .qaza-check input[type="checkbox"]:checked {
            background:   var(--amber);
            border-color: var(--amber);
        }

        /* ── Save button ──────────────────────────────────────────────────── */
        .btn-save {
            width:         100%;
            padding:       11px;
            border:        none;
            border-radius: 10px;
            background:    var(--green);
            color:         #000;
            font-family:   'DM Sans', sans-serif;
            font-size:     14px;
            font-weight:   500;
            cursor:        pointer;
            transition:    opacity .2s, transform .1s;
        }

        .btn-save:hover  { opacity: .88; }
        .btn-save:active { transform: scale(.98); }

        /* ── Toast ────────────────────────────────────────────────────────── */
        .toast {
            margin-top:    1rem;
            padding:       10px 14px;
            border-radius: 10px;
            font-size:     13px;
            font-weight:   500;
        }

        .toast.success {
            background: var(--green-bg);
            color:      var(--green);
            border:     1px solid rgba(34,197,94,0.2);
        }

        .toast.error {
            background: var(--red-bg);
            color:      var(--red);
            border:     1px solid rgba(239,68,68,0.2);
        }

        /* ── Footer links ─────────────────────────────────────────────────── */
        .footer-links {
            display:    flex;
            gap:        1rem;
            margin-top: 1.25rem;
        }

        .footer-links a {
            color:           var(--muted);
            text-decoration: none;
            font-size:       13px;
            display:         flex;
            align-items:     center;
            gap:             5px;
            transition:      color .2s;
        }

        .footer-links a:hover { color: var(--text); }

        /* ── Responsive ───────────────────────────────────────────────────── */
        @media (max-width: 380px) {
            .card { padding: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="card">

    <!-- Header -->
    <div class="header">
        <div class="header-icon">🕌</div>
        <h1>Namaz Tracker</h1>
        <?php if ($is_today): ?>
            <span class="today-badge">Today</span>
        <?php else: ?>
            <span class="today-badge" style="color:var(--muted);background:var(--surface-2);border-color:var(--border);">
                <?= date('M j', strtotime($selected_date)) ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Date picker (GET form — auto-submits on change) -->
    <form method="GET" id="dateForm">
        <div class="date-row">
            <input
                type="date"
                name="date"
                value="<?= e($selected_date) ?>"
                max="<?= date('Y-m-d') ?>"
                onchange="this.form.submit()"
                aria-label="Select date"
            >
        </div>
    </form>

    <!-- Mini 5-prayer progress bar -->
    <div class="score-bar-wrap">
        <div class="score-bar-header">
            <span>Daily prayers</span>
            <span><?= $prayers_done ?>/5</span>
        </div>
        <div class="score-bar-track">
            <div class="score-bar-fill"></div>
        </div>
    </div>

    <!-- Prayer log form -->
    <form method="POST">
        <input type="hidden" name="date" value="<?= e($selected_date) ?>">

        <table class="prayer-table">
            <?php foreach ($prayer_list as $key => $info): ?>
                <tr<?= $key === 'qaza' ? ' class="qaza-row"' : '' ?>>
                    <td>
                        <span class="prayer-name"><?= e($info['label']) ?></span>
                        <span class="prayer-time"><?= e($info['time']) ?></span>
                    </td>
                    <td>
                        <div class="check-wrap <?= $key === 'qaza' ? 'qaza-check' : '' ?>">
                            <input
                                type="checkbox"
                                name="<?= e($key) ?>"
                                <?= $status[$key] ? 'checked' : '' ?>
                                aria-label="<?= e($info['label']) ?> prayer"
                            >
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <button type="submit" class="btn-save">Save Record</button>
    </form>

    <!-- Toast -->
    <?php if ($message): ?>
        <div class="toast <?= e($message_type) ?>" role="alert">
            <?= e($message) ?>
        </div>
    <?php endif; ?>

    <!-- Footer links -->
    <div class="footer-links">
        <a href="progress.php">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                <rect x="1" y="7" width="3" height="5" rx="1" fill="currentColor"/>
                <rect x="5" y="4" width="3" height="8" rx="1" fill="currentColor"/>
                <rect x="9" y="1" width="3" height="11" rx="1" fill="currentColor"/>
            </svg>
            View Progress
        </a>
        <a href="../dashboard.php">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                <path d="M9 10L5 6.5 9 3" stroke="currentColor" stroke-width="1.4"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Dashboard
        </a>
    </div>

</div>
</body>
</html>