<?php
/**
 * Mini-X - Admin Functions Module
 * Käsittelee ylläpidon toiminnot: käyttäjien moderointi, huoneiden hallinta,
 * ilmiantojen käsittely, palautteet, audit-lokit ja järjestelmäasetukset.
 */

/**
 * Varmistaa että kaikki ylläpidon tarvitsemat taulut ja sarakkeet ovat olemassa (Auto-Migration)
 *
 * @param mysqli $conn
 * @return bool
 */
function ensureAdminSchema($conn)
{
    static $checked = false;
    if ($checked || !$conn) {
        return true;
    }

    // 1. users.role sarake
    $roleCol = $conn->query("SHOW COLUMNS FROM `users` LIKE 'role'");
    if ($roleCol && $roleCol->num_rows === 0) {
        $conn->query("ALTER TABLE `users` ADD COLUMN `role` ENUM('user', 'admin') NOT NULL DEFAULT 'user' AFTER `password`");
    }

    // Varmistetaan että käyttäjällä 'admin' on admin-rooli
    $conn->query("UPDATE `users` SET `role` = 'admin' WHERE `username` = 'admin' AND `role` != 'admin'");

    // 2. posts.is_pinned sarake (Kiinnitetyt ilmoitukset)
    $pinCol = $conn->query("SHOW COLUMNS FROM `posts` LIKE 'is_pinned'");
    if ($pinCol && $pinCol->num_rows === 0) {
        $conn->query("ALTER TABLE `posts` ADD COLUMN `is_pinned` TINYINT(1) NOT NULL DEFAULT 0 AFTER `best_comment_id`");
    }

    // 3. rooms taulu (Dynaamiset huoneet)
    $roomsTable = $conn->query("SHOW TABLES LIKE 'rooms'");
    if ($roomsTable && $roomsTable->num_rows === 0) {
        $createRoomsSql = "CREATE TABLE `rooms` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `slug` varchar(30) NOT NULL UNIQUE,
            `name` varchar(50) NOT NULL,
            `icon` varchar(50) DEFAULT '',
            `description` varchar(255) DEFAULT '',
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $conn->query($createRoomsSql);

        // Alustetaan oletushuoneet
        $initialRooms = [
            ['general', 'Yleinen', '💬', 'Vapaa keskustelu ja sekalaiset aiheet'],
            ['tech', 'Teknologia', '💻', 'IT, ohjelmointi, laitteet ja teknologia'],
            ['food', 'Ruoka & Juoma', '🍔', 'Ruoanlaitto, reseptit ja ravintolat'],
            ['gaming', 'Pelaaminen', '🎮', 'Videopelit, konsolit ja e-urheilu'],
            ['entertainment', 'Viihde', '🎬', 'Elokuvat, sarjat, musiikki ja popkulttuuri'],
            ['lifestyle', 'Elämäntapa', '🌿', 'Hyvinvointi, arki ja matkailu']
        ];
        $insertRoom = $conn->prepare("INSERT IGNORE INTO `rooms` (`slug`, `name`, `icon`, `description`) VALUES (?, ?, ?, ?)");
        if ($insertRoom) {
            foreach ($initialRooms as $r) {
                $insertRoom->bind_param("ssss", $r[0], $r[1], $r[2], $r[3]);
                $insertRoom->execute();
            }
            $insertRoom->close();
        }
    }

    // 4. feedbacks taulu (Palautteet käyttäjiltä)
    $feedbackTable = $conn->query("SHOW TABLES LIKE 'feedbacks'");
    if ($feedbackTable && $feedbackTable->num_rows === 0) {
        $createFeedbackSql = "CREATE TABLE `feedbacks` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `type` varchar(30) NOT NULL DEFAULT 'general',
            `subject` varchar(150) NOT NULL,
            `message` text NOT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'unread',
            `admin_note` text DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $conn->query($createFeedbackSql);
    }

    // 5. reports taulu (Ilmiannot sisällöstä)
    $reportsTable = $conn->query("SHOW TABLES LIKE 'reports'");
    if ($reportsTable && $reportsTable->num_rows === 0) {
        $createReportsSql = "CREATE TABLE `reports` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `reporter_id` int(11) NOT NULL,
            `post_id` int(11) DEFAULT NULL,
            `comment_id` int(11) DEFAULT NULL,
            `reason` varchar(50) NOT NULL,
            `details` text DEFAULT NULL,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `resolved_by` int(11) DEFAULT NULL,
            `resolved_at` datetime DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `post_id` (`post_id`),
            KEY `reporter_id` (`reporter_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $conn->query($createReportsSql);
    }

    // 6. admin_logs taulu (Ylläpidon toimintaloki)
    $logsTable = $conn->query("SHOW TABLES LIKE 'admin_logs'");
    if ($logsTable && $logsTable->num_rows === 0) {
        $createLogsSql = "CREATE TABLE `admin_logs` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `admin_id` int(11) NOT NULL,
            `action` varchar(50) NOT NULL,
            `target_type` varchar(30) DEFAULT NULL,
            `target_id` int(11) DEFAULT NULL,
            `details` text DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            KEY `admin_id` (`admin_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $conn->query($createLogsSql);
    }

    // 7. system_settings taulu (Järjestelmäasetukset / Huoltotila)
    $settingsTable = $conn->query("SHOW TABLES LIKE 'system_settings'");
    if ($settingsTable && $settingsTable->num_rows === 0) {
        $createSettingsSql = "CREATE TABLE `system_settings` (
            `setting_key` varchar(50) NOT NULL,
            `setting_value` text NOT NULL,
            `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        $conn->query($createSettingsSql);

        $initSetting = $conn->prepare("INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES ('maintenance_mode', '0')");
        if ($initSetting) {
            $initSetting->execute();
            $initSetting->close();
        }
    }

    $checked = true;
    return true;
}

/**
 * Tarkistaa onko nykyinen kirjautunut käyttäjä ylläpitäjä
 *
 * @return bool
 */
function isAdmin()
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Kirjaa ylläpitäjän toimenpiteen audit-lokiin
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param string $action
 * @param string $targetType
 * @param int|null $targetId
 * @param string $details
 * @return bool
 */
function logAdminAction($conn, $adminId, $action, $targetType = null, $targetId = null, $details = '')
{
    if (!$conn || $adminId <= 0) return false;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ississ", $adminId, $action, $targetType, $targetId, $details, $ip);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

/**
 * Hakee ylläpidon yleiskatsauksen tilastot
 *
 * @param mysqli $conn
 * @return array
 */
function getAdminOverviewStats($conn)
{
    ensureAdminSchema($conn);
    $stats = [
        'total_users'     => 0,
        'active_users'    => 0,
        'suspended_users' => 0,
        'admin_users'     => 0,
        'total_posts'     => 0,
        'total_comments'  => 0,
        'total_likes'     => 0,
        'pending_reports' => 0,
        'unread_feedback' => 0,
        'total_rooms'     => 0,
    ];

    if (!$conn) return $stats;

    // Käyttäjätilastot
    $res = $conn->query("SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN deleted_at IS NULL THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN deleted_at IS NOT NULL THEN 1 ELSE 0 END) AS suspended,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) AS admins
    FROM users");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['total_users'] = (int)$row['total'];
        $stats['active_users'] = (int)$row['active'];
        $stats['suspended_users'] = (int)$row['suspended'];
        $stats['admin_users'] = (int)$row['admins'];
    }

    // Julkaisut
    $res = $conn->query("SELECT COUNT(*) AS total FROM posts");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['total_posts'] = (int)$row['total'];
    }

    // Kommentit
    $res = $conn->query("SELECT COUNT(*) AS total FROM comments");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['total_comments'] = (int)$row['total'];
    }

    // Tykkäykset
    $res = $conn->query("SELECT COUNT(*) AS total FROM likes");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['total_likes'] = (int)$row['total'];
    }

    // Odottavat ilmiannot
    $res = $conn->query("SELECT COUNT(*) AS total FROM reports WHERE status = 'pending'");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['pending_reports'] = (int)$row['total'];
    }

    // Lukemattomat palautteet
    $res = $conn->query("SELECT COUNT(*) AS total FROM feedbacks WHERE status = 'unread'");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['unread_feedback'] = (int)$row['total'];
    }

    // Huoneet
    $res = $conn->query("SELECT COUNT(*) AS total FROM rooms");
    if ($res && $row = $res->fetch_assoc()) {
        $stats['total_rooms'] = (int)$row['total'];
    }

    return $stats;
}

/**
 * Hakee odottavien hälytysten kokonaismäärän (ilmiannot + palautteet) Sidebarin badgea varten
 *
 * @param mysqli $conn
 * @return int
 */
function getAdminPendingAlertCount($conn)
{
    if (!$conn) return 0;
    ensureAdminSchema($conn);
    $total = 0;
    $res1 = $conn->query("SELECT COUNT(*) AS c FROM reports WHERE status = 'pending'");
    if ($res1 && $row1 = $res1->fetch_assoc()) {
        $total += (int)$row1['c'];
    }
    $res2 = $conn->query("SELECT COUNT(*) AS c FROM feedbacks WHERE status = 'unread'");
    if ($res2 && $row2 = $res2->fetch_assoc()) {
        $total += (int)$row2['c'];
    }
    return $total;
}

/**
 * Hakee kaikki käyttäjät hallintapaneeliin suodatuksella
 *
 * @param mysqli $conn
 * @param string $search
 * @param string $roleFilter
 * @param string $statusFilter
 * @return array
 */
function adminGetAllUsers($conn, $search = '', $roleFilter = 'all', $statusFilter = 'all')
{
    if (!$conn) return [];
    ensureAdminSchema($conn);

    $sql = "SELECT id, username, display_name, email, avatar, role, created_at, deleted_at,
                   (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) AS post_count,
                   (SELECT COUNT(*) FROM comments WHERE comments.user_id = users.id) AS comment_count
            FROM users WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($search)) {
        $sql .= " AND (username LIKE ? OR display_name LIKE ? OR email LIKE ?)";
        $term = "%" . addcslashes($search, '%_') . "%";
        $params[] = $term;
        $params[] = $term;
        $params[] = $term;
        $types .= "sss";
    }

    if ($roleFilter === 'admin' || $roleFilter === 'user') {
        $sql .= " AND role = ?";
        $params[] = $roleFilter;
        $types .= "s";
    }

    if ($statusFilter === 'active') {
        $sql .= " AND deleted_at IS NULL";
    } elseif ($statusFilter === 'suspended') {
        $sql .= " AND deleted_at IS NOT NULL";
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $users = [];
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    return $users;
}

/**
 * Ylläpitäjä muokkaa käyttäjän nimitietoja ja roolia
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $targetUserId
 * @param string $newUsername
 * @param string $newDisplayName
 * @param string $newRole
 * @return array ['success' => bool, 'message' => string]
 */
function adminUpdateUser($conn, $adminId, $targetUserId, $newUsername, $newDisplayName, $newRole)
{
    if (!$conn || $adminId <= 0 || $targetUserId <= 0) {
        return ['success' => false, 'message' => 'Virheelliset parametrit.'];
    }

    $newUsername = trim($newUsername);
    $newDisplayName = trim($newDisplayName);
    $newRole = ($newRole === 'admin') ? 'admin' : 'user';

    if (mb_strlen($newUsername) < 3 || mb_strlen($newUsername) > 20) {
        return ['success' => false, 'message' => 'Käyttäjänimen tulee olla 3–20 merkkiä.'];
    }

    if (!preg_match('/^[a-zA-Z0-9_]+$/', $newUsername)) {
        return ['success' => false, 'message' => 'Käyttäjänimi voi sisältää vain kirjaimia, numeroita ja alaviivoja.'];
    }

    // Tarkistetaan onko käyttäjänimi jo toisella
    if (isUsernameExists($conn, $newUsername, $targetUserId)) {
        return ['success' => false, 'message' => 'Käyttäjänimi on jo toisen käyttäjän käytössä.'];
    }

    if (empty($newDisplayName)) {
        $newDisplayName = $newUsername;
    }

    // Haetaan vanhat tiedot logia varten
    $oldStmt = $conn->prepare("SELECT username, display_name, role FROM users WHERE id = ? LIMIT 1");
    $oldStmt->bind_param("i", $targetUserId);
    $oldStmt->execute();
    $oldUser = $oldStmt->get_result()->fetch_assoc();
    $oldStmt->close();

    $stmt = $conn->prepare("UPDATE users SET username = ?, display_name = ?, role = ? WHERE id = ?");
    if (!$stmt) {
        return ['success' => false, 'message' => 'Tietokantavirhe.'];
    }
    $stmt->bind_param("sssi", $newUsername, $newDisplayName, $newRole, $targetUserId);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        $detail = "Päivitettiin käyttäjä #{$targetUserId}: username '{$oldUser['username']}' -> '{$newUsername}', display_name '{$oldUser['display_name']}' -> '{$newDisplayName}', role '{$oldUser['role']}' -> '{$newRole}'";
        logAdminAction($conn, $adminId, 'update_user', 'user', $targetUserId, $detail);

        // Lähetetään ilmoitus käyttäjälle nimenmuutoksesta
        if ($oldUser['username'] !== $newUsername || $oldUser['display_name'] !== $newDisplayName) {
            $notifMsg = "Ylläpitäjä on päivittänyt käyttäjätietosi (@{$newUsername}).";
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, actor_id, actor_name, post_id, type, content_preview) VALUES (?, ?, 'Ylläpito', 0, 'system', ?)");
            if ($notifStmt) {
                $notifStmt->bind_param("iis", $targetUserId, $adminId, $notifMsg);
                $notifStmt->execute();
                $notifStmt->close();
            }
        }

        return ['success' => true, 'message' => 'Käyttäjätiedot päivitetty onnistuneesti!'];
    }

    return ['success' => false, 'message' => 'Päivitys epäonnistui.'];
}

/**
 * Ylläpitäjä poistaa käyttäjän profiilikuvan (jos se on sopimaton)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $targetUserId
 * @return bool
 */
function adminResetUserAvatar($conn, $adminId, $targetUserId)
{
    if (!$conn || $adminId <= 0 || $targetUserId <= 0) return false;

    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $targetUserId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($user['avatar'])) {
        $filePath = __DIR__ . "/../uploads/avatars/" . $user['avatar'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    $upd = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
    $upd->bind_param("i", $targetUserId);
    $res = $upd->execute();
    $upd->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'reset_avatar', 'user', $targetUserId, "Poistettiin sopimaton avatar käyttäjältä #{$targetUserId}");
    }
    return $res;
}

/**
 * Ylläpitäjä asettaa käyttäjätilin tauolle (Suspend / Ban) tai aktivoi sen uudelleen
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $targetUserId
 * @return array ['success' => bool, 'message' => string, 'new_status' => string]
 */
function adminToggleUserStatus($conn, $adminId, $targetUserId)
{
    if (!$conn || $adminId <= 0 || $targetUserId <= 0) {
        return ['success' => false, 'message' => 'Virheellinen pyyntö.', 'new_status' => ''];
    }

    // Estetään oman tilin jäädyttäminen
    if ($adminId === $targetUserId) {
        return ['success' => false, 'message' => 'Et voi jäädyttää omaa ylläpitotiliäsi!', 'new_status' => ''];
    }

    $stmt = $conn->prepare("SELECT username, deleted_at FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $targetUserId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        return ['success' => false, 'message' => 'Käyttäjää ei löydy.', 'new_status' => ''];
    }

    if (empty($user['deleted_at'])) {
        // Jäädytetään
        $upd = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
        $upd->bind_param("i", $targetUserId);
        $res = $upd->execute();
        $upd->close();
        if ($res) {
            logAdminAction($conn, $adminId, 'suspend_user', 'user', $targetUserId, "Jäädytettiin tili @{$user['username']}");
            return ['success' => true, 'message' => "Käyttäjätili @{$user['username']} jäädytetty.", 'new_status' => 'suspended'];
        }
    } else {
        // Aktivoidaan uudelleen
        $upd = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
        $upd->bind_param("i", $targetUserId);
        $res = $upd->execute();
        $upd->close();
        if ($res) {
            logAdminAction($conn, $adminId, 'reactivate_user', 'user', $targetUserId, "Aktivoitiin uudelleen tili @{$user['username']}");
            return ['success' => true, 'message' => "Käyttäjätili @{$user['username']} aktivoitu uudelleen.", 'new_status' => 'active'];
        }
    }

    return ['success' => false, 'message' => 'Tilapäivitys epäonnistui.', 'new_status' => ''];
}

/**
 * Ylläpitäjä poistaa käyttäjän ja kaikki siihen liittyvät tiedot pysyvästi (Hard Delete)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $targetUserId
 * @return bool
 */
function adminDeleteUserPermanently($conn, $adminId, $targetUserId)
{
    if (!$conn || $adminId <= 0 || $targetUserId <= 0 || $adminId === $targetUserId) {
        return false;
    }

    // 1. Haetaan ja poistetaan käyttäjän kaikki kuvat (avatar, banneri, julkaisujen kuvat)
    $uStmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    $uStmt->bind_param("i", $targetUserId);
    $uStmt->execute();
    $userRow = $uStmt->get_result()->fetch_assoc();
    $uStmt->close();

    if (!empty($userRow['avatar'])) {
        @unlink(__DIR__ . "/../uploads/avatars/" . $userRow['avatar']);
    }

    // Profiilin bannerikuva
    $pStmt = $conn->prepare("SELECT theme_banner, theme_bg_val FROM profile_customizations WHERE user_id = ? LIMIT 1");
    if ($pStmt) {
        $pStmt->bind_param("i", $targetUserId);
        $pStmt->execute();
        $pRow = $pStmt->get_result()->fetch_assoc();
        $pStmt->close();
        if (!empty($pRow['theme_banner'])) {
            @unlink(__DIR__ . "/../uploads/banners/" . $pRow['theme_banner']);
        }
        if (!empty($pRow['theme_bg_val'])) {
            @unlink(__DIR__ . "/../uploads/backgrounds/" . $pRow['theme_bg_val']);
        }
    }

    // Julkaisujen kuvat
    $postImgStmt = $conn->prepare("SELECT image FROM posts WHERE user_id = ? AND image IS NOT NULL");
    if ($postImgStmt) {
        $postImgStmt->bind_param("i", $targetUserId);
        $postImgStmt->execute();
        $postImgs = $postImgStmt->get_result();
        while ($pImg = $postImgs->fetch_assoc()) {
            if (!empty($pImg['image'])) {
                @unlink(__DIR__ . "/../uploads/posts/" . $pImg['image']);
            }
        }
        $postImgStmt->close();
    }

    // 2. Poistetaan tietokannasta kaikki liittyvät rivit
    $conn->query("DELETE FROM likes WHERE user_id = {$targetUserId}");
    $conn->query("DELETE FROM comments WHERE user_id = {$targetUserId}");
    $conn->query("DELETE FROM notifications WHERE user_id = {$targetUserId} OR actor_id = {$targetUserId}");
    $conn->query("DELETE FROM feedbacks WHERE user_id = {$targetUserId}");
    $conn->query("DELETE FROM reports WHERE reporter_id = {$targetUserId}");
    $conn->query("DELETE FROM profile_customizations WHERE user_id = {$targetUserId}");
    $conn->query("DELETE FROM posts WHERE user_id = {$targetUserId}");

    // Poistetaan käyttäjä
    $delUser = $conn->prepare("DELETE FROM users WHERE id = ?");
    $delUser->bind_param("i", $targetUserId);
    $res = $delUser->execute();
    $delUser->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'hard_delete_user', 'user', $targetUserId, "Poistettiin käyttäjä #{$targetUserId} ja kaikki tiedot pysyvästi");
    }
    return $res;
}

/**
 * Ylläpitäjä poistaa minkä tahansa julkaisun
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $postId
 * @return bool
 */
function adminDeletePost($conn, $adminId, $postId)
{
    if (!$conn || $adminId <= 0 || $postId <= 0) return false;

    // Haetaan julkaisun tiedot
    $stmt = $conn->prepare("SELECT user_id, image, content FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post) return false;

    if (!empty($post['image'])) {
        $imagePath = __DIR__ . "/../uploads/posts/" . $post['image'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }

    // Poistetaan kytkökset
    $conn->query("DELETE FROM likes WHERE post_id = {$postId}");
    $conn->query("DELETE FROM comments WHERE post_id = {$postId}");
    $conn->query("DELETE FROM notifications WHERE post_id = {$postId}");
    $conn->query("DELETE FROM reports WHERE post_id = {$postId}");

    $delStmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $delStmt->bind_param("i", $postId);
    $res = $delStmt->execute();
    $delStmt->close();

    if ($res) {
        $preview = mb_substr($post['content'] ?? '', 0, 40);
        logAdminAction($conn, $adminId, 'delete_post', 'post', $postId, "Ylläpito poisti julkaisun #{$postId} (Tekijä #{$post['user_id']}): '{$preview}...'");
    }
    return $res;
}

/**
 * Ylläpitäjä kiinnittää tai poistaa julkaisun kiinnityksen (Pin / Unpin Announcement)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $postId
 * @return array ['success' => bool, 'is_pinned' => int]
 */
function adminTogglePostPin($conn, $adminId, $postId)
{
    if (!$conn || $adminId <= 0 || $postId <= 0) {
        return ['success' => false, 'is_pinned' => 0];
    }
    ensureAdminSchema($conn);

    $stmt = $conn->prepare("SELECT is_pinned FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post) return ['success' => false, 'is_pinned' => 0];

    $newPin = !empty($post['is_pinned']) ? 0 : 1;
    $upd = $conn->prepare("UPDATE posts SET is_pinned = ? WHERE id = ?");
    $upd->bind_param("ii", $newPin, $postId);
    $res = $upd->execute();
    $upd->close();

    if ($res) {
        $actionName = $newPin ? 'pin_post' : 'unpin_post';
        $detail = $newPin ? "Kiinnitettiin julkaisu #{$postId} sivun ylälaitaan" : "Poistettiin julkaisun #{$postId} kiinnitys";
        logAdminAction($conn, $adminId, $actionName, 'post', $postId, $detail);
        return ['success' => true, 'is_pinned' => $newPin];
    }

    return ['success' => false, 'is_pinned' => 0];
}

/**
 * Ylläpitäjä lisää uuden huoneen (Room)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param string $slug
 * @param string $name
 * @param string $icon
 * @param string $description
 * @return array ['success' => bool, 'message' => string]
 */
function adminAddRoom($conn, $adminId, $slug, $name, $icon, $description)
{
    if (!$conn || $adminId <= 0) {
        return ['success' => false, 'message' => 'Virheellinen pyyntö.'];
    }
    ensureAdminSchema($conn);

    $slug = strtolower(trim($slug));
    $name = trim($name);
    $icon = trim($icon);
    $description = trim($description);

    if (empty($slug) || empty($name)) {
        return ['success' => false, 'message' => 'Huoneen tunnus ja nimi ovat pakollisia.'];
    }

    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
        return ['success' => false, 'message' => 'Huoneen tunnus (slug) voi sisältää vain pieniä kirjaimia a-z, numeroita ja väliviivoja.'];
    }

    // Tarkistetaan onko jo olemassa
    $check = $conn->prepare("SELECT id FROM rooms WHERE slug = ? LIMIT 1");
    $check->bind_param("s", $slug);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $check->close();
        return ['success' => false, 'message' => "Huone tunnuksella '{$slug}' on jo olemassa."];
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO rooms (slug, name, icon, description, is_active) VALUES (?, ?, ?, ?, 1)");
    if (!$stmt) return ['success' => false, 'message' => 'Tietokantavirhe.'];
    $stmt->bind_param("ssss", $slug, $name, $icon, $description);
    $res = $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'add_room', 'room', $newId, "Lisättiin uusi huone: {$name} ({$slug})");
        return ['success' => true, 'message' => "Huone '{$name}' lisätty onnistuneesti!"];
    }

    return ['success' => false, 'message' => 'Huoneen lisääminen epäonnistui.'];
}

/**
 * Ylläpitäjä muuttaa huoneen aktiivisuustilaa (Toggle Active / Inactive)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $roomId
 * @return array
 */
function adminToggleRoomStatus($conn, $adminId, $roomId)
{
    if (!$conn || $adminId <= 0 || $roomId <= 0) return ['success' => false, 'message' => 'Virheellinen pyyntö.'];
    ensureAdminSchema($conn);

    $stmt = $conn->prepare("SELECT slug, name, is_active FROM rooms WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$room) return ['success' => false, 'message' => 'Huonetta ei löydy.'];

    // general-huonetta ei voi poistaa käytöstä
    if ($room['slug'] === 'general') {
        return ['success' => false, 'message' => 'Yleistä huonetta (general) ei voi ottaa pois käytöstä.'];
    }

    $newActive = !empty($room['is_active']) ? 0 : 1;
    $upd = $conn->prepare("UPDATE rooms SET is_active = ? WHERE id = ?");
    $upd->bind_param("ii", $newActive, $roomId);
    $res = $upd->execute();
    $upd->close();

    if ($res) {
        $stateText = $newActive ? 'aktivoitiin' : 'piilotettiin';
        logAdminAction($conn, $adminId, 'toggle_room', 'room', $roomId, "Huone '{$room['name']}' {$stateText}");
        return ['success' => true, 'message' => "Huone '{$room['name']}' on nyt " . ($newActive ? 'aktiivinen' : 'piilotettu') . "."];
    }

    return ['success' => false, 'message' => 'Päivitys epäonnistui.'];
}

/**
 * Ylläpitäjä poistaa huoneen
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $roomId
 * @return array
 */
function adminDeleteRoom($conn, $adminId, $roomId)
{
    if (!$conn || $adminId <= 0 || $roomId <= 0) return ['success' => false, 'message' => 'Virheellinen pyyntö.'];
    ensureAdminSchema($conn);

    $stmt = $conn->prepare("SELECT slug, name FROM rooms WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $roomId);
    $stmt->execute();
    $room = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$room) return ['success' => false, 'message' => 'Huonetta ei löydy.'];

    if ($room['slug'] === 'general') {
        return ['success' => false, 'message' => 'Yleistä huonetta (general) ei voi poistaa.'];
    }

    // Siirretään poistettavan huoneen julkaisut general-huoneeseen
    $updPosts = $conn->prepare("UPDATE posts SET room = 'general' WHERE room = ?");
    $updPosts->bind_param("s", $room['slug']);
    $updPosts->execute();
    $updPosts->close();

    $del = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $del->bind_param("i", $roomId);
    $res = $del->execute();
    $del->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'delete_room', 'room', $roomId, "Poistettiin huone '{$room['name']}' ({$room['slug']})");
        return ['success' => true, 'message' => "Huone '{$room['name']}' poistettu onnistuneesti!"];
    }

    return ['success' => false, 'message' => 'Huoneen poistaminen epäonnistui.'];
}

/**
 * Hakee kaikki huoneet hallintapaneeliin
 *
 * @param mysqli $conn
 * @return array
 */
function adminGetAllRooms($conn)
{
    if (!$conn) return [];
    ensureAdminSchema($conn);

    $sql = "SELECT rooms.*, 
                   (SELECT COUNT(*) FROM posts WHERE posts.room = rooms.slug) AS post_count 
            FROM rooms ORDER BY id ASC";
    $res = $conn->query($sql);
    $rooms = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $rooms[] = $row;
        }
    }
    return $rooms;
}

/**
 * Luo uuden ilmiannon sisällöstä (Käyttäjiltä)
 *
 * @param mysqli $conn
 * @param int $reporterId
 * @param int|null $postId
 * @param int|null $commentId
 * @param string $reason
 * @param string $details
 * @return bool
 */
function createReport($conn, $reporterId, $postId = null, $commentId = null, $reason = 'other', $details = '')
{
    if (!$conn || $reporterId <= 0 || (empty($postId) && empty($commentId))) {
        return false;
    }
    ensureAdminSchema($conn);

    $reason = trim($reason);
    $details = trim($details);

    $stmt = $conn->prepare("INSERT INTO reports (reporter_id, post_id, comment_id, reason, details) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("iiiss", $reporterId, $postId, $commentId, $reason, $details);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

/**
 * Hakee kaikki ilmiannot hallintapaneeliin
 *
 * @param mysqli $conn
 * @param string $statusFilter
 * @return array
 */
function adminGetAllReports($conn, $statusFilter = 'pending')
{
    if (!$conn) return [];
    ensureAdminSchema($conn);

    $sql = "SELECT r.*, 
                   u.username AS reporter_name,
                   p.content AS post_content,
                   p.image AS post_image,
                   author.username AS post_author,
                   author.id AS post_author_id
            FROM reports r
            JOIN users u ON r.reporter_id = u.id
            LEFT JOIN posts p ON r.post_id = p.id
            LEFT JOIN users author ON p.user_id = author.id";

    if ($statusFilter !== 'all') {
        $sql .= " WHERE r.status = '" . $conn->real_escape_string($statusFilter) . "'";
    }
    $sql .= " ORDER BY r.id DESC";

    $res = $conn->query($sql);
    $reports = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $reports[] = $row;
        }
    }
    return $reports;
}

/**
 * Ylläpitäjä ratkaisee ilmiannon (Hylkää tai poistaa sisällön)
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $reportId
 * @param string $action 'dismiss' | 'delete_content'
 * @return bool
 */
function adminResolveReport($conn, $adminId, $reportId, $action)
{
    if (!$conn || $adminId <= 0 || $reportId <= 0) return false;

    $stmt = $conn->prepare("SELECT post_id, comment_id FROM reports WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $reportId);
    $stmt->execute();
    $rep = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rep) return false;

    if ($action === 'delete_content') {
        if (!empty($rep['post_id'])) {
            adminDeletePost($conn, $adminId, (int)$rep['post_id']);
        }
        $newStatus = 'resolved';
    } else {
        $newStatus = 'dismissed';
    }

    $upd = $conn->prepare("UPDATE reports SET status = ?, resolved_by = ?, resolved_at = NOW() WHERE id = ?");
    $upd->bind_param("sii", $newStatus, $adminId, $reportId);
    $res = $upd->execute();
    $upd->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'resolve_report', 'report', $reportId, "Ilmianto #{$reportId} merkitty tilaksi '{$newStatus}'");
    }
    return $res;
}

/**
 * Käyttäjä lähettää palautetta (Palaute)
 *
 * @param mysqli $conn
 * @param int $userId
 * @param string $type
 * @param string $subject
 * @param string $message
 * @return bool
 */
function submitFeedback($conn, $userId, $type, $subject, $message)
{
    if (!$conn || $userId <= 0) return false;
    ensureAdminSchema($conn);

    $type = trim($type);
    $subject = trim($subject);
    $message = trim($message);

    if (empty($subject) || empty($message)) return false;

    $stmt = $conn->prepare("INSERT INTO feedbacks (user_id, type, subject, message) VALUES (?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("isss", $userId, $type, $subject, $message);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

/**
 * Hakee kaikki palautteet hallintapaneeliin
 *
 * @param mysqli $conn
 * @param string $statusFilter
 * @return array
 */
function adminGetAllFeedbacks($conn, $statusFilter = 'all')
{
    if (!$conn) return [];
    ensureAdminSchema($conn);

    $sql = "SELECT f.*, u.username, u.display_name, u.avatar 
            FROM feedbacks f 
            JOIN users u ON f.user_id = u.id";
    if ($statusFilter !== 'all') {
        $sql .= " WHERE f.status = '" . $conn->real_escape_string($statusFilter) . "'";
    }
    $sql .= " ORDER BY f.id DESC";

    $res = $conn->query($sql);
    $feedbacks = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $feedbacks[] = $row;
        }
    }
    return $feedbacks;
}

/**
 * Ylläpitäjä päivittää palautteen tilan ja muistiinpanon
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $feedbackId
 * @param string $status
 * @param string $adminNote
 * @return bool
 */
function adminUpdateFeedbackStatus($conn, $adminId, $feedbackId, $status, $adminNote = '')
{
    if (!$conn || $adminId <= 0 || $feedbackId <= 0) return false;

    $allowed = ['unread', 'read', 'resolved'];
    if (!in_array($status, $allowed)) {
        $status = 'read';
    }

    $stmt = $conn->prepare("UPDATE feedbacks SET status = ?, admin_note = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $adminNote, $feedbackId);
    $res = $stmt->execute();
    $stmt->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'update_feedback', 'feedback', $feedbackId, "Palautteen #{$feedbackId} tila: {$status}");
    }
    return $res;
}

/**
 * Ylläpitäjä poistaa palautteen
 *
 * @param mysqli $conn
 * @param int $adminId
 * @param int $feedbackId
 * @return bool
 */
function adminDeleteFeedback($conn, $adminId, $feedbackId)
{
    if (!$conn || $adminId <= 0 || $feedbackId <= 0) return false;

    $stmt = $conn->prepare("DELETE FROM feedbacks WHERE id = ?");
    $stmt->bind_param("i", $feedbackId);
    $res = $stmt->execute();
    $stmt->close();

    if ($res) {
        logAdminAction($conn, $adminId, 'delete_feedback', 'feedback', $feedbackId, "Poistettiin palaute #{$feedbackId}");
    }
    return $res;
}

/**
 * Hakee järjestelmäasetuksen arvon
 *
 * @param mysqli $conn
 * @param string $key
 * @param string $default
 * @return string
 */
function getSystemSetting($conn, $key, $default = '')
{
    if (!$conn) return $default;
    ensureAdminSchema($conn);

    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1");
    if (!$stmt) return $default;
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row['setting_value'] ?? $default;
}

/**
 * Asettaa järjestelmäasetuksen
 *
 * @param mysqli $conn
 * @param string $key
 * @param string $value
 * @return bool
 */
function setSystemSetting($conn, $key, $value)
{
    if (!$conn) return false;
    ensureAdminSchema($conn);

    $stmt = $conn->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    if (!$stmt) return false;
    $stmt->bind_param("ss", $key, $value);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

/**
 * Tarkistaa onko huoltotila päällä
 *
 * @param mysqli $conn
 * @return bool
 */
function isMaintenanceMode($conn)
{
    return getSystemSetting($conn, 'maintenance_mode', '0') === '1';
}

/**
 * Hakee ylläpidon audit-lokit
 *
 * @param mysqli $conn
 * @param int $limit
 * @return array
 */
function adminGetLogs($conn, $limit = 50)
{
    if (!$conn) return [];
    ensureAdminSchema($conn);

    $limit = (int)$limit;
    $sql = "SELECT l.*, u.username AS admin_name 
            FROM admin_logs l 
            JOIN users u ON l.admin_id = u.id 
            ORDER BY l.id DESC LIMIT {$limit}";
    $res = $conn->query($sql);
    $logs = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    return $logs;
}

/**
 * Skannaa uploads-hakemiston orvot kuvat (tiedostot joita ei löydy tietokannasta)
 *
 * @param mysqli $conn
 * @return array ['total_files' => int, 'orphans' => array, 'total_size_bytes' => int]
 */
function adminScanOrphanFiles($conn)
{
    $dirs = [
        'posts'       => __DIR__ . '/../uploads/posts/',
        'avatars'     => __DIR__ . '/../uploads/avatars/',
        'banners'     => __DIR__ . '/../uploads/banners/',
        'backgrounds' => __DIR__ . '/../uploads/backgrounds/'
    ];

    $dbImages = [
        'posts'       => [],
        'avatars'     => [],
        'banners'     => [],
        'backgrounds' => []
    ];

    if ($conn) {
        // Julkaisujen kuvat
        $pRes = $conn->query("SELECT image FROM posts WHERE image IS NOT NULL AND image != ''");
        if ($pRes) {
            while ($r = $pRes->fetch_assoc()) {
                $dbImages['posts'][$r['image']] = true;
            }
        }

        // Avatarit
        $aRes = $conn->query("SELECT avatar FROM users WHERE avatar IS NOT NULL AND avatar != ''");
        if ($aRes) {
            while ($r = $aRes->fetch_assoc()) {
                $dbImages['avatars'][$r['avatar']] = true;
            }
        }

        // Bannerit ja taustat
        $bRes = $conn->query("SELECT theme_banner, theme_bg_val FROM profile_customizations");
        if ($bRes) {
            while ($r = $bRes->fetch_assoc()) {
                if (!empty($r['theme_banner'])) $dbImages['banners'][$r['theme_banner']] = true;
                if (!empty($r['theme_bg_val'])) $dbImages['backgrounds'][$r['theme_bg_val']] = true;
            }
        }
    }

    $orphans = [];
    $totalFiles = 0;
    $totalSizeBytes = 0;

    foreach ($dirs as $category => $path) {
        if (!is_dir($path)) continue;
        $files = scandir($path);
        foreach ($files as $f) {
            if ($f === '.' || $f === '..' || $f === '.gitkeep' || $f === 'index.html') continue;
            $fullPath = $path . $f;
            if (is_file($fullPath)) {
                $totalFiles++;
                $size = filesize($fullPath);
                $totalSizeBytes += $size;

                if (!isset($dbImages[$category][$f])) {
                    $orphans[] = [
                        'category' => $category,
                        'filename' => $f,
                        'path'     => $fullPath,
                        'size'     => $size,
                        'mtime'    => filemtime($fullPath)
                    ];
                }
            }
        }
    }

    return [
        'total_files'      => $totalFiles,
        'orphans'          => $orphans,
        'total_size_bytes' => $totalSizeBytes
    ];
}
