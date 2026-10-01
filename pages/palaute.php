<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'palaute.php') {
    header("Location: ../index.php?page=palaute");
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../functions/init.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
if (!isset($conn) || !$conn) {
    $conn = dbConnect();
}
/** @var mysqli $conn */
$userId = (int)($_SESSION['user_id'] ?? 0);

// Haetaan käyttäjän aiemmin lähettämät palautteet
$myFeedbacks = [];
if ($conn && $userId > 0) {
    $stmt = $conn->prepare("SELECT id, type, subject, message, status, admin_note, created_at FROM feedbacks WHERE user_id = ? ORDER BY id DESC LIMIT 10");
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $myFeedbacks[] = $row;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anna palautetta - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=<?= filemtime(__DIR__ . '/../css/main.css') ?>">
    <link rel="stylesheet" href="./css/sidebar.css?v=<?= filemtime(__DIR__ . '/../css/sidebar.css') ?>">
    <link rel="stylesheet" href="./css/header.css?v=<?= filemtime(__DIR__ . '/../css/header.css') ?>">
    <link rel="stylesheet" href="./css/toast.css?v=<?= filemtime(__DIR__ . '/../css/toast.css') ?>">
    <style>
        .feedback-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 20px 16px 60px;
        }
        .feedback-card {
            background: #16181c;
            border: 1px solid #2f3336;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .feedback-intro {
            font-size: 15px;
            color: #8b98a5;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .feedback-form-group {
            margin-bottom: 18px;
        }
        .feedback-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #e7e9ea;
            margin-bottom: 8px;
        }
        .feedback-input, .feedback-select, .feedback-textarea {
            width: 100%;
            box-sizing: border-box;
            background: #202327;
            border: 1px solid #333639;
            border-radius: 8px;
            padding: 12px 14px;
            color: #e7e9ea;
            font-size: 14px;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s;
        }
        .feedback-input:focus, .feedback-select:focus, .feedback-textarea:focus {
            border-color: #1d9bf0;
        }
        .feedback-btn-submit {
            display: inline-block;
            background: #1d9bf0;
            color: #ffffff;
            border: none;
            border-radius: 9999px;
            padding: 10px 24px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
        }
        .feedback-btn-submit:hover {
            background: #1a8cd8;
        }
        .history-title {
            font-size: 18px;
            font-weight: 700;
            color: #e7e9ea;
            margin-bottom: 14px;
        }
        .history-item {
            background: #16181c;
            border: 1px solid #2f3336;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
        }
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        .history-subject {
            font-weight: 700;
            font-size: 15px;
            color: #e7e9ea;
        }
        .badge-status {
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 9999px;
            text-transform: uppercase;
        }
        .status-unread { background: rgba(244, 33, 46, 0.15); color: #f4212e; }
        .status-read { background: rgba(239, 243, 244, 0.15); color: #71767b; }
        .status-resolved { background: rgba(0, 186, 124, 0.15); color: #00ba7c; }
        .history-message {
            font-size: 14px;
            color: #8b98a5;
            line-height: 1.4;
        }
        .history-response {
            margin-top: 10px;
            padding: 10px 12px;
            background: rgba(29, 155, 240, 0.08);
            border-left: 3px solid #1d9bf0;
            border-radius: 4px;
            font-size: 13px;
            color: #dbe4eb;
        }
    </style>
</head>
<body>
    <div class="layout">
        <!-- Sivupalkki -->
        <?php include __DIR__ . '/../components/sidebar.php'; ?>

        <main class="feed">
            <?php
            $headerTitle = 'Anna palautetta';
            include __DIR__ . '/../components/header.php';
            ?>

            <div class="feedback-container">
                <div class="feedback-card">
                    <p class="feedback-intro">
                        Arvostamme mielipidettäsi! Voit lähettää meille kehitysehdotuksia, ilmoittaa kohtaamistasi teknisistä ongelmista tai antaa yleistä palautetta Mini-X -sovelluksesta.
                    </p>

                    <form method="POST" action="index.php">
                        <input type="hidden" name="submit_feedback" value="1">

                        <div class="feedback-form-group">
                            <label for="feedback_type" class="feedback-label">Palautteen tyyppi:</label>
                            <select name="feedback_type" id="feedback_type" class="feedback-select" required>
                                <option value="general">Yleinen palaute</option>
                                <option value="bug">Virheilmoitus / Bugin raportointi</option>
                                <option value="suggestion">Kehitysehdotus / Uusi idea</option>
                                <option value="report">Ilmoitus häiriöstä tai sopimattomasta toiminnasta</option>
                            </select>
                        </div>

                        <div class="feedback-form-group">
                            <label for="subject" class="feedback-label">Aihe / Otsikko:</label>
                            <input type="text" name="subject" id="subject" class="feedback-input" placeholder="Esim. Kuvan lataus tökkii mobiilissa" maxlength="150" required>
                        </div>

                        <div class="feedback-form-group">
                            <label for="message" class="feedback-label">Viesti / Kuvaus:</label>
                            <textarea name="message" id="message" class="feedback-textarea" rows="5" placeholder="Kuvaile palautteesi mahdollisimman tarkasti..." required></textarea>
                        </div>

                        <button type="submit" class="feedback-btn-submit">Lähetä palaute</button>
                    </form>
                </div>

                <?php if (!empty($myFeedbacks)): ?>
                    <h3 class="history-title">Aiemmin lähettämäsi palautteet</h3>
                    <?php foreach ($myFeedbacks as $fb): ?>
                        <div class="history-item">
                            <div class="history-header">
                                <span class="history-subject"><?= htmlspecialchars($fb['subject']) ?></span>
                                <?php
                                $statusClass = 'status-unread';
                                $statusLabel = 'Vastaanotettu';
                                if ($fb['status'] === 'read') {
                                    $statusClass = 'status-read';
                                    $statusLabel = 'Luettu';
                                } elseif ($fb['status'] === 'resolved') {
                                    $statusClass = 'status-resolved';
                                    $statusLabel = 'Käsitelty';
                                }
                                ?>
                                <span class="badge-status <?= $statusClass ?>"><?= $statusLabel ?></span>
                            </div>
                            <p class="history-message"><?= nl2br(htmlspecialchars($fb['message'])) ?></p>

                            <?php if (!empty($fb['admin_note'])): ?>
                                <div class="history-response">
                                    <strong>Ylläpidon vastaus / kommentti:</strong><br>
                                    <?= nl2br(htmlspecialchars($fb['admin_note'])) ?>
                                </div>
                            <?php endif; ?>
                            <small style="color: #71767b; display: block; margin-top: 6px;"><?= formatTimeAgo($fb['created_at']) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <?php include __DIR__ . '/../components/toast.php'; ?>
    <script src="./js/toast.js?v=<?= filemtime(__DIR__ . '/../js/toast.js') ?>"></script>
    <script src="./js/script.js?v=<?= filemtime(__DIR__ . '/../js/script.js') ?>"></script>
</body>
</html>
