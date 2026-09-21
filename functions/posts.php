<?php

// Hakee kaikki julkaisut etusivulle (käyttäjänimi ja avatar haetaan users-taulusta JOINilla)
function getShowContents($conn)
{
    $sql = "SELECT posts.*, 
                   users.username AS author, 
                   COALESCE(NULLIF(users.display_name, ''), users.username) AS author_display_name,
                   users.avatar AS author_avatar 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE users.deleted_at IS NULL 
            ORDER BY posts.id DESC";
    $result = $conn->query($sql);
    $contents = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contents[] = $row;
        }
    }
    return $contents;
}

// Hakee vain tietyn käyttäjän julkaisut profiilisivulle
function getUserPosts($conn, $userId)
{
    $sql = "SELECT posts.*, 
                   users.username AS author, 
                   COALESCE(NULLIF(users.display_name, ''), users.username) AS author_display_name,
                   users.avatar AS author_avatar 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id = ? AND users.deleted_at IS NULL 
            ORDER BY posts.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $contents = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contents[] = $row;
        }
    }
    $stmt->close();
    return $contents;
}

// Palauttaa julkaisun kuvan URL-osoitteen tai null
function getPostImageUrl($image)
{
    if (!empty($image)) {
        $filePath = __DIR__ . "/../uploads/posts/" . $image;
        if (file_exists($filePath)) {
            return "uploads/posts/" . htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
        }
    }
    return null;
}

// Käsittelee ja tallentaa julkaisun kuvan
function uploadPostImage($file, $userId)
{
    if (!isset($file) || !is_array($file) || !isset($file['error'])) {
        return ['error' => 'Kuvan latauksessa tapahtui virhe.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['filename' => null];
    }

    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return ['error' => 'Kuvan koko saa olla enintään 5 MB.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return ['error' => 'Kuvan latauksessa tapahtui virhe.'];
    }

    // Maksimikoko 5 MB
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['error' => 'Kuvan koko saa olla enintään 5 MB.'];
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
        return ['error' => 'Vain JPG, PNG, WEBP ja GIF -kuvat ovat sallittuja.'];
    }

    $ext = $allowedMimes[$mimeType];
    $uploadDir = __DIR__ . "/../uploads/posts/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'post_' . (int)$userId . '_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['error' => 'Kuvan tallentaminen epäonnistui.'];
    }

    return ['filename' => $fileName];
}

// Lisää uuden julkaisun (teksti, kuva tai molemmat)
function addPost($conn, $userId, $content = null, $image = null)
{
    $content = ($content !== null && trim($content) !== '') ? trim($content) : null;
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $content, $image);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Päivittää julkaisun sisällön
function updatePost($conn, $id, $content, $userId)
{
    $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $content, $id, $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Poistaa julkaisun ja sen liittyvät tykkäykset, kommentit, ilmoitukset sekä kuvan
function deletePost($conn, $id, $userId)
{
    // Haetaan julkaisun mahdollinen kuvatiedosto poistamista varten
    $imgStmt = $conn->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
    if ($imgStmt) {
        $imgStmt->bind_param("ii", $id, $userId);
        $imgStmt->execute();
        $imgRow = $imgStmt->get_result()->fetch_assoc();
        $imgStmt->close();
        if (!empty($imgRow['image'])) {
            $imagePath = __DIR__ . "/../uploads/posts/" . $imgRow['image'];
            if (file_exists($imagePath)) {
                @unlink($imagePath);
            }
        }
    }

    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $res = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected > 0) {
        // Poista julkaisuun liittyvät tykkäykset, kommentit ja ilmoitukset
        $delLikes = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
        if ($delLikes) {
            $delLikes->bind_param("i", $id);
            $delLikes->execute();
            $delLikes->close();
        }

        $delComments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        if ($delComments) {
            $delComments->bind_param("i", $id);
            $delComments->execute();
            $delComments->close();
        }

        $delNotifs = $conn->prepare("DELETE FROM notifications WHERE post_id = ?");
        if ($delNotifs) {
            $delNotifs->bind_param("i", $id);
            $delNotifs->execute();
            $delNotifs->close();
        }
    }

    return $res;
}

// Hakee julkaisut käyttäjänimen tai nimen perusteella (Selaa / Haku)
function searchPostsByUsername($conn, $keyword)
{
    $sql = "SELECT posts.*, 
                   users.username AS author, 
                   COALESCE(NULLIF(users.display_name, ''), users.username) AS author_display_name,
                   users.avatar AS author_avatar 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE (users.username LIKE ? OR users.display_name LIKE ?) AND users.deleted_at IS NULL 
            ORDER BY posts.id DESC";
    $stmt = $conn->prepare($sql);
    $escapedKeyword = addcslashes($keyword, '%_');
    $searchTerm = "%" . $escapedKeyword . "%";
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $contents = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $contents[] = $row;
        }
    }
    $stmt->close();
    return $contents;
}

// Hakee kaikki käyttäjät selaamista varten
function getAllUsers($conn)
{
    $result = $conn->query("SELECT id, username, COALESCE(NULLIF(display_name, ''), username) AS display_name, avatar FROM users WHERE deleted_at IS NULL ORDER BY username ASC");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}

// Suodattaa selaussivun pikavalintakäyttäjät (oletuksena admin tai ensimmäinen käyttäjä)
function getQuickSelectUsers(array $allUsers): array
{
    $adminUsers = [];
    foreach ($allUsers as $u) {
        if (strtolower($u['username'] ?? '') === 'admin') {
            $adminUsers[] = $u;
        }
    }
    if (!empty($adminUsers)) {
        return $adminUsers;
    }
    return !empty($allUsers) ? [reset($allUsers)] : [];
}
