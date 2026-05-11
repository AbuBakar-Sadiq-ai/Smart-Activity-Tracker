<?php
/**
 * Skill Tracker – skills/index.php
 * Add skills and log daily practice for the logged-in user.
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

// ── State ─────────────────────────────────────────────────────────────────────
$message      = '';
$message_type = 'success';

// ── Handle: Add new skill ─────────────────────────────────────────────────────
if (isset($_POST['add_skill'])) {
    $skillName = trim($_POST['skill_name'] ?? '');

    if ($skillName === '') {
        $message      = 'Please enter a skill name.';
        $message_type = 'error';
    } elseif (mb_strlen($skillName) > 100) {
        $message      = 'Skill name is too long (max 100 characters).';
        $message_type = 'error';
    } else {
        // Check duplicate — scoped to this user only
        $chk = $conn->prepare(
            "SELECT id FROM skills WHERE user_id = ? AND skill_name = ? LIMIT 1"
        );
        $chk->bind_param('is', $user_id, $skillName);
        $chk->execute();
        $chk->store_result();
        $exists = $chk->num_rows > 0;
        $chk->close();

        if ($exists) {
            $message      = 'You already have a skill called "' . e($skillName) . '".';
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare(
                "INSERT INTO skills (user_id, skill_date, skill_name, completed)
                 VALUES (?, CURDATE(), ?, 0)"
            );
            $stmt->bind_param('is', $user_id, $skillName);
            $stmt->execute();
            $stmt->close();
            $message = 'Skill "' . e($skillName) . '" added!';
        }
    }
}

// ── Handle: Save daily progress ───────────────────────────────────────────────
if (isset($_POST['save_progress'])) {
    $date = trim($_POST['date'] ?? '');

    if (!is_valid_date($date)) {
        $message      = 'Please select a valid date.';
        $message_type = 'error';
    } else {
        // Fetch this user's skills — used as the authoritative list (never trust POST keys alone)
        $sk = $conn->prepare(
            "SELECT DISTINCT skill_name FROM skills WHERE user_id = ? ORDER BY skill_name"
        );
        $sk->bind_param('i', $user_id);
        $sk->execute();
        $userSkills = $sk->get_result()->fetch_all(MYSQLI_ASSOC);
        $sk->close();

        if (empty($userSkills)) {
            $message      = 'No skills to save.';
            $message_type = 'error';
        } else {
            $conn->begin_transaction();
            try {
                // Use INSERT ... ON DUPLICATE KEY UPDATE to collapse check+insert+update
                // into a single query per skill — requires UNIQUE(user_id, skill_date, skill_name)
                $stmt = $conn->prepare(
                    "INSERT INTO skills (user_id, skill_date, skill_name, completed)
                     VALUES (?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE completed = VALUES(completed)"
                );

                foreach ($userSkills as $s) {
                    $skillName = $s['skill_name'];
                    $completed = isset($_POST['completed'][$skillName]) ? 1 : 0;
                    $stmt->bind_param('issi', $user_id, $date, $skillName, $completed);
                    $stmt->execute();
                }

                $stmt->close();
                $conn->commit();
                $message = 'Progress saved for ' . date('M j, Y', strtotime($date)) . '!';

            } catch (Exception $ex) {
                $conn->rollback();
                $message      = 'Save failed. Please try again.';
                $message_type = 'error';
            }
        }
    }
}

// ── Fetch skills for this user ────────────────────────────────────────────────
$sk = $conn->prepare(
    "SELECT DISTINCT skill_name FROM skills WHERE user_id = ? ORDER BY skill_name"
);
$sk->bind_param('i', $user_id);
$sk->execute();
$skills = $sk->get_result()->fetch_all(MYSQLI_ASSOC);
$sk->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skill Tracker</title>

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
            --accent:      #f472b6;
            --accent-glow: rgba(244,114,182,0.15);
            --green:       #22c55e;
            --green-bg:    rgba(34,197,94,0.1);
            --red:         #ef4444;
            --red-bg:      rgba(239,68,68,0.1);
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

        /* ── Card ──────────────────────────────────────────────────────────── */
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

        /* ── Header ────────────────────────────────────────────────────────── */
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
            border:          1px solid rgba(244,114,182,0.3);
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

        .skill-count-badge {
            margin-left:   auto;
            font-size:     11px;
            color:         var(--accent);
            background:    var(--accent-glow);
            border:        1px solid rgba(244,114,182,0.2);
            padding:       2px 10px;
            border-radius: 20px;
        }

        /* ── Section label ─────────────────────────────────────────────────── */
        .section-label {
            font-size:      11px;
            font-weight:    500;
            color:          var(--muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-bottom:  .6rem;
        }

        /* ── Add skill form ─────────────────────────────────────────────────── */
        .add-row {
            display:       flex;
            gap:           8px;
            margin-bottom: 1.5rem;
        }

        input[type="text"],
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

        input[type="text"]::placeholder { color: var(--muted); }

        input[type="text"]:focus,
        input[type="date"]:focus { border-color: rgba(244,114,182,0.5); }

        /* ── Buttons ─────────────────────────────────────────────────────────── */
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

        .btn-accent {
            background: var(--accent);
            color:      #000;
        }

        .btn-accent:hover { opacity: .85; }

        .btn-save {
            width:      100%;
            margin-top: 1rem;
            background: var(--green);
            color:      #fff;
            padding:    11px;
            font-size:  14px;
        }

        .btn-save:hover { opacity: .88; }

        /* ── Divider ──────────────────────────────────────────────────────────── */
        .divider {
            border:        none;
            border-top:    1px solid var(--border);
            margin:        1.25rem 0;
        }

        /* ── Date row ────────────────────────────────────────────────────────── */
        .date-row {
            margin-bottom: 1rem;
        }

        /* ── Skills table ────────────────────────────────────────────────────── */
        .skills-table {
            width:           100%;
            border-collapse: collapse;
        }

        .skills-table tr {
            border-bottom: 1px solid var(--border);
            transition:    background .15s;
        }

        .skills-table tr:last-child { border-bottom: none; }
        .skills-table tr:hover      { background: var(--surface-2); }

        .skills-table td {
            padding: 11px 8px;
        }

        .skills-table td:first-child {
            text-align: left;
            font-size:  14px;
        }

        .skills-table td:last-child {
            text-align: right;
            width:      40px;
        }

        /* ── Custom checkbox ─────────────────────────────────────────────────── */
        .check-wrap {
            display:         flex;
            align-items:     center;
            justify-content: flex-end;
        }

        .check-wrap input[type="checkbox"] {
            appearance:    none;
            width:         20px;
            height:        20px;
            border:        2px solid rgba(255,255,255,0.15);
            border-radius: 6px;
            background:    var(--surface-2);
            cursor:        pointer;
            position:      relative;
            transition:    background .2s, border-color .2s;
        }

        .check-wrap input[type="checkbox"]:checked {
            background:   var(--accent);
            border-color: var(--accent);
        }

        .check-wrap input[type="checkbox"]:checked::after {
            content:     '';
            position:    absolute;
            top: 3px; left: 6px;
            width: 5px; height: 9px;
            border:      2px solid #000;
            border-top:  none;
            border-left: none;
            transform:   rotate(45deg);
        }

        /* ── Empty state ─────────────────────────────────────────────────────── */
        .empty-state {
            text-align:    center;
            padding:       1.5rem 0;
            color:         var(--muted);
            font-size:     14px;
        }

        .empty-state .icon {
            font-size:     2rem;
            display:       block;
            margin-bottom: .4rem;
        }

        /* ── Toast ───────────────────────────────────────────────────────────── */
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

        /* ── Footer links ────────────────────────────────────────────────────── */
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

        /* ── Responsive ──────────────────────────────────────────────────────── */
        @media (max-width: 380px) {
            .card    { padding: 1.25rem; }
            .add-row { flex-direction: column; }
            .btn-accent { width: 100%; }
        }
    </style>
</head>
<body>
<div class="card">

    <!-- Header -->
    <div class="header">
        <div class="header-icon">🧠</div>
        <h1>Skill Tracker</h1>
        <?php if (!empty($skills)): ?>
            <span class="skill-count-badge"><?= count($skills) ?> skill<?= count($skills) !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>

    <!-- Add Skill -->
    <p class="section-label">Add a skill</p>
    <form method="POST" autocomplete="off">
        <div class="add-row">
            <input
                type="text"
                name="skill_name"
                placeholder="e.g. Flutter, Quran, DSA…"
                maxlength="100"
                required
                aria-label="New skill name"
            >
            <button type="submit" name="add_skill" class="btn btn-accent">Add</button>
        </div>
    </form>

    <hr class="divider">

    <!-- Log Progress -->
    <?php if (!empty($skills)): ?>

        <p class="section-label">Log daily progress</p>

        <form method="POST" autocomplete="off">

            <div class="date-row">
                <input
                    type="date"
                    name="date"
                    required
                    max="<?= date('Y-m-d') ?>"
                    value="<?= e(date('Y-m-d')) ?>"
                    aria-label="Select date"
                >
            </div>

            <table class="skills-table">
                <?php foreach ($skills as $s): ?>
                    <tr>
                        <td><?= e($s['skill_name']) ?></td>
                        <td>
                            <div class="check-wrap">
                                <input
                                    type="checkbox"
                                    name="completed[<?= e($s['skill_name']) ?>]"
                                    aria-label="Mark <?= e($s['skill_name']) ?> as practiced"
                                >
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <button type="submit" name="save_progress" class="btn btn-save">
                Save Progress
            </button>
        </form>

    <?php else: ?>

        <div class="empty-state">
            <span class="icon">💡</span>
            No skills yet. Add your first skill above to start tracking.
        </div>

    <?php endif; ?>

    <!-- Toast -->
    <?php if ($message): ?>
        <div class="toast <?= e($message_type) ?>" role="alert">
            <?= $message ?>
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