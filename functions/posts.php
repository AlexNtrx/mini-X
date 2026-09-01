<?php

// Hakee kaikki julkaisut etusivulle (käyttäjänimi ja avatar haetaan users-taulusta JOINilla)
function getShowContents($conn)
{
    $sql = "SELECT posts.*, users.username AS author, users.avatar AS author_avatar 
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
    $sql = "SELECT posts.*, users.username AS author, users.avatar AS author_avatar 
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

// Lisää uuden julkaisun
function addPost($conn, $userId, $content)
{
    $stmt = $conn->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $content);
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

// Poistaa julkaisun ja sen liittyvät tykkäykset, kommentit ja ilmoitukset
function deletePost($conn, $id, $userId)
{
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

// Hakee julkaisut käyttäjänimen perusteella (Selaa / Haku)
function searchPostsByUsername($conn, $keyword)
{
    $sql = "SELECT posts.*, users.username AS author, users.avatar AS author_avatar 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE users.username LIKE ? AND users.deleted_at IS NULL 
            ORDER BY posts.id DESC";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $keyword . "%";
    $stmt->bind_param("s", $searchTerm);
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
    $result = $conn->query("SELECT id, username, avatar FROM users WHERE deleted_at IS NULL ORDER BY username ASC");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}
