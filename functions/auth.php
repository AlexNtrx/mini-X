<?php

// Tarkistaa onko käyttäjänimi varattu
function isUsernameExists($conn, $username)
{
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Rekisteröi uuden käyttäjän
function registerUser($conn, $username, $password, $email)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $email);
    $success = $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    return $success ? $newId : false;
}

// Kirjaa käyttäjän sisään
function loginUser($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT id, username, password, avatar, deleted_at FROM users WHERE username = ? LIMIT 1");
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
    $stmt = $conn->prepare("UPDATE users SET deleted_at = NULL WHERE id = ?");
    $stmt->bind_param("i", $userId);
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
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND deleted_at IS NULL LIMIT 1");
        $stmt->bind_param("si", $email, $excludeUserId);
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1");
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

    $updateStmt = $conn->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $userId);
    $success = $updateStmt->execute();
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

// Päivittää käyttäjän profiilikuvan
function updateUserAvatar($conn, $userId, $file)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
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

    // Haetaan vanha kuva poistettavaksi
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $oldUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($oldUser['avatar'])) {
        $oldFilePath = $uploadDir . $oldUser['avatar'];
        if (file_exists($oldFilePath)) {
            @unlink($oldFilePath);
        }
    }

    // Luodaan uniikki ja turvallinen tiedostonimi
    $newFileName = "avatar_" . $userId . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $extension;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return "Kuvan tallentaminen palvelimelle epäonnistui.";
    }

    // Päivitetään tietokanta
    $updateStmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
    $updateStmt->bind_param("si", $newFileName, $userId);
    $success = $updateStmt->execute();
    $updateStmt->close();

    if ($success) {
        $_SESSION['avatar'] = $newFileName;
        return true;
    }

    return "Tietokannan päivitys epäonnistui.";
}

// Poistaa käyttäjän profiilikuvan
function deleteUserAvatar($conn, $userId)
{
    $uploadDir = __DIR__ . "/../uploads/avatars/";
    $stmt = $conn->prepare("SELECT avatar FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!empty($user['avatar'])) {
        $filePath = $uploadDir . $user['avatar'];
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    }

    $updateStmt = $conn->prepare("UPDATE users SET avatar = NULL WHERE id = ?");
    $updateStmt->bind_param("i", $userId);
    $success = $updateStmt->execute();
    $updateStmt->close();

    if ($success) {
        unset($_SESSION['avatar']);
        return true;
    }

    return false;
}
