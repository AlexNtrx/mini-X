<?php
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'admin.php' && strpos($_SERVER['SCRIPT_NAME'] ?? '', '/pages/') !== false) {
    header("Location: ../admin.php");
    exit;
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../functions/init.php";

// Tiukka pääsynvalvonta: Vain ylläpitäjät pääsevät tälle sivulle
if (!function_exists('isAdmin') || !isAdmin()) {
    http_response_code(403);
    setFlashToast("Pääsy evätty. Vain ylläpitäjille.", "error");
    header("Location: index.php?page=home");
    exit;
}

if (!isset($conn) || !$conn) {
    $conn = dbConnect();
}
/** @var mysqli $conn */
ensureAdminSchema($conn);

$activeTab = trim($_GET['tab'] ?? 'overview');
$stats = getAdminOverviewStats($conn);
$isMaintenance = isMaintenanceMode($conn);

// Käyttäjät-välilehden suodatukset
$userSearch = trim($_GET['user_search'] ?? '');
$userRoleFilter = trim($_GET['user_role'] ?? 'all');
$userStatusFilter = trim($_GET['user_status'] ?? 'all');

// Palautteiden suodatus
$feedbackStatusFilter = trim($_GET['fb_status'] ?? 'all');

// Ilmiantojen suodatus
$reportStatusFilter = trim($_GET['rep_status'] ?? 'pending');

$adminUsername = $_SESSION['username'] ?? '';
$adminDisplayName = !empty($_SESSION['display_name']) ? $_SESSION['display_name'] : $adminUsername;
$adminAvatarUrl = getUserAvatarUrl($_SESSION['avatar'] ?? null);
?>
<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ylläpito - Mini X</title>
    <link rel="stylesheet" href="./css/main.css?v=<?= filemtime(__DIR__ . '/../css/main.css') ?>">
    <link rel="stylesheet" href="./css/toast.css?v=<?= filemtime(__DIR__ . '/../css/toast.css') ?>">
    <link rel="stylesheet" href="./css/admin.css?v=<?= filemtime(__DIR__ . '/../css/admin.css') ?>">
</head>
<body class="admin-body">
    <!-- Erillinen Ylläpidon Yläpalkki (Admin Topbar) -->
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <a href="admin.php" class="admin-brand">
                <div class="admin-brand-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                </div>
                <div class="admin-brand-text">
                    <span class="admin-brand-title">Mini-X</span>
                    <span class="admin-brand-subtitle">Ylläpito</span>
                </div>
            </a>

            <div class="admin-topbar-center">
                <div class="admin-status-pill <?= $isMaintenance ? 'status-pill-danger' : 'status-pill-success' ?>">
                    <span class="status-dot"></span>
                    <span>Huoltotila: <?= $isMaintenance ? 'Päällä' : 'Pois päältä' ?></span>
                </div>
            </div>

            <div class="admin-topbar-actions">
                <a href="index.php?page=home" class="admin-back-btn" title="Siirry takaisin sovellukseen">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Takaisin sovellukseen</span>
                </a>

                <div class="admin-user-chip">
                    <div class="admin-user-avatar">
                        <?php if ($adminAvatarUrl): ?>
                            <img src="<?= $adminAvatarUrl ?>" alt="Avatar">
                        <?php else: ?>
                            <?= getUserInitials($adminDisplayName) ?>
                        <?php endif; ?>
                    </div>
                    <div class="admin-user-info">
                        <span class="admin-user-name"><?= htmlspecialchars($adminDisplayName) ?></span>
                        <span class="admin-role-badge">ADMIN</span>
                    </div>
                </div>

                <a href="index.php?page=logout" class="admin-logout-btn" onclick="return confirm('Haluatko varmasti kirjautua ulos?');" title="Kirjaudu ulos">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <!-- Ylläpidon osioiden navigaatiopalkki (Subnav Bar) -->
    <div class="admin-subnav-bar">
        <div class="admin-subnav-inner">
            <nav class="admin-tabs" aria-label="Ylläpidon osiot">
                <a href="admin.php?tab=overview" class="admin-tab-btn <?= $activeTab === 'overview' ? 'active' : '' ?>">
                    Yleiskatsaus
                </a>
                <a href="admin.php?tab=users" class="admin-tab-btn <?= $activeTab === 'users' ? 'active' : '' ?>">
                    Käyttäjät
                </a>
                <a href="admin.php?tab=rooms" class="admin-tab-btn <?= $activeTab === 'rooms' ? 'active' : '' ?>">
                    Huoneet (<?= $stats['total_rooms'] ?>)
                </a>
                <a href="admin.php?tab=reports" class="admin-tab-btn <?= $activeTab === 'reports' ? 'active' : '' ?>">
                    Ilmiannot
                    <?php if ($stats['pending_reports'] > 0): ?>
                        <span class="tab-badge"><?= $stats['pending_reports'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?tab=feedbacks" class="admin-tab-btn <?= $activeTab === 'feedbacks' ? 'active' : '' ?>">
                    Palautteet
                    <?php if ($stats['unread_feedback'] > 0): ?>
                        <span class="tab-badge"><?= $stats['unread_feedback'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="admin.php?tab=storage" class="admin-tab-btn <?= $activeTab === 'storage' ? 'active' : '' ?>">
                    Tallennustila
                </a>
            </nav>
        </div>
    </div>

    <!-- Työskentelytila (Main Canvas) -->
    <main class="admin-main-canvas">
        <div class="admin-container">

            <!-- 1. YLEISKATSAUS (Overview) -->
            <?php if ($activeTab === 'overview'): ?>
                <!-- Huoltotila -->
                <div class="maintenance-card" style="border-left: 4px solid <?= $isMaintenance ? '#f4212e' : '#00ba7c' ?>;">
                    <div class="maintenance-info">
                        <h3>Huoltotila: <?= $isMaintenance ? '<span style="color:#f4212e;">Päällä</span>' : '<span style="color:#00ba7c;">Pois päältä</span>' ?></h3>
                        <p>
                            <?= $isMaintenance 
                                ? 'Sivusto on suljettu tavallisilta käyttäjiltä. Vain ylläpitäjät pääsevät sisään.' 
                                : 'Sivusto on avoinna kaikille käyttäjille normaalisti.' ?>
                        </p>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="admin_action" value="toggle_maintenance">
                        <button type="submit" class="btn-admin-action <?= $isMaintenance ? 'btn-edit' : 'btn-suspend' ?>" onclick="return confirm('Haluatko varmasti vaihtaa huoltotilan tilaa?')">
                            <?= $isMaintenance ? 'Kytke pois päältä' : 'Kytke päälle' ?>
                        </button>
                    </form>
                </div>

                <!-- Tilastoruudukko -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-card-title">Käyttäjät</span>
                        <span class="stat-card-value"><?= number_format($stats['total_users']) ?></span>
                        <span class="stat-card-sub"><?= $stats['active_users'] ?> aktiivista · <?= $stats['suspended_users'] ?> jäädytettyä</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card-title">Julkaisut</span>
                        <span class="stat-card-value"><?= number_format($stats['total_posts']) ?></span>
                        <span class="stat-card-sub"><?= $stats['total_comments'] ?> kommenttia · <?= $stats['total_likes'] ?> tykkäystä</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card-title">Käsittelemättömät ilmiannot</span>
                        <span class="stat-card-value" style="color: <?= $stats['pending_reports'] > 0 ? '#f4212e' : '#e7e9ea' ?>;">
                            <?= number_format($stats['pending_reports']) ?>
                        </span>
                        <span class="stat-card-sub"><a href="admin.php?tab=reports" style="color:#1d9bf0;">Käsittele ilmiannot &rarr;</a></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-card-title">Uudet palautteet</span>
                        <span class="stat-card-value" style="color: <?= $stats['unread_feedback'] > 0 ? '#ff9800' : '#e7e9ea' ?>;">
                            <?= number_format($stats['unread_feedback']) ?>
                        </span>
                        <span class="stat-card-sub"><a href="admin.php?tab=feedbacks" style="color:#1d9bf0;">Lue palautteet &rarr;</a></span>
                    </div>
                </div>

                <!-- Pikaohjaus osioihin -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Pikatoiminnot ja osiot</h3>
                    </div>
                    <div class="quick-nav-grid">
                        <a href="admin.php?tab=users" class="quick-nav-card">
                            <span class="quick-nav-title">Käyttäjien hallinta</span>
                            <span class="quick-nav-desc">Tarkastele, muokkaa nimitietoja tai jäädytä käyttäjätilejä.</span>
                        </a>
                        <a href="admin.php?tab=rooms" class="quick-nav-card">
                            <span class="quick-nav-title">Huoneiden hallinta</span>
                            <span class="quick-nav-desc">Lisää uusia aihealueita tai säädä huoneiden näkyvyyttä.</span>
                        </a>
                        <a href="admin.php?tab=reports" class="quick-nav-card">
                            <span class="quick-nav-title">Ilmoitusten käsittely</span>
                            <span class="quick-nav-desc">Tarkista käyttäjien tekemät ilmiannot ja poista sopimaton sisältö.</span>
                        </a>
                        <a href="admin.php?tab=feedbacks" class="quick-nav-card">
                            <span class="quick-nav-title">Käyttäjäpalautteet</span>
                            <span class="quick-nav-desc">Lue ja vastaa käyttäjien lähettämiin kysymyksiin ja ideoihin.</span>
                        </a>
                    </div>
                </div>

            <!-- 2. KÄYTTÄJÄT (Users) -->
            <?php elseif ($activeTab === 'users'): ?>
                <?php $usersList = adminGetAllUsers($conn, $userSearch, $userRoleFilter, $userStatusFilter); ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Käyttäjien hallinta (<?= count($usersList) ?>)</h3>
                        <form method="GET" class="filter-bar">
                            <input type="hidden" name="tab" value="users">
                            <input type="text" name="user_search" class="admin-search-input" placeholder="Hae käyttäjää..." value="<?= htmlspecialchars($userSearch) ?>">
                            <select name="user_role" class="admin-select" onchange="this.form.submit()">
                                <option value="all" <?= $userRoleFilter === 'all' ? 'selected' : '' ?>>Kaikki roolit</option>
                                <option value="admin" <?= $userRoleFilter === 'admin' ? 'selected' : '' ?>>Ylläpitäjät</option>
                                <option value="user" <?= $userRoleFilter === 'user' ? 'selected' : '' ?>>Tavalliset</option>
                            </select>
                            <select name="user_status" class="admin-select" onchange="this.form.submit()">
                                <option value="all" <?= $userStatusFilter === 'all' ? 'selected' : '' ?>>Kaikki tilat</option>
                                <option value="active" <?= $userStatusFilter === 'active' ? 'selected' : '' ?>>Aktiiviset</option>
                                <option value="suspended" <?= $userStatusFilter === 'suspended' ? 'selected' : '' ?>>Jäädytetyt</option>
                            </select>
                            <button type="submit" class="btn-primary-action" style="padding: 7px 14px; font-size:13px;">Hae</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Käyttäjä</th>
                                    <th>Sähköposti</th>
                                    <th>Rooli</th>
                                    <th>Tila</th>
                                    <th>Julkaisut</th>
                                    <th>Liittynyt</th>
                                    <th>Toiminnot</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($usersList)): ?>
                                    <?php foreach ($usersList as $u): ?>
                                        <?php 
                                        $uAvatar = getUserAvatarUrl($u['avatar']);
                                        $isSuspended = !empty($u['deleted_at']);
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-cell">
                                                    <?php if ($uAvatar): ?>
                                                        <img src="<?= $uAvatar ?>" alt="Avatar" class="user-avatar-sm">
                                                    <?php else: ?>
                                                        <div class="user-avatar-sm"><?= getUserInitials($u['display_name']) ?></div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <strong><?= htmlspecialchars($u['display_name']) ?></strong><br>
                                                        <span style="color:#71767b;">@<?= htmlspecialchars($u['username']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="color:#8b98a5;"><?= htmlspecialchars($u['email'] ?? '-') ?></td>
                                            <td>
                                                <span class="admin-badge-pill <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                                    <?= htmlspecialchars(strtoupper($u['role'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="admin-badge-pill <?= $isSuspended ? 'badge-suspended' : 'badge-active' ?>">
                                                    <?= $isSuspended ? 'Jäädytetty' : 'Aktiivinen' ?>
                                                </span>
                                            </td>
                                            <td><?= $u['post_count'] ?> kpl</td>
                                            <td style="color:#71767b; white-space:nowrap;"><?= date('d.m.Y', strtotime($u['created_at'])) ?></td>
                                            <td>
                                                <div class="btn-action-group">
                                                    <button 
                                                        type="button" 
                                                        class="btn-admin-action btn-edit" 
                                                        title="Muokkaa käyttäjätietoja"
                                                        onclick="openEditUserModal(<?= htmlspecialchars(json_encode($u), ENT_QUOTES, 'UTF-8') ?>)">
                                                        Muokkaa
                                                    </button>

                                                    <?php if (!empty($u['avatar'])): ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="admin_action" value="reset_avatar">
                                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                            <button type="submit" class="btn-admin-action btn-suspend" title="Nollaa profiilikuva" onclick="return confirm('Haluatko varmasti poistaa käyttäjän @<?= htmlspecialchars($u['username']) ?> profiilikuvan?')">
                                                                Nollaa kuva
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ((int)$_SESSION['user_id'] !== (int)$u['id']): ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="admin_action" value="toggle_user_status">
                                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                            <button type="submit" class="btn-admin-action btn-suspend" onclick="return confirm('Haluatko varmasti muuttaa käyttäjän @<?= htmlspecialchars($u['username']) ?> tilaa?')">
                                                                <?= $isSuspended ? 'Aktivoi' : 'Jäädytä' ?>
                                                            </button>
                                                        </form>

                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="admin_action" value="delete_user_permanent">
                                                            <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>">
                                                            <button type="submit" class="btn-admin-action btn-delete" onclick="return confirm('VAROITUS: Haluatko varmasti poistaa käyttäjän @<?= htmlspecialchars($u['username']) ?> ja kaikki hänen tietonsa pysyvästi?')">
                                                                Poista
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="admin-empty-state">Käyttäjiä ei löytynyt hakuehdoilla.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <!-- 3. HUONEET (Rooms) -->
            <?php elseif ($activeTab === 'rooms'): ?>
                <?php $roomsList = adminGetAllRooms($conn); ?>
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px; align-items: flex-start;">
                    <!-- Lisää huone -lomake -->
                    <div class="admin-card">
                        <h3 class="admin-card-title" style="margin-bottom: 16px;">Lisää uusi huone</h3>
                        <form method="POST">
                            <input type="hidden" name="admin_action" value="add_room">

                            <div class="admin-form-group">
                                <label class="admin-form-label">Huoneen tunnus (Slug):</label>
                                <input type="text" name="slug" class="admin-form-input" placeholder="esim. musiikki, talous" pattern="[a-z0-9_-]+" required>
                            </div>

                            <div class="admin-form-group">
                                <label class="admin-form-label">Huoneen nimi:</label>
                                <input type="text" name="name" class="admin-form-input" placeholder="esim. Musiikki" required>
                            </div>

                            <div class="admin-form-group">
                                <label class="admin-form-label">Kuvaus:</label>
                                <textarea name="description" class="admin-form-textarea" rows="2" placeholder="Lyhyt kuvaus aiheesta..."></textarea>
                            </div>

                            <button type="submit" class="btn-primary-action" style="width: 100%;">Tallenna huone</button>
                        </form>
                    </div>

                    <!-- Huoneluettelo -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3 class="admin-card-title">Kaikki huoneet (<?= count($roomsList) ?>)</h3>
                        </div>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Nimi</th>
                                        <th>Tunnus (Slug)</th>
                                        <th>Kuvaus</th>
                                        <th>Julkaisut</th>
                                        <th>Tila</th>
                                        <th>Toiminnot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($roomsList as $room): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($room['name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($room['slug']) ?></code></td>
                                            <td style="color:#8b98a5; max-width:200px;"><?= htmlspecialchars($room['description']) ?></td>
                                            <td><?= (int)$room['post_count'] ?> kpl</td>
                                            <td>
                                                <span class="admin-badge-pill <?= !empty($room['is_active']) ? 'badge-active' : 'badge-suspended' ?>">
                                                    <?= !empty($room['is_active']) ? 'Aktiivinen' : 'Piilotettu' ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-action-group">
                                                    <?php if ($room['slug'] !== 'general'): ?>
                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="admin_action" value="toggle_room">
                                                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                            <button type="submit" class="btn-admin-action btn-edit">
                                                                <?= !empty($room['is_active']) ? 'Piilota' : 'Aktivoi' ?>
                                                            </button>
                                                        </form>

                                                        <form method="POST" style="display:inline;">
                                                            <input type="hidden" name="admin_action" value="delete_room">
                                                            <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                            <button type="submit" class="btn-admin-action btn-delete" onclick="return confirm('Haluatko varmasti poistaa huoneen \'<?= htmlspecialchars($room['name']) ?>\'? Sen julkaisut siirtyvät Yleinen-huoneeseen.')">
                                                                Poista
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span style="color:#71767b; font-size:11px;">Päähuone</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <!-- 4. ILMIANNOT (Reports Queue) -->
            <?php elseif ($activeTab === 'reports'): ?>
                <?php $reportsList = adminGetAllReports($conn, $reportStatusFilter); ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Ilmiannot ja moderointijono</h3>
                        <form method="GET" class="filter-bar">
                            <input type="hidden" name="tab" value="reports">
                            <select name="rep_status" class="admin-select" onchange="this.form.submit()">
                                <option value="pending" <?= $reportStatusFilter === 'pending' ? 'selected' : '' ?>>Käsittelemättömät</option>
                                <option value="resolved" <?= $reportStatusFilter === 'resolved' ? 'selected' : '' ?>>Poistetut sisällöt</option>
                                <option value="dismissed" <?= $reportStatusFilter === 'dismissed' ? 'selected' : '' ?>>Hylätyt ilmoitukset</option>
                                <option value="all" <?= $reportStatusFilter === 'all' ? 'selected' : '' ?>>Kaikki</option>
                            </select>
                        </form>
                    </div>

                    <?php if (!empty($reportsList)): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Aika</th>
                                        <th>Ilmoittaja</th>
                                        <th>Kohde</th>
                                        <th>Syy</th>
                                        <th>Lisätiedot</th>
                                        <th>Tila</th>
                                        <th>Toiminnot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportsList as $rep): ?>
                                        <tr>
                                            <td style="color:#71767b; white-space:nowrap;"><?= formatTimeAgo($rep['created_at']) ?></td>
                                            <td><strong>@<?= htmlspecialchars($rep['reporter_name']) ?></strong></td>
                                            <td>
                                                <?php if (!empty($rep['post_id'])): ?>
                                                    <div>
                                                        <span style="font-size:11px; color:#1d9bf0;">Julkaisu #<?= $rep['post_id'] ?> (@<?= htmlspecialchars($rep['post_author'] ?? 'tuntematon') ?>)</span><br>
                                                        <em>"<?= htmlspecialchars(mb_substr($rep['post_content'] ?? '', 0, 50)) ?>..."</em>
                                                    </div>
                                                <?php else: ?>
                                                    <span>Kommentti #<?= $rep['comment_id'] ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="admin-badge-pill badge-suspended"><?= htmlspecialchars($rep['reason']) ?></span></td>
                                            <td style="color:#8b98a5; max-width:200px;"><?= htmlspecialchars($rep['details'] ?? '-') ?></td>
                                            <td>
                                                <?php
                                                $stClass = ($rep['status'] === 'pending') ? 'badge-suspended' : (($rep['status'] === 'resolved') ? 'badge-active' : 'badge-user');
                                                ?>
                                                <span class="admin-badge-pill <?= $stClass ?>"><?= htmlspecialchars($rep['status']) ?></span>
                                            </td>
                                            <td>
                                                <?php if ($rep['status'] === 'pending'): ?>
                                                    <div class="btn-action-group">
                                                        <form method="POST">
                                                            <input type="hidden" name="admin_action" value="resolve_report">
                                                            <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                                            <input type="hidden" name="resolve_action" value="delete_content">
                                                            <button type="submit" class="btn-admin-action btn-delete" onclick="return confirm('Haluatko varmasti poistaa tämän sisällön?')">
                                                                Poista sisältö
                                                            </button>
                                                        </form>

                                                        <form method="POST">
                                                            <input type="hidden" name="admin_action" value="resolve_report">
                                                            <input type="hidden" name="report_id" value="<?= $rep['id'] ?>">
                                                            <input type="hidden" name="resolve_action" value="dismiss">
                                                            <button type="submit" class="btn-admin-action btn-edit">
                                                                Hylkää
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php else: ?>
                                                    <span style="color:#71767b; font-size:12px;">Käsitelty</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="admin-empty-state">Ei ilmoituksia valitussa tilassa.</p>
                    <?php endif; ?>
                </div>

            <!-- 5. PALAUTTEET (Feedbacks / Palaute) -->
            <?php elseif ($activeTab === 'feedbacks'): ?>
                <?php $feedbacksList = adminGetAllFeedbacks($conn, $feedbackStatusFilter); ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Käyttäjäpalautteet (<?= count($feedbacksList) ?>)</h3>
                        <form method="GET" class="filter-bar">
                            <input type="hidden" name="tab" value="feedbacks">
                            <select name="fb_status" class="admin-select" onchange="this.form.submit()">
                                <option value="all" <?= $feedbackStatusFilter === 'all' ? 'selected' : '' ?>>Kaikki palautteet</option>
                                <option value="unread" <?= $feedbackStatusFilter === 'unread' ? 'selected' : '' ?>>Uudet (Lukemattomat)</option>
                                <option value="read" <?= $feedbackStatusFilter === 'read' ? 'selected' : '' ?>>Luetut</option>
                                <option value="resolved" <?= $feedbackStatusFilter === 'resolved' ? 'selected' : '' ?>>Käsitellyt</option>
                            </select>
                        </form>
                    </div>

                    <?php if (!empty($feedbacksList)): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Aika</th>
                                        <th>Lähettäjä</th>
                                        <th>Tyyppi</th>
                                        <th>Aihe & Viesti</th>
                                        <th>Tila</th>
                                        <th>Vastaus / Toiminnot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($feedbacksList as $fb): ?>
                                        <tr>
                                            <td style="color:#71767b; white-space:nowrap;"><?= formatTimeAgo($fb['created_at']) ?></td>
                                            <td><strong>@<?= htmlspecialchars($fb['username']) ?></strong></td>
                                            <td><span class="admin-badge-pill badge-user"><?= htmlspecialchars($fb['type']) ?></span></td>
                                            <td style="max-width:320px;">
                                                <strong><?= htmlspecialchars($fb['subject']) ?></strong>
                                                <p style="margin:4px 0 0; color:#8b98a5; font-size:12px;"><?= nl2br(htmlspecialchars($fb['message'])) ?></p>
                                                <?php if (!empty($fb['admin_note'])): ?>
                                                    <div style="margin-top:6px; padding:4px 8px; background:rgba(29,155,240,0.1); border-left:2px solid #1d9bf0; font-size:11px; color:#dbe4eb;">
                                                        <strong>Vastaus:</strong> <?= htmlspecialchars($fb['admin_note']) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $fbClass = ($fb['status'] === 'unread') ? 'badge-suspended' : (($fb['status'] === 'resolved') ? 'badge-active' : 'badge-user');
                                                ?>
                                                <span class="admin-badge-pill <?= $fbClass ?>"><?= htmlspecialchars($fb['status']) ?></span>
                                            </td>
                                            <td>
                                                <form method="POST" style="margin-bottom:6px;">
                                                    <input type="hidden" name="admin_action" value="update_feedback">
                                                    <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">
                                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                                        <input type="text" name="admin_note" class="admin-form-input" style="padding:4px 8px; font-size:12px;" placeholder="Kirjoita vastaus..." value="<?= htmlspecialchars($fb['admin_note'] ?? '') ?>">
                                                        <div class="btn-action-group">
                                                            <button type="submit" name="status" value="resolved" class="btn-admin-action btn-edit" style="font-size:11px;">Käsitelty</button>
                                                            <button type="submit" name="status" value="read" class="btn-admin-action btn-suspend" style="font-size:11px;">Luettu</button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <form method="POST">
                                                    <input type="hidden" name="admin_action" value="delete_feedback">
                                                    <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">
                                                    <button type="submit" class="btn-admin-action btn-delete" style="font-size:11px;" onclick="return confirm('Haluatko varmasti poistaa tämän palautteen?')">Poista</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="admin-empty-state">Ei palautteita valituilla ehdoilla.</p>
                    <?php endif; ?>
                </div>

            <!-- 6. TALLENNUSTILA (Storage Cleanup) -->
            <?php elseif ($activeTab === 'storage'): ?>
                <?php $scanResult = adminScanOrphanFiles($conn); ?>
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3 class="admin-card-title">Tallennustila ja tiedostojen siivous</h3>
                    </div>
                    <p style="color:#8b98a5; font-size:14px; margin-bottom:20px;">
                        Palvelimen uploads-hakemistoissa on yhteensä <strong><?= $scanResult['total_files'] ?></strong> tiedostoa 
                        (Koko yhteensä: <strong><?= number_format($scanResult['total_size_bytes'] / 1024 / 1024, 2) ?> MB</strong>).<br>
                        Alla näet tiedostot, joita ei enää löydy mistään tietokannan julkaisusta tai profiilista (esim. poistettujen käyttäjien tai korvattujen kuvien jäänteet).
                    </p>

                    <?php if (!empty($scanResult['orphans'])): ?>
                        <form method="POST">
                            <input type="hidden" name="admin_action" value="clean_orphaned_files">
                            <div style="margin-bottom:12px; display:flex; justify-content:space-between; align-items:center;">
                                <span style="color:#ff7a00; font-weight:700;">Löytyi <?= count($scanResult['orphans']) ?> tarpeetonta tiedostoa</span>
                                <button type="submit" class="btn-primary-action" style="background:#f4212e;" onclick="return confirm('Haluatko varmasti poistaa kaikki valitut tiedostot palvelimelta?')">
                                    Poista valitut tiedostot
                                </button>
                            </div>
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th style="width:40px;"><input type="checkbox" id="check-all-orphans" checked onclick="toggleAllOrphans(this)"></th>
                                            <th>Kategoria</th>
                                            <th>Tiedostonimi</th>
                                            <th>Koko</th>
                                            <th>Viimeksi muokattu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($scanResult['orphans'] as $orphan): ?>
                                            <?php $relPath = $orphan['category'] . '/' . $orphan['filename']; ?>
                                            <tr>
                                                <td><input type="checkbox" name="selected_files[]" value="<?= htmlspecialchars($relPath) ?>" class="orphan-checkbox" checked></td>
                                                <td><span class="admin-badge-pill badge-user"><?= htmlspecialchars($orphan['category']) ?></span></td>
                                                <td><code><?= htmlspecialchars($orphan['filename']) ?></code></td>
                                                <td><?= number_format($orphan['size'] / 1024, 1) ?> KB</td>
                                                <td style="color:#71767b;"><?= date('d.m.Y H:i', $orphan['mtime']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="admin-empty-state" style="color:#00ba7c;">
                            Ei orpoja tiedostoja. Kaikki uploads-hakemiston tiedostot ovat aktiivisessa käytössä.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Edit User Modal -->
    <div class="admin-modal-overlay" id="editUserModal" style="display: none;" onclick="closeEditUserModal(event)">
        <div class="admin-modal-card" onclick="event.stopPropagation()">
            <div class="admin-modal-header">
                <h3 class="admin-modal-title">Muokkaa käyttäjätietoja</h3>
                <button type="button" class="admin-modal-close" onclick="closeEditUserModal()">&times;</button>
            </div>
            <form method="POST" id="editUserForm">
                <input type="hidden" name="admin_action" value="update_user">
                <input type="hidden" name="target_user_id" id="edit-user-id" value="">

                <div class="admin-form-group">
                    <label class="admin-form-label">Käyttäjänimi (3–20 merkkiä):</label>
                    <input type="text" name="username" id="edit-user-username" class="admin-form-input" required maxlength="20">
                    <div style="font-size:11px; color:#8b98a5; margin-top:4px;">
                        Pikavalinta: <a href="javascript:void(0)" onclick="sanitizeUsernameDefault()" style="color:#1d9bf0;">Aseta muodossa 'user_{ID}'</a>
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Näyttönimi / Nimimerkki:</label>
                    <input type="text" name="display_name" id="edit-user-display-name" class="admin-form-input" maxlength="25">
                </div>

                <div class="admin-form-group">
                    <label class="admin-form-label">Rooli:</label>
                    <select name="role" id="edit-user-role" class="admin-form-select">
                        <option value="user">User (Tavallinen jäsen)</option>
                        <option value="admin">Admin (Ylläpitäjä)</option>
                    </select>
                </div>

                <div class="admin-modal-actions">
                    <button type="button" class="btn-admin-action" onclick="closeEditUserModal()">Peruuta</button>
                    <button type="submit" class="btn-primary-action">Tallenna muutokset</button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/toast.php'; ?>
    <script src="./js/toast.js?v=<?= filemtime(__DIR__ . '/../js/toast.js') ?>"></script>
    <script src="./js/script.js?v=<?= filemtime(__DIR__ . '/../js/script.js') ?>"></script>
    <script>
    function openEditUserModal(user) {
        document.getElementById('edit-user-id').value = user.id;
        document.getElementById('edit-user-username').value = user.username;
        document.getElementById('edit-user-display-name').value = user.display_name || user.username;
        document.getElementById('edit-user-role').value = user.role || 'user';
        document.getElementById('editUserModal').style.display = 'flex';
    }

    function closeEditUserModal(e) {
        if (e && e.target !== e.currentTarget && !e.target.classList.contains('admin-modal-close')) {
            return;
        }
        document.getElementById('editUserModal').style.display = 'none';
    }

    function sanitizeUsernameDefault() {
        const userId = document.getElementById('edit-user-id').value;
        if (userId) {
            document.getElementById('edit-user-username').value = 'user_' + userId;
            document.getElementById('edit-user-display-name').value = 'Käyttäjä ' + userId;
        }
    }

    function toggleAllOrphans(master) {
        document.querySelectorAll('.orphan-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
    }
    </script>
</body>
</html>
