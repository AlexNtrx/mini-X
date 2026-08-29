<?php
// Hakee kaikki julkaisut etusivulle (käyttäjänimi haetaan users-taulusta JOINilla)
function getShowContents($conn)
{
    $sql = "SELECT posts.*, users.username AS author 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
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
function getUserPosts($conn, $userId,)
{
    $sql = "SELECT posts.*, users.username AS author 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id = ? 
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

// Poistaa julkaisun
function deletePost($conn, $id, $userId)
{
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $id, $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

// Hakee julkaisut käyttäjänimen perusteella (Selaa / Haku)
function searchPostsByUsername($conn, $keyword)
{
        
    $sql = "SELECT posts.*, users.username AS author 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE users.username LIKE ? 
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
    $result = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    return $users;
}
