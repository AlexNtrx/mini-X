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
function registerUser($conn, $username, $password)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $hashedPassword);
    $success = $stmt->execute();
    $newId = $conn->insert_id;
    $stmt->close();
    return $success ? $newId : false;
}
// Kirjaa käyttäjän sisään
function loginUser($conn, $username, $password)
{
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
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

// Päivittää käyttäjänimen
function updateUsername($conn, $userId, $newUsername)
{
    $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->bind_param("si", $newUsername, $userId);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}
