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
function registerUser($conn, $username, $password,$email)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password,email) VALUES (?, ?,?)");
    $stmt->bind_param("sss", $username, $hashedPassword,$email);
    $success = $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    return $success ? $newId : false;
}
// Kirjaa käyttäjän sisään
function loginUser($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT id, username, password, deleted_at FROM users WHERE username = ? LIMIT 1");
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
function updateSalasana($conn, $userId, $newSalasana)
{
    $hashedPassword = password_hash($newSalasana, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword , $userId);
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
