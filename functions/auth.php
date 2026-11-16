<?php

// Tarkistaa onko käyttäjänimi varattu
function isUsernameExists($conn, $username, $excludeUserId = 0)
{
    if ($excludeUserId > 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $username, $excludeUserId);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
    }
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Rekisteröi uuden käyttäjän
function registerUser($conn, $username, $password, $email, $displayName = null)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $finalDisplayName = (!empty($displayName)) ? trim($displayName) : $username;
    $stmt = $conn->prepare("INSERT INTO users (username, display_name, password, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $finalDisplayName, $hashedPassword, $email);
    $success = $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    return $success ? $newId : false;
}

// Kirjaa käyttäjän sisään
function loginUser($conn, $username, $password)
{
    if (function_exists('ensureAdminSchema')) {
        ensureAdminSchema($conn);
    }
    $stmt = $conn->prepare("SELECT id, username, display_name, password, avatar, role, deleted_at, suspended_at FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $stmt->close();
            return $user;
        }
    }

    $stmt->close();
    return false;
}

// Aktivoi käyttäjätilin uudelleen (Reactivate Soft-Deleted Account)
function reactivateUser($conn, $userId)
{
    ensureAdminSchema($conn);
    $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ? AND deleted_at IS NOT NULL AND suspended_at IS NULL");
    $stmt->bind_param("i", $userId);
    $success = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    return $success;
}

function refreshAuthenticatedSession($conn): bool
{
    if (!isset($_SESSION['user_id'])) {
        return false;
    }

    ensureAdminSchema($conn);
    $stmt = $conn->prepare("SELECT username, display_name, avatar, role FROM users WHERE id = ? AND deleted_at IS NULL AND suspended_at IS NULL LIMIT 1");
    $userId = (int)$_SESSION['user_id'];
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION = [];
        return false;
    }

    $_SESSION['username'] = $user['username'];
    $_SESSION['display_name'] = !empty($user['display_name']) ? $user['display_name'] : $user['username'];
    $_SESSION['avatar'] = $user['avatar'];
    $_SESSION['role'] = $user['role'] ?? 'user';
    return true;
}

// Päivittää näyttönimen (Display Name)
function updateDisplayName($conn, $userId, $newDisplayName)
{
    $stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE id = ?");
    $stmt->bind_param("si", $newDisplayName, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Päivittää käyttäjänimen
function updateUsername($conn, $userId, $newUsername)
{
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $newUsername, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Päivittää salasanan
function updateSalasana($conn, $userId, $newSalasana)
{
    $hashedPassword = password_hash($newSalasana, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Tarkistaa onko sähköposti jo käytössä toisella käyttäjällä
function isEmailExists($conn, $email, $excludeUserId = 0)
{
    if ($excludeUserId > 0) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->bind_param("si", $email, $excludeUserId);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
    }
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Päivittää käyttäjän sähköpostin
function updateEmail($conn, $userId, $newEmail)
{
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
    $stmt->bind_param("si", $newEmail, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Poistaa käyttäjätilin väliaikaisesti (Soft Delete)
function softDeleteUser($conn, $userId, $password)
{
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        return "Salasana on virheellinen.";
    }

    $updateStmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ? AND deleted_at IS NULL AND suspended_at IS NULL");
    $updateStmt->bind_param("i", $userId);
    $success = $updateStmt->execute() && $updateStmt->affected_rows === 1;
    $updateStmt->close();

    return $success ? true : "Tilin poistaminen epäonnistui.";
}

// Palauttaa käyttäjän profiilikuvan URL-osoitteen tai null
function getUserAvatarUrl($avatar)
{
    if (!empty($avatar)) {
        $filePath = __DIR__ . "/../uploads/avatars/" . $avatar;
        if (file_exists($filePath)) {
            return "uploads/avatars/" . htmlspecialchars($avatar);
        }
    }
    return null;
}

// Palauttaa käyttäjän nimikirjaimet turvallisesti UTF-8-muodossa
function getUserInitials($username)
{
    $name = trim($username ?? '');
    if ($name === '') {
        return 'U';
    }
    $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY);
    if (count($parts) >= 2) {
        $first = mb_substr($parts[0], 0, 1, 'UTF-8');
        $second = mb_substr($parts[1], 0, 1, 'UTF-8');
        return htmlspecialchars(mb_strtoupper($first . $second, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }
    $initials = mb_substr($name, 0, 2, 'UTF-8');
    return htmlspecialchars(mb_strtoupper($initials, 'UTF-8'), ENT_QUOTES, 'UTF-8');
}

// Päivittää käyttäjän profiilikuvan
function updateUserAvatar($conn, $userId, $file, ?ProfileImageChanges $pending = null)
{
    if (!isset($file) || !is_array($file) || !isset($file['error'])) {
        return "Kuvan latauksessa tapahtui virhe.";
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return "Kuvan koko saa olla enintään 3 MB.";
    }

    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return "Kuvan latauksessa tapahtui virhe.";
    }

    // Maksimikoko 3 MB
    $maxSize = 3 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return "Kuvan koko saa olla enintään 3 MB.";
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
    $uploadDir = __DIR__ . "/../uploads/avatars/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $previous = getLocalProfileImagePath($uploadDir, getStoredAvatar($conn, $userId));

    // Luodaan uniikki ja turvallinen tiedostonimi
    $newFileName = "avatar_" . $userId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return "Kuvan tallentaminen palvelimelle epäonnistui.";
    }

    return saveUserAvatar($conn, $userId, $previous, $targetPath, $pending)
        ?: "Tietokannan päivitys epäonnistui.";
}

// Poistaa käyttäjän profiilikuvan
function deleteUserAvatar($conn, $userId, ?ProfileImageChanges $pending = null)
{
    $uploadDir = __DIR__ . "/../uploads/avatars/";
    $previous = getLocalProfileImagePath($uploadDir, getStoredAvatar($conn, $userId));
    return saveUserAvatar($conn, $userId, $previous, null, $pending);
}

function getStoredAvatar($conn, $userId): ?string
{
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $user['avatar'] ?? null;
}

function saveUserAvatar($conn, $userId, ?string $previous, ?string $replacement, ?ProfileImageChanges $pending): bool
{
    $avatar = $replacement === null ? null : basename($replacement);
    return saveProfileImageChange($pending, $previous, $replacement, function () use ($conn, $userId, $avatar) {
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->bind_param("si", $avatar, $userId);
        $saved = $stmt->execute();
        $stmt->close();
        if ($saved) {
            if ($avatar === null) {
                unset($_SESSION['avatar']);
            } else {
                $_SESSION['avatar'] = $avatar;
            }
        }
        return $saved;
    });
}

/**
 * Tallentaa käyttäjän profiilikuvan base64 DataURL -muodosta (Canvas Crop).
 *
 * @param mysqli $conn
 * @param int $userId
 * @param string $dataUrl
 * @return bool|string True onnistuessa, virheilmoitus epäonnistuessa.
 */
function updateUserAvatarFromDataUrl($conn, $userId, $dataUrl, ?ProfileImageChanges $pending = null)
{
    if (empty($dataUrl) || !is_string($dataUrl)) {
        return "Kuvan tiedot puuttuvat.";
    }

    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/i', $dataUrl, $matches)) {
        return "Virheellinen kuvaformaatti.";
    }

    $rawExt = strtolower($matches[1]);
    $ext = ($rawExt === 'jpeg' || $rawExt === 'jpg') ? 'jpg' : ($rawExt === 'webp' ? 'webp' : 'png');
    $imageData = base64_decode($matches[2]);

    if ($imageData === false || strlen($imageData) === 0) {
        return "Kuvan purkaminen epäonnistui.";
    }

    // Maksimikoko 4 MB
    if (strlen($imageData) > 4 * 1024 * 1024) {
        return "Kuvan koko saa olla enintään 4 MB.";
    }

    $uploadDir = __DIR__ . "/../uploads/avatars/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $previous = getLocalProfileImagePath($uploadDir, getStoredAvatar($conn, $userId));

    $newFileName = "avatar_" . $userId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (file_put_contents($targetPath, $imageData) === false) {
        return "Kuvan tallentaminen palvelimelle epäonnistui.";
    }

    return saveUserAvatar($conn, $userId, $previous, $targetPath, $pending)
        ?: "Tietokannan päivitys epäonnistui.";
}
