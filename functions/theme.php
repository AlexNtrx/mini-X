<?php
/**
 * Mini-X - Hi5 Profile Theme & Music Functions
 * Hallinnoi käyttäjän profiilin kustomointia (teema, banneri, värit, kursoriefektit ja taustamusiikki).
 */

/**
 * Varmistaa että profile_customizations -taulu on olemassa tietokannassa.
 *
 * @param mysqli $conn
 * @return bool
 */
function ensureCustomizationsTable($conn)
{
    static $checked = false;
    if ($checked || !$conn) {
        return true;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `profile_customizations` (
        `user_id` INT NOT NULL,
        `bio` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `song_title` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `song_artist` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `song_artwork` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `song_url` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `theme_accent` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '#1d9bf0',
        `theme_banner` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `theme_bg_type` VARCHAR(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'default',
        `theme_bg_val` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
        `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`user_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $result = $conn->query($sql);

    // Varmistetaan että song_artwork-sarake löytyy jos taulu oli jo luotu
    $colCheck = $conn->query("SHOW COLUMNS FROM `profile_customizations` LIKE 'song_artwork'");
    if ($colCheck && $colCheck->num_rows === 0) {
        $conn->query("ALTER TABLE `profile_customizations` ADD COLUMN `song_artwork` VARCHAR(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '' AFTER `song_artist`");
    }

    // Varmistetaan että banner_pos_y-sarake löytyy (pystysijainti 0-100%)
    $colCheck2 = $conn->query("SHOW COLUMNS FROM `profile_customizations` LIKE 'banner_pos_y'");
    if ($colCheck2 && $colCheck2->num_rows === 0) {
        $conn->query("ALTER TABLE `profile_customizations` ADD COLUMN `banner_pos_y` INT NOT NULL DEFAULT 50 AFTER `theme_banner`");
    }

    // Poistetaan tarpeeton theme_sparkles sarake jos vielä olemassa
    $colCheck3 = $conn->query("SHOW COLUMNS FROM `profile_customizations` LIKE 'theme_sparkles'");
    if ($colCheck3 && $colCheck3->num_rows > 0) {
        $conn->query("ALTER TABLE `profile_customizations` DROP COLUMN `theme_sparkles`");
    }

    $checked = true;
    return (bool)$result;
}

/**
 * Palauttaa käyttäjän kustomointiasetukset. Jos asetuksia ei löydy, palauttaa oletusarvot.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return array
 */
function getUserCustomization($conn, $userId)
{
    $default = [
        'user_id'        => (int)$userId,
        'bio'            => '',
        'song_title'     => '',
        'song_artist'    => '',
        'song_artwork'   => '',
        'song_url'       => '',
        'theme_accent'  => '#1d9bf0',
        'theme_banner'  => '',
        'banner_pos_y'  => 50,
        'theme_bg_type' => 'default',
        'theme_bg_val'  => '',
    ];

    if (!$conn || $userId <= 0) {
        return $default;
    }

    ensureCustomizationsTable($conn);

    $stmt = $conn->prepare("SELECT user_id, bio, song_title, song_artist, song_artwork, song_url, theme_accent, theme_banner, banner_pos_y, theme_bg_type, theme_bg_val FROM profile_customizations WHERE user_id = ? LIMIT 1");
    if (!$stmt) {
        return $default;
    }

    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        $merged = array_merge($default, $row);
        $merged['banner_pos_y'] = isset($row['banner_pos_y']) ? (int)$row['banner_pos_y'] : 50;
        return $merged;
    }

    return $default;
}

/**
 * Tallentaa tai päivittää käyttäjän kustomointiasetukset.
 *
 * @param mysqli $conn
 * @param int $userId
 * @param array $data
 * @return bool
 */
function saveUserCustomization($conn, $userId, array $data)
{
    if (!$conn || $userId <= 0) {
        return false;
    }

    ensureCustomizationsTable($conn);

    $current = getUserCustomization($conn, $userId);

    $bio           = isset($data['bio']) ? mb_substr(trim($data['bio']), 0, 255, 'UTF-8') : $current['bio'];
    $songTitle     = isset($data['song_title']) ? mb_substr(trim($data['song_title']), 0, 100, 'UTF-8') : $current['song_title'];
    $songArtist    = isset($data['song_artist']) ? mb_substr(trim($data['song_artist']), 0, 100, 'UTF-8') : $current['song_artist'];
    $songArtwork   = isset($data['song_artwork']) ? trim($data['song_artwork']) : $current['song_artwork'];
    $songUrl       = isset($data['song_url']) ? trim($data['song_url']) : $current['song_url'];
    $themeAccent   = isset($data['theme_accent']) ? sanitizeHexColor($data['theme_accent']) : $current['theme_accent'];
    $themeBanner   = isset($data['theme_banner']) ? trim($data['theme_banner']) : $current['theme_banner'];
    $bannerPosY  = isset($data['banner_pos_y']) ? max(0, min(100, (int)$data['banner_pos_y'])) : (int)($current['banner_pos_y'] ?? 50);
    $themeBgType = isset($data['theme_bg_type']) ? trim($data['theme_bg_type']) : $current['theme_bg_type'];
    $themeBgVal  = isset($data['theme_bg_val']) ? trim($data['theme_bg_val']) : $current['theme_bg_val'];

    $sql = "INSERT INTO profile_customizations 
            (user_id, bio, song_title, song_artist, song_artwork, song_url, theme_accent, theme_banner, banner_pos_y, theme_bg_type, theme_bg_val) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            bio = VALUES(bio),
            song_title = VALUES(song_title),
            song_artist = VALUES(song_artist),
            song_artwork = VALUES(song_artwork),
            song_url = VALUES(song_url),
            theme_accent = VALUES(theme_accent),
            theme_banner = VALUES(theme_banner),
            banner_pos_y = VALUES(banner_pos_y),
            theme_bg_type = VALUES(theme_bg_type),
            theme_bg_val = VALUES(theme_bg_val)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "isssssssiss",
        $userId,
        $bio,
        $songTitle,
        $songArtist,
        $songArtwork,
        $songUrl,
        $themeAccent,
        $themeBanner,
        $bannerPosY,
        $themeBgType,
        $themeBgVal
    );

    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

/**
 * Palauttaa käyttäjän bannerikuvan URL-osoitteen (tukee ladattua tiedostoa tai ulkoista linkkiä).
 *
 * @param string|null $banner
 * @return string|null
 */
function getUserBannerUrl($banner)
{
    if (empty($banner)) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $banner)) {
        return htmlspecialchars($banner, ENT_QUOTES);
    }

    $clean = basename($banner);
    $filePath = __DIR__ . "/../uploads/banners/" . $clean;
    if (file_exists($filePath)) {
        return "uploads/banners/" . htmlspecialchars($clean, ENT_QUOTES);
    }

    return null;
}

/**
 * Päivittää käyttäjän bannerikuvan (Upload Banner).
 *
 * @param mysqli $conn
 * @param int $userId
 * @param array $file
 * @return bool|string True jos onnistui, virheviesti jos epäonnistui
 */
function updateUserBanner($conn, $userId, $file)
{
    if (!isset($file) || !is_array($file) || !isset($file['error'])) {
        return "Bannerin latauksessa tapahtui virhe.";
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return "Bannerin koko saa olla enintään 5 MB.";
    }

    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return "Bannerin latauksessa tapahtui virhe.";
    }

    // Maksimikoko 5 MB
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return "Bannerin koko saa olla enintään 5 MB.";
    }

    // Sallitut MIME-tyypit
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mimeType, $allowedMimes)) {
        return "Vain JPG-, PNG-, WEBP- ja GIF-kuvat ovat sallittuja.";
    }

    $extension = $allowedMimes[$mimeType];
    $uploadDir = __DIR__ . "/../uploads/banners/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Haetaan vanha banneri poistettavaksi
    $current = getUserCustomization($conn, $userId);
    if (!empty($current['theme_banner']) && !preg_match('/^https?:\/\//i', $current['theme_banner'])) {
        $oldFile = $uploadDir . basename($current['theme_banner']);
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    // Luodaan uniikki ja turvallinen tiedostonimi
    $newFileName = "banner_" . $userId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return "Bannerin tallentaminen palvelimelle epäonnistui.";
    }

    // Päivitetään tietokanta
    return saveUserCustomization($conn, $userId, ['theme_banner' => $newFileName]);
}

/**
 * Poistaa käyttäjän bannerikuvan.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return bool
 */
function deleteUserBanner($conn, $userId)
{
    $uploadDir = __DIR__ . "/../uploads/banners/";
    $current = getUserCustomization($conn, $userId);
    if (!empty($current['theme_banner']) && !preg_match('/^https?:\/\//i', $current['theme_banner'])) {
        $oldFile = $uploadDir . basename($current['theme_banner']);
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    return saveUserCustomization($conn, $userId, ['theme_banner' => '']);
}

/**
 * Palauttaa käyttäjän taustakuvan URL-osoitteen.
 *
 * @param string|null $bgVal
 * @return string|null
 */
function getUserBackgroundUrl($bgVal)
{
    if (empty($bgVal)) {
        return null;
    }

    if (preg_match('/^https?:\/\//i', $bgVal)) {
        return htmlspecialchars($bgVal, ENT_QUOTES);
    }

    $clean = basename($bgVal);
    $filePath = __DIR__ . "/../uploads/backgrounds/" . $clean;
    if (file_exists($filePath)) {
        return "uploads/backgrounds/" . htmlspecialchars($clean, ENT_QUOTES);
    }

    return null;
}

/**
 * Päivittää käyttäjän taustakuvan tiedostosta (Upload Background).
 *
 * @param mysqli $conn
 * @param int $userId
 * @param array $file
 * @return bool|string True jos onnistui, virheviesti jos epäonnistui
 */
function updateUserBackground($conn, $userId, $file)
{
    if (!isset($file) || !is_array($file) || !isset($file['error'])) {
        return "Taustakuvan latauksessa tapahtui virhe.";
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return "Taustakuvan koko saa olla enintään 8 MB.";
    }

    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return "Taustakuvan latauksessa tapahtui virhe.";
    }

    // Maksimikoko 8 MB
    $maxSize = 8 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return "Taustakuvan koko saa olla enintään 8 MB.";
    }

    // Sallitut MIME-tyypit
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!array_key_exists($mimeType, $allowedMimes)) {
        return "Vain JPG-, PNG-, WEBP- ja GIF-kuvat ovat sallittuja.";
    }

    $extension = $allowedMimes[$mimeType];
    $uploadDir = __DIR__ . "/../uploads/backgrounds/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Haetaan vanha taustakuva poistettavaksi
    $current = getUserCustomization($conn, $userId);
    if (!empty($current['theme_bg_val']) && !preg_match('/^https?:\/\//i', $current['theme_bg_val'])) {
        $oldFile = $uploadDir . basename($current['theme_bg_val']);
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    // Luodaan uniikki tiedostonimi
    $newFileName = "bg_" . $userId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return "Taustakuvan tallentaminen palvelimelle epäonnistui.";
    }

    // Päivitetään tietokanta
    return saveUserCustomization($conn, $userId, [
        'theme_bg_type' => 'custom_image',
        'theme_bg_val'  => $newFileName
    ]);
}

/**
 * Poistaa käyttäjän taustakuvan.
 *
 * @param mysqli $conn
 * @param int $userId
 * @return bool
 */
function deleteUserBackground($conn, $userId)
{
    $uploadDir = __DIR__ . "/../uploads/backgrounds/";
    $current = getUserCustomization($conn, $userId);
    if (!empty($current['theme_bg_val']) && !preg_match('/^https?:\/\//i', $current['theme_bg_val'])) {
        $oldFile = $uploadDir . basename($current['theme_bg_val']);
        if (file_exists($oldFile)) {
            @unlink($oldFile);
        }
    }

    return saveUserCustomization($conn, $userId, [
        'theme_bg_type' => 'default',
        'theme_bg_val'  => ''
    ]);
}

/**
 * Palauttaa kaikki Hi5-esiasetetut taustateemat (Presets).
 *
 * @return array
 */
function getHi5BackgroundPresets()
{
    return [
        [
            'id'    => 'default',
            'name'  => 'Oletus (Tumma)',
            'desc'  => 'Klassinen musta',
            'type'  => 'color',
            'thumb' => 'linear-gradient(135deg, #050608, #12141a)',
            'css'   => 'background-color: #000000; background-image: none;'
        ],
        [
            'id'    => 'pattern_stars',
            'name'  => 'Tähtitaivas',
            'desc'  => 'Kimaltavat tähdet',
            'type'  => 'pattern',
            'thumb' => 'radial-gradient(#ffffff 1.5px, #060810 1.5px)',
            'css'   => 'background-color: #060810; background-image: radial-gradient(#ffffff 1px, transparent 1px), radial-gradient(#ff007f 1px, transparent 1px); background-size: 36px 36px, 54px 54px; background-position: 0 0, 18px 18px;'
        ],
        [
            'id'    => 'pattern_grid',
            'name'  => 'Cyber Grid',
            'desc'  => 'Retroruudukko',
            'type'  => 'pattern',
            'thumb' => 'linear-gradient(rgba(0, 240, 255, 0.5) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 240, 255, 0.5) 1px, #070a12 1px)',
            'css'   => 'background-color: #070a12; background-image: linear-gradient(rgba(0, 240, 255, 0.12) 1px, transparent 1px), linear-gradient(90deg, rgba(0, 240, 255, 0.12) 1px, transparent 1px); background-size: 32px 32px;'
        ],
        [
            'id'    => 'pattern_polka',
            'name'  => 'Retro Polka',
            'desc'  => 'Pisteet',
            'type'  => 'pattern',
            'thumb' => 'radial-gradient(rgba(255, 255, 255, 0.5) 1.5px, #0b0d13 1.5px)',
            'css'   => 'background-color: #0b0d13; background-image: radial-gradient(rgba(255, 255, 255, 0.14) 1.5px, transparent 1.5px); background-size: 24px 24px;'
        ],
        [
            'id'    => 'gradient_sunset',
            'name'  => 'Neon Sunset',
            'desc'  => 'Auringonlasku',
            'type'  => 'gradient',
            'thumb' => 'linear-gradient(135deg, #2b0b47, #0d0e15)',
            'css'   => 'background: linear-gradient(180deg, #19092c 0%, #0d0e15 100%) fixed;'
        ],
        [
            'id'    => 'preset_nebula',
            'name'  => 'Cosmic Nebula',
            'desc'  => 'Avaruussumu',
            'type'  => 'image',
            'thumb' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=300&q=60',
            'url'   => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1920&q=80',
            'css'   => "background-color: #060810; background-image: url('https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;"
        ],
        [
            'id'    => 'preset_cyberpunk',
            'name'  => 'Cyberpunk City',
            'desc'  => 'Neonkaupunki',
            'type'  => 'image',
            'thumb' => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=300&q=60',
            'url'   => 'https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1920&q=80',
            'css'   => "background-color: #070a12; background-image: url('https://images.unsplash.com/photo-1509198397868-475647b2a1e5?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;"
        ],
        [
            'id'    => 'preset_blossom',
            'name'  => 'Anime Blossom',
            'desc'  => 'Kirsikankukka',
            'type'  => 'image',
            'thumb' => 'https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&w=300&q=60',
            'url'   => 'https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&w=1920&q=80',
            'css'   => "background-color: #120810; background-image: url('https://images.unsplash.com/photo-1522383225653-ed111181a951?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;"
        ],
        [
            'id'    => 'preset_clouds',
            'name'  => 'Pastel Dream',
            'desc'  => 'Pastellipilvet',
            'type'  => 'image',
            'thumb' => 'https://images.unsplash.com/photo-1534088568595-a066f410bcda?auto=format&fit=crop&w=300&q=60',
            'url'   => 'https://images.unsplash.com/photo-1534088568595-a066f410bcda?auto=format&fit=crop&w=1920&q=80',
            'css'   => "background-color: #0c0e18; background-image: url('https://images.unsplash.com/photo-1534088568595-a066f410bcda?auto=format&fit=crop&w=1920&q=80'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;"
        ],
    ];
}

/**
 * Tuottaa profiilin taustatyylin CSS-koodin.
 *
 * @param string|null $bgType
 * @param string|null $bgVal
 * @return string
 */
function getProfileBackgroundStyle($bgType, $bgVal = '')
{
    $bgType = trim($bgType ?? 'default');
    if ($bgType === 'default' || empty($bgType)) {
        return '';
    }

    if ($bgType === 'custom_image' || $bgType === 'image') {
        $url = getUserBackgroundUrl($bgVal);
        if ($url) {
            return "background-color: #000000; background-image: url('" . htmlspecialchars($url, ENT_QUOTES) . "'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;";
        }
        return '';
    }

    $presets = getHi5BackgroundPresets();
    foreach ($presets as $p) {
        if ($p['id'] === $bgType) {
            return $p['css'];
        }
    }

    return '';
}


/**
 * Validoi ja puhdistaa heksavärikoodin.
 *
 * @param string $color
 * @param string $default
 * @return string
 */
function sanitizeHexColor($color, $default = '#1d9bf0')
{
    $clean = trim($color);
    if (preg_match('/^#[a-fA-F0-9]{6}$/', $clean) || preg_match('/^#[a-fA-F0-9]{3}$/', $clean)) {
        return strtolower($clean);
    }
    return $default;
}

/**
 * Palauttaa Hi5-tyyliset esiasetetut teemat (Preset Banners & Colors).
 *
 * @return array
 */
function getHi5PresetThemes()
{
    return [
        'colors' => [
            ['name' => 'Hi5 Hot Pink', 'hex' => '#ff007f'],
            ['name' => 'Cyber Cyan',   'hex' => '#00f0ff'],
            ['name' => 'Toxic Lime',   'hex' => '#39ff14'],
            ['name' => 'Electric Violet', 'hex' => '#9d00ff'],
            ['name' => 'Sunset Neon',  'hex' => '#ff6600'],
            ['name' => 'Mini-X Blue',  'hex' => '#1d9bf0'],
        ]
    ];
}

