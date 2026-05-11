<?php
/**
 * Smart Study Tracker – index.php
 * Logs daily subject attendance for the logged-in user.
 */

session_start();
require_once '../config/db.php';

// ── Auth guard ────────────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = (int) $_SESSION['user_id'];

// ── Timetable definition ──────────────────────────────────────────────────────
// Duplicates removed (Thursday had "Database Systems (Lab)" twice)
$timetable = [
    'Monday'    => ['Database Systems', 'Software Engineering', 'Design and Analysis of Algorithms'],
    'Tuesday'   => ['Artificial Intelligence (Lab)', 'Design and Analysis of Algorithms', 'Software Engineering'],
    'Wednesday' => ['Database Systems', 'Artificial Intelligence (Lab)', 'Artificial Intelligence'],
    'Thursday'  => ['Database Systems (Lab)', 'Assembly Language (Lab)', 'Assembly Language'],
    'Friday'    => ['Computer Organization and Assembly Language (Lab)', 'Probability and Statistics'],
    'Saturday'  => [],
    'Sunday'    => [],
];

// ── State ─────────────────────────────────────────────────────────────────────
$message        = '';
$message_type   = 'success'; // or 'error'
$subjectsToday  = [];
$dayName        = '';
$selected_date  = '';
$already_saved  = false;

// ── Helper: validate a date string ───────────────────────────────────────────
function is_valid_date(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// ── Handle: show day subjects ─────────────────────────────────────────────────
if (isset($_POST['get_day'])) {
    $date = trim($_POST['date'] ?? '');

    if (!is_valid_date($date)) {
        $message      = 'Please select a valid date.';
        $message_type = 'error';
    } else {
        $selected_date = $date;
        $dayName       = date('l', strtotime($date));
        $subjectsToday = $timetable[$dayName] ?? [];

        // Check if already saved for this date
        $chk = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM study
             WHERE user_id = ? AND study_date = ?"
        );
        $chk->bind_param('is', $user_id, $selected_date);
        $chk->execute();
        $already_saved = (bool) $chk->get_result()->fetch_assoc()['cnt'];
        $chk->close();
    }
}

// ── Handle: save study ────────────────────────────────────────────────────────
if (isset($_POST['save_study'])) {
    $date = trim($_POST['date'] ?? '');

    if (!is_valid_date($date)) {
        $message      = 'Invalid date submitted.';
        $message_type = 'error';
    } else {
        $selected_date = $date;
        $dayName       = date('l', strtotime($date));
        $subjectsToday = $timetable[$dayName] ?? [];

        // Guard against duplicate saves for the same user+date
        $chk = $conn->prepare(
            "SELECT COUNT(*) AS cnt FROM study
             WHERE user_id = ? AND study_date = ?"
        );
        $chk->bind_param('is', $user_id, $selected_date);
        $chk->execute();
        $already_saved = (bool) $chk->get_result()->fetch_assoc()['cnt'];
        $chk->close();

        if ($already_saved) {
            $message      = "Study for $dayName has already been saved.";
            $message_type = 'error';
        } elseif (empty($subjectsToday)) {
            $message      = 'No subjects to save for this day.';
            $message_type = 'error';
        } else {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare(
                    "INSERT INTO study (user_id, study_date, subject_name, completed)
                     VALUES (?, ?, ?, ?)"
                );
                foreach ($subjectsToday as $subject) {
                    $completed = isset($_POST['completed'][$subject]) ? 1 : 0;
                    $stmt->bind_param('issi', $user_id, $selected_date, $subject, $completed);
                    $stmt->execute();
                }
                $stmt->close();
                $conn->commit();

                $already_saved = true;
                $message       = "Study log saved for $dayName!";
                $message_type  = 'success';

            } catch (Exception $e) {
                $conn->rollback();
                $message      = 'Something went wrong. Please try again.';
                $message_type = 'error';
            }
        }
    }
}

// ── Safe output helper ────────────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Study Tracker</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <style>
        /* ── Reset & base ─────────────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:         #0a0a0f;
            --surface:    #12121a;
            --surface-2:  #1c1c28;
            --border:     rgba(255,255,255,0.07);
            --text:       #e8e8f0;
            --muted:      #6e6e85;
            --accent:     #7c6dfa;
            --accent-glow: rgba(124,109,250,0.18);
            --green:      #22c55e;
            --green-bg:   rgba(34,197,94,0.1);
            --red:        #ef4444;
            --red-bg:     rgba(239,68,68,0.1);
            --radius:     16px;
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
            max-width:     460px;
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
            width:         36px;
            height:        36px;
            background:    var(--accent-glow);
            border:        1px solid rgba(124,109,250,0.3);
            border-radius: 10px;
            display:       flex;
            align-items:   center;
            justify-content: center;
            font-size:     18px;
        }

        .header h1 {
            font-family:    'Syne', sans-serif;
            font-size:      1.15rem;
            font-weight:    700;
            letter-spacing: -.01em;
        }

        /* ── Date form ────────────────────────────────────────────────────── */
        .date-row {
            display: flex;
            gap:     8px;
            margin-bottom: 1.25rem;
        }

        input[type="date"] {
            flex:          1;
            padding:       9px 12px;
            background:    var(--surface-2);
            border:        1px solid var(--border);
            border-radius: 10px;
            color:         var(--text);
            font-family:   'DM Sans', sans-serif;
            font-size:     14px;
            outline:       none;
            transition:    border-color .2s;
        }

        input[type="date"]:focus {
            border-color: rgba(124,109,250,0.5);
        }

        /* ── Buttons ──────────────────────────────────────────────────────── */
        .btn {
            padding:       9px 16px;
            border:        none;
            border-radius: 10px;
            font-family:   'DM Sans', sans-serif;
            font-size:     13px;
            font-weight:   500;
            cursor:        pointer;
            transition:    opacity .2s, transform .1s;
            white-space:   nowrap;
        }

        .btn:active { transform: scale(.97); }

        .btn-primary {
            background: var(--accent);
            color:      #fff;
        }

        .btn-primary:hover { opacity: .88; }

        .btn-save {
            width:      100%;
            margin-top: 1rem;
            background: var(--green);
            color:      #fff;
            padding:    11px;
            font-size:  14px;
        }

        .btn-save:hover   { opacity: .88; }
        .btn-save:disabled {
            background: var(--surface-2);
            color:      var(--muted);
            cursor:     not-allowed;
            transform:  none;
        }

        /* ── Day heading ──────────────────────────────────────────────────── */
        .day-heading {
            display:       flex;
            align-items:   center;
            gap:           8px;
            margin-bottom: .75rem;
        }

        .day-heading h2 {
            font-family: 'Syne', sans-serif;
            font-size:   1rem;
            font-weight: 700;
        }

        .subject-count {
            font-size:   11px;
            color:       var(--accent);
            background:  var(--accent-glow);
            border:      1px solid rgba(124,109,250,0.2);
            padding:     2px 8px;
            border-radius: 20px;
        }

        /* ── Subject table ────────────────────────────────────────────────── */
        .subject-table {
            width:           100%;
            border-collapse: collapse;
            margin-bottom:   .25rem;
        }

        .subject-table tr {
            border-bottom: 1px solid var(--border);
            transition:    background .15s;
        }

        .subject-table tr:last-child { border-bottom: none; }

        .subject-table tr:hover { background: var(--surface-2); }

        .subject-table td {
            padding: 11px 8px;
        }

        .subject-table td:first-child {
            text-align: left;
            font-size:  14px;
            color:      var(--text);
        }

        .subject-table td:last-child {
            text-align: right;
            width:      40px;
        }

        /* ── Custom checkbox ──────────────────────────────────────────────── */
        .check-wrap {
            display:     flex;
            align-items: center;
            justify-content: flex-end;
        }

        .check-wrap input[type="checkbox"] {
            appearance: none;
            width:      20px;
            height:     20px;
            border:     2px solid rgba(255,255,255,0.15);
            border-radius: 6px;
            background: var(--surface-2);
            cursor:     pointer;
            position:   relative;
            transition: background .2s, border-color .2s;
        }

        .check-wrap input[type="checkbox"]:checked {
            background:   var(--green);
            border-color: var(--green);
        }

        .check-wrap input[type="checkbox"]:checked::after {
            content:  '';
            position: absolute;
            top: 3px; left: 6px;
            width: 5px; height: 9px;
            border: 2px solid #fff;
            border-top:  none;
            border-left: none;
            transform:   rotate(45deg);
        }

        .check-wrap input[type="checkbox"]:disabled {
            opacity: .5;
            cursor:  not-allowed;
        }

        /* ── Already-saved row tint ───────────────────────────────────────── */
        tr.was-done td:first-child { color: var(--green); }
        tr.was-missed td:first-child { color: var(--muted); text-decoration: line-through; }

        /* ── Off-day / no class panel ─────────────────────────────────────── */
        .no-class {
            text-align:    center;
            padding:       1.5rem 0;
            color:         var(--muted);
            font-size:     14px;
        }

        .no-class .emoji { font-size: 2rem; display: block; margin-bottom: .4rem; }

        /* ── Saved badge ──────────────────────────────────────────────────── */
        .saved-badge {
            display:       flex;
            align-items:   center;
            gap:           6px;
            font-size:     12px;
            color:         var(--green);
            background:    var(--green-bg);
            border:        1px solid rgba(34,197,94,0.2);
            border-radius: 8px;
            padding:       6px 10px;
            margin-bottom: .75rem;
        }

        /* ── Toast message ────────────────────────────────────────────────── */
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

        /* ── Footer ───────────────────────────────────────────────────────── */
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
            .date-row { flex-direction: column; }
            .btn-primary { width: 100%; }
        }
    </style>
</head>

<body>
<div class="card">

    <!-- Header -->
    <div class="header">
        <div class="header-icon">📚</div>
        <h1>Smart Study Tracker</h1>
    </div>

    <!-- Date select form -->
    <form method="POST" autocomplete="off">
        <div class="date-row">
            <input
                type="date"
                name="date"
                required
                max="<?= date('Y-m-d') ?>"
                value="<?= e($selected_date ?: date('Y-m-d')) ?>"
                aria-label="Select date"
            >
            <button type="submit" name="get_day" class="btn btn-primary">
                Show Classes
            </button>
        </div>
    </form>

    <?php if ($dayName): ?>

        <!-- Day heading -->
        <div class="day-heading">
            <h2><?= e($dayName) ?></h2>
            <?php if (!empty($subjectsToday)): ?>
                <span class="subject-count"><?= count($subjectsToday) ?> subjects</span>
            <?php endif; ?>
        </div>

        <?php if (empty($subjectsToday)): ?>

            <!-- Weekend / no class -->
            <div class="no-class">
                <span class="emoji">🎉</span>
                No classes on <?= e($dayName) ?>. Enjoy your break!
            </div>

        <?php else: ?>

            <?php if ($already_saved): ?>
                <!-- Already-saved notice -->
                <div class="saved-badge">
                    <svg width="13" height="13" viewBox="0 0 13 13" fill="none">
                        <circle cx="6.5" cy="6.5" r="6" stroke="#22c55e" stroke-width="1.2"/>
                        <path d="M4 6.5l2 2 3-3" stroke="#22c55e" stroke-width="1.3"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Already logged for this date
                </div>
            <?php endif; ?>

            <!-- Subject form -->
            <form method="POST">
                <input type="hidden" name="date" value="<?= e($selected_date) ?>">

                <table class="subject-table">
                    <?php foreach ($subjectsToday as $subject): ?>
                        <tr>
                            <td><?= e($subject) ?></td>
                            <td>
                                <div class="check-wrap">
                                    <input
                                        type="checkbox"
                                        name="completed[<?= e($subject) ?>]"
                                        <?= $already_saved ? 'disabled' : '' ?>
                                        aria-label="Mark <?= e($subject) ?> as completed"
                                    >
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <button
                    type="submit"
                    name="save_study"
                    class="btn btn-save"
                    <?= $already_saved ? 'disabled' : '' ?>
                >
                    <?= $already_saved ? 'Already Saved' : 'Save Study Log' ?>
                </button>
            </form>

        <?php endif; ?>

    <?php endif; ?>

    <!-- Toast message -->
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