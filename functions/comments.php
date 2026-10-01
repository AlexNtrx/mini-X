<?php

// Lisää kommentin julkaisuun (tukee myös vastauksia eli parent_id)
function addComment($conn, $postId, $userId, $content, $parentId = null)
{
    $content = trim($content);
    if ($content === '') {
        return false;
    }

    $validParentId = null;
    if ($parentId !== null && (int)$parentId > 0) {
        $pId = (int)$parentId;
        $checkStmt = $conn->prepare("SELECT id, parent_id, post_id FROM comments WHERE id = ? LIMIT 1");
        if ($checkStmt) {
            $checkStmt->bind_param("i", $pId);
            $checkStmt->execute();
            $parentRow = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();
            if ($parentRow && (int)$parentRow['post_id'] === (int)$postId) {
                // Rajoitetaan 1-tasoiseen ketjutukseen: jos kohde on jo alikommentti, liitetään sen pääkommenttiin
                $validParentId = !empty($parentRow['parent_id']) ? (int)$parentRow['parent_id'] : (int)$parentRow['id'];
            }
        }
    }

    if ($validParentId !== null) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, parent_id, user_id, content) VALUES (?, ?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("iiis", $postId, $validParentId, $userId, $content);
    } else {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param("iis", $postId, $userId, $content);
    }

    $res = $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();
    return $res ? $insertId : false;
}

// Hakee julkaisun kaikkien kommenttien kokonaismäärän
function getCommentCount($conn, $postId)
{
    if (!$conn || $postId <= 0) return 0;
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM comments WHERE post_id = ?");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['total'] ?? 0);
}

// Hakee kommentin kirjoittajan user_id:n
function getCommentOwnerId($conn, $commentId)
{
    if (!$conn || $commentId <= 0) return 0;
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ? LIMIT 1");
    if (!$stmt) return 0;
    $stmt->bind_param("i", $commentId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)($row['user_id'] ?? 0);
}

// Asettaa tai poistaa parhaan vastauksen (Best Answer)
function toggleBestAnswer($conn, $postId, $commentId, $userId)
{
    if (!$conn || $postId <= 0 || $userId <= 0) return false;

    // Tarkistetaan julkaisun omistaja ja nykyinen valinta
    $stmt = $conn->prepare("SELECT user_id, best_comment_id FROM posts WHERE id = ? LIMIT 1");
    if (!$stmt) return false;
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $post = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$post || (int)$post['user_id'] !== (int)$userId) {
        return false;
    }

    // Jos sama kommentti on jo valittu, poistetaan valinta (toggle off)
    if ((int)($post['best_comment_id'] ?? 0) === (int)$commentId) {
        $upd = $conn->prepare("UPDATE posts SET best_comment_id = NULL WHERE id = ?");
        if ($upd) {
            $upd->bind_param("i", $postId);
            $upd->execute();
            $upd->close();
        }
        return ['action' => 'unmarked'];
    }

    // Varmistetaan että valittava kommentti kuuluu tähän julkaisuun
    $cStmt = $conn->prepare("SELECT id, user_id FROM comments WHERE id = ? AND post_id = ? LIMIT 1");
    if (!$cStmt) return false;
    $cStmt->bind_param("ii", $commentId, $postId);
    $cStmt->execute();
    $comment = $cStmt->get_result()->fetch_assoc();
    $cStmt->close();

    if (!$comment) {
        return false;
    }

    $upd = $conn->prepare("UPDATE posts SET best_comment_id = ? WHERE id = ?");
    if (!$upd) return false;
    $upd->bind_param("ii", $commentId, $postId);
    $res = $upd->execute();
    $upd->close();

    return [
        'action' => 'marked',
        'comment_owner_id' => (int)$comment['user_id']
    ];
}

// Hakee julkaisun kaikki kommentit puurakenteena (pääkommentit + vastaukset, Best Answer ensin)
function getCommentsByPost($conn, $postId, $bestCommentId = null)
{
    $sql = "SELECT comments.*, 
                   users.username AS author, 
                   COALESCE(NULLIF(users.display_name, ''), users.username) AS author_display_name,
                   users.avatar AS author_avatar 
            FROM comments 
            JOIN users ON comments.user_id = users.id 
            WHERE comments.post_id = ? 
            ORDER BY comments.id ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $allComments = [];
    while ($row = $result->fetch_assoc()) {
        $allComments[] = $row;
    }
    $stmt->close();

    $parents = [];
    $repliesByParent = [];

    foreach ($allComments as $c) {
        $c['replies'] = [];
        if (empty($c['parent_id'])) {
            $parents[$c['id']] = $c;
        } else {
            $repliesByParent[$c['parent_id']][] = $c;
        }
    }

    // Kiinnitetään alikommentit (replies) pääkommentteihin
    foreach ($repliesByParent as $pId => $subList) {
        if (isset($parents[$pId])) {
            $parents[$pId]['replies'] = $subList;
        } else {
            // Jos vanhempaa ei löydy, näytetään omana kommenttinaan
            foreach ($subList as $orphan) {
                $parents[$orphan['id']] = $orphan;
            }
        }
    }

    // Jos bestCommentId on asetettu, siirretään kyseinen pääkommentti kärkeen
    if ($bestCommentId !== null && (int)$bestCommentId > 0 && isset($parents[(int)$bestCommentId])) {
        $best = $parents[(int)$bestCommentId];
        unset($parents[(int)$bestCommentId]);
        $parents = [(int)$bestCommentId => $best] + $parents;
    }

    return array_values($parents);
}

// Poistaa kommentin (ja sen alikommentit) sekä päivittää tarvittaessa julkaisun best_comment_id
function deleteComment($conn, $commentId, $userId)
{
    // Nollataan mahdollinen best_comment_id viittaus
    $upd = $conn->prepare("UPDATE posts SET best_comment_id = NULL WHERE best_comment_id = ?");
    if ($upd) {
        $upd->bind_param("i", $commentId);
        $upd->execute();
        $upd->close();
    }

    // Poistetaan myös alikommentit
    $delSub = $conn->prepare("DELETE FROM comments WHERE parent_id = ?");
    if ($delSub) {
        $delSub->bind_param("i", $commentId);
        $delSub->execute();
        $delSub->close();
    }

    $stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ii", $commentId, $userId);
    $res = $stmt->execute();
    $stmt->close();
    return $res;
}

/**
 * Renderöi julkaisun kommenttilistan HTML-muodossa
 *
 * @param mysqli $conn
 * @param int $postId
 * @param int|null $bestCommentId
 * @param int|null $postOwnerId
 * @return string
 */
function renderCommentsListHtml($conn, $postId, $bestCommentId = null, $postOwnerId = null)
{
    if (!$conn || $postId <= 0) return '';
    if ($postOwnerId === null) {
        $postOwnerId = getPostOwnerId($conn, $postId);
    }
    $comments = getCommentsByPost($conn, $postId, $bestCommentId);
    $isPostOwner = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$postOwnerId);

    ob_start();
    if (!empty($comments)): ?>
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <?php 
                $commentAvatarUrl = getUserAvatarUrl($comment['author_avatar'] ?? null); 
                $isThisBest = ($bestCommentId !== null && (int)$comment['id'] === (int)$bestCommentId);
                ?>
                <div class="comment-item <?= $isThisBest ? 'best-answer-card' : '' ?>" id="comment-<?= (int)$comment['id'] ?>">
                    <?php if ($isThisBest): ?>
                        <div class="best-answer-ribbon">
                            <span class="best-answer-ribbon-icon">⭐</span>
                            <span>Tekijän valitsema paras vastaus</span>
                        </div>
                    <?php endif; ?>

                    <div class="comment-main-row">
                        <div class="comment-avatar">
                            <?php if ($commentAvatarUrl): ?>
                                <img src="<?= $commentAvatarUrl ?>" alt="Avatar" class="avatar-img clickable-avatar" onclick="openAvatarModal('<?= htmlspecialchars($commentAvatarUrl, ENT_QUOTES) ?>')" title="Näytä profiilikuva">
                            <?php else: ?>
                                <?= getUserInitials($comment['author_display_name'] ?? ($comment['author'] ?? '')) ?>
                            <?php endif; ?>
                        </div>
                        <div class="comment-body">
                            <div class="comment-header">
                                <a href="index.php?page=profile&u=<?= urlencode($comment['author']) ?>" class="comment-author-link">
                                    <strong class="comment-author"><?= htmlspecialchars($comment['author_display_name'] ?? $comment['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span class="comment-handle">@<?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?></span>
                                </a>
                                <span class="comment-date" title="<?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') ?>">· <?= formatTimeAgo($comment['created_at']) ?></span>

                                <!-- Vastaa-nappi (Reply) -->
                                <button type="button" class="btn-reply-action" onclick="toggleCommentReplyBox('reply-box-<?= (int)$comment['id'] ?>', '@<?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?> ')" title="Vastaa tähän kommenttiin">
                                    ↩ Vastaa
                                </button>

                                <!-- Best Answer Nappi (vain julkaisun tekijälle) -->
                                <?php if ($isPostOwner): ?>
                                    <form method="POST" class="best-answer-form" style="display:inline;">
                                        <input type="hidden" name="post_id" value="<?= (int)$postId ?>">
                                        <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
                                        <button type="submit" name="toggle_best_answer" class="btn-best-answer-action <?= $isThisBest ? 'active' : '' ?>" title="<?= $isThisBest ? 'Peruuta parhaan vastauksen merkintä' : 'Merkitse parhaaksi vastaukseksi' ?>">
                                            <?= $isThisBest ? '★ Paras vastaus' : '☆ Valitse parhaaksi' ?>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$comment['user_id']): ?>
                                    <form method="POST" class="delete-comment-form" onsubmit="return confirm('Poistetaanko kommentti?');">
                                        <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
                                        <input type="hidden" name="post_id" value="<?= (int)$postId ?>">
                                        <button type="submit" name="delete_comment" class="delete-comment-btn" title="Poista kommentti">&times;</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="comment-text"><?= htmlspecialchars(trim($comment['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                    </div>

                    <!-- Inline Vastauslomake (Reply form) -->
                    <div class="comment-reply-box" id="reply-box-<?= (int)$comment['id'] ?>" style="display: none;">
                        <form method="POST" class="inline-reply-form">
                            <input type="hidden" name="post_id" value="<?= (int)$postId ?>">
                            <input type="hidden" name="parent_id" value="<?= (int)$comment['id'] ?>">
                            <div class="inline-reply-input-row">
                                <input type="text" name="comment_content" id="reply-input-<?= (int)$comment['id'] ?>" class="inline-reply-input" placeholder="Vastaa käyttäjälle @<?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?>..." maxlength="140" required autocomplete="off">
                                <button type="submit" name="add_comment" class="inline-reply-submit-btn">Vastaa</button>
                                <button type="button" class="inline-reply-cancel-btn" onclick="toggleCommentReplyBox('reply-box-<?= (int)$comment['id'] ?>')" title="Peruuta">&times;</button>
                            </div>
                        </form>
                    </div>

                    <!-- Alikommentit / Vastaukset (Threaded Replies) -->
                    <?php if (!empty($comment['replies'])): ?>
                        <div class="comment-replies-list">
                            <?php foreach ($comment['replies'] as $reply): ?>
                                <?php $replyAvatarUrl = getUserAvatarUrl($reply['author_avatar'] ?? null); ?>
                                <div class="comment-item reply-comment-item" id="comment-<?= (int)$reply['id'] ?>">
                                    <div class="comment-avatar">
                                        <?php if ($replyAvatarUrl): ?>
                                            <img src="<?= $replyAvatarUrl ?>" alt="Avatar" class="avatar-img clickable-avatar" onclick="openAvatarModal('<?= htmlspecialchars($replyAvatarUrl, ENT_QUOTES) ?>')" title="Näytä profiilikuva">
                                        <?php else: ?>
                                            <?= getUserInitials($reply['author_display_name'] ?? ($reply['author'] ?? '')) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-body">
                                        <div class="comment-header">
                                            <a href="index.php?page=profile&u=<?= urlencode($reply['author']) ?>" class="comment-author-link">
                                                <strong class="comment-author"><?= htmlspecialchars($reply['author_display_name'] ?? $reply['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                                <span class="comment-handle">@<?= htmlspecialchars($reply['author'], ENT_QUOTES, 'UTF-8') ?></span>
                                            </a>
                                            <span class="comment-date" title="<?= htmlspecialchars($reply['created_at'], ENT_QUOTES, 'UTF-8') ?>">· <?= formatTimeAgo($reply['created_at']) ?></span>
                                            <button type="button" class="btn-reply-action" onclick="toggleCommentReplyBox('reply-box-<?= (int)$comment['id'] ?>', '@<?= htmlspecialchars($reply['author'], ENT_QUOTES, 'UTF-8') ?> ')">↩ Vastaa</button>
                                            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$reply['user_id']): ?>
                                                <form method="POST" class="delete-comment-form" onsubmit="return confirm('Poistetaanko vastaus?');">
                                                    <input type="hidden" name="comment_id" value="<?= (int)$reply['id'] ?>">
                                                    <input type="hidden" name="post_id" value="<?= (int)$postId ?>">
                                                    <button type="submit" name="delete_comment" class="delete-comment-btn" title="Poista vastaus">&times;</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-text"><?= htmlspecialchars(trim($reply['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif;
    return ob_get_clean();
}
