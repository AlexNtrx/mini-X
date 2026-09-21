<?php
require_once __DIR__ . "/../functions/init.php";
/** 
 * @var array $content 
 * @var mysqli $conn 
 */
?>
<article
    class="post"
    id="post-<?= $content["id"] ?>">

    <?php $postAvatarUrl = getUserAvatarUrl($content['author_avatar'] ?? null); ?>
    <!-- Post Avatar -->
    <div class="post-avatar">
        <?php if ($postAvatarUrl): ?>
            <img src="<?= $postAvatarUrl ?>" alt="Avatar" class="avatar-img clickable-avatar" onclick="openAvatarModal('<?= htmlspecialchars($postAvatarUrl, ENT_QUOTES) ?>')" title="Näytä profiilikuva">
        <?php else: ?>
            <?= getUserInitials($content['author_display_name'] ?? ($content['author'] ?? '')) ?>
        <?php endif; ?>
    </div>

    <div class="post-content">

        <!-- Normal post -->
        <div class="post-view">
            <div class="post-top">

                <div class="post-user">
                    <a href="index.php?page=profile&u=<?= urlencode($content['author']) ?>" class="post-author-link">
                        <strong class="post-author">
                            <?= htmlspecialchars($content["author_display_name"] ?? $content["author"], ENT_QUOTES, "UTF-8") ?>
                        </strong>
                        <span class="post-handle">
                            @<?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?>
                        </span>
                    </a>
                    <span class="post-date" title="<?= htmlspecialchars($content["created_at"], ENT_QUOTES, "UTF-8") ?>">
                        · <?= formatTimeAgo($content["created_at"]) ?>
                    </span>

                    <?php
                    $postRoom = $content['room'] ?? 'general';
                    $postType = $content['post_type'] ?? 'general';
                    $bestCommentId = !empty($content['best_comment_id']) ? (int)$content['best_comment_id'] : null;
                    $availableRooms = function_exists('getAvailableRooms') ? getAvailableRooms() : [];
                    ?>
                    <?php if ($postRoom !== 'general' && isset($availableRooms[$postRoom])): ?>
                        <a href="index.php?page=home&room=<?= urlencode($postRoom) ?>" class="post-badge-room" title="<?= htmlspecialchars($availableRooms[$postRoom]['desc'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= $availableRooms[$postRoom]['icon'] ?> <?= htmlspecialchars($availableRooms[$postRoom]['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($postType === 'question'): ?>
                        <span class="post-badge-question" title="Kysymysjulkaisu">❓ Kysymys</span>
                        <?php if ($bestCommentId): ?>
                            <span class="post-badge-resolved" title="Ratkaistu (paras vastaus valittu)">✓ Ratkaistu</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$content['user_id']): ?>
                    <div class="post-menu">

                        <button
                            type="button"
                            class="menu-button"
                            onclick="toggleMenu(this)">
                            ...
                        </button>

                        <div class="menu-dropdown">

                            <button
                                type="button"
                                onclick="showEditForm(this)">
                                Muokkaa
                            </button>
                            <!-- delete lomake -->
                            <form method="POST">
                                <input type="hidden" name="id" value="<?= $content["id"] ?>">
                                <button
                                    type="submit"
                                    name="delete_post"
                                    onclick="return confirm('Haluatko varmasti poistaa tämän julkaisun?')">
                                    Poista
                                </button>
                            </form>


                        </div>

                    </div>
                <?php endif; ?>

            </div>
            <?php
            $postId = (int)$content['id'];
            $isPostOwner = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$content['user_id']);
            $likeCount = (isset($conn) && $conn) ? getLikeCount($conn, $postId) : 0; 
            $isLiked = (isset($conn) && $conn) ? isPostLikedByUser($conn, $postId, $_SESSION['user_id'] ?? 0) : false;
            $comments = (isset($conn) && $conn) ? getCommentsByPost($conn, $postId, $bestCommentId) : [];
            $commentCount = 0;
            foreach ($comments as $cItem) {
                $commentCount += 1 + count($cItem['replies'] ?? []);
            }
            ?>
            <?php if (!empty(trim($content["content"] ?? ''))): ?>
                <p class="post-text"><?= htmlspecialchars(trim($content["content"]), ENT_QUOTES, "UTF-8") ?></p>
            <?php endif; ?>

            <?php 
            $postImageUrl = getPostImageUrl($content['image'] ?? null);
            if ($postImageUrl): 
            ?>
                <div class="post-media-container">
                    <img 
                        src="<?= $postImageUrl ?>" 
                        alt="Julkaisun kuva" 
                        class="post-media-img" 
                        loading="lazy"
                        onclick="openPostImageModal(this.src)"
                        title="Klikkaa nähdäksesi kuva suurena"
                    >
                </div>
            <?php endif; ?>

            <!-- Action Bar (Likes & Comments) -->
            <div class="post-actions">
                <!-- Like Form -->
                <form method="POST" class="action-form">
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <button type="submit" name="like_post" class="action-btn like-btn <?= $isLiked ? 'liked' : '' ?>" title="<?= $isLiked ? 'Peruuta tykkäys' : 'Tykkää' ?>">
                        <span class="action-icon"><?= $isLiked ? '&#9829;' : '&#9825;' ?></span>
                        <span class="action-count"><?= $likeCount > 0 ? $likeCount : '' ?></span>
                    </button>
                </form>

                <!-- Comment Toggle Button -->
                <button type="button" class="action-btn comment-btn" onclick="toggleCommentSection(<?= $postId ?>)" title="Kommentoi">
                    <span class="action-icon">&#128172;</span>
                    <span class="action-count"><?= $commentCount > 0 ? $commentCount : '' ?></span>
                </button>
            </div>

            <!-- Comments Section -->
            <div class="comments-section" id="comments-<?= $postId ?>">
                <!-- Add Comment Form -->
                <form method="POST" class="add-comment-form">
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <div class="comment-input-row">
                        <div class="comment-input-avatar">
                            <?php $currentCommentUserAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); ?>
                            <?php if ($currentCommentUserAvatar): ?>
                                <img src="<?= $currentCommentUserAvatar ?>" alt="Avatar" class="avatar-img">
                            <?php else: ?>
                                <?= getUserInitials(!empty($_SESSION['display_name']) ? $_SESSION['display_name'] : ($_SESSION['username'] ?? '')) ?>
                            <?php endif; ?>
                        </div>
                        <input type="text" name="comment_content" class="comment-input" placeholder="Kirjoita kommentti..." maxlength="140" required autocomplete="off">
                        <button type="submit" name="add_comment" class="comment-submit-btn">Vastaa</button>
                    </div>
                </form>

                <!-- Comments List -->
                <?php if (!empty($comments)): ?>
                    <div class="comments-list">
                        <?php foreach ($comments as $comment): ?>
                            <?php 
                            $commentAvatarUrl = getUserAvatarUrl($comment['author_avatar'] ?? null); 
                            $isThisBest = ($bestCommentId !== null && (int)$comment['id'] === $bestCommentId);
                            ?>
                            <div class="comment-item <?= $isThisBest ? 'best-answer-card' : '' ?>" id="comment-<?= $comment['id'] ?>">
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
                                            <button type="button" class="btn-reply-action" onclick="toggleCommentReplyBox('reply-box-<?= $comment['id'] ?>', '@<?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?> ')" title="Vastaa tähän kommenttiin">
                                                ↩ Vastaa
                                            </button>

                                            <!-- Best Answer Nappi (vain julkaisun tekijälle) -->
                                            <?php if ($isPostOwner): ?>
                                                <form method="POST" class="best-answer-form" style="display:inline;">
                                                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                    <button type="submit" name="toggle_best_answer" class="btn-best-answer-action <?= $isThisBest ? 'active' : '' ?>" title="<?= $isThisBest ? 'Peruuta parhaan vastauksen merkintä' : 'Merkitse parhaaksi vastaukseksi' ?>">
                                                        <?= $isThisBest ? '★ Paras vastaus' : '☆ Valitse parhaaksi' ?>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$comment['user_id']): ?>
                                                <form method="POST" class="delete-comment-form" onsubmit="return confirm('Poistetaanko kommentti?');">
                                                    <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                                                    <button type="submit" name="delete_comment" class="delete-comment-btn" title="Poista kommentti">&times;</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                        <div class="comment-text"><?= htmlspecialchars(trim($comment['content'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </div>
                                </div>

                                <!-- Inline Vastauslomake (Reply form) -->
                                <div class="comment-reply-box" id="reply-box-<?= $comment['id'] ?>" style="display: none;">
                                    <form method="POST" class="inline-reply-form">
                                        <input type="hidden" name="post_id" value="<?= $postId ?>">
                                        <input type="hidden" name="parent_id" value="<?= $comment['id'] ?>">
                                        <div class="inline-reply-input-row">
                                            <input type="text" name="comment_content" id="reply-input-<?= $comment['id'] ?>" class="inline-reply-input" placeholder="Vastaa käyttäjälle @<?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?>..." maxlength="140" required autocomplete="off">
                                            <button type="submit" name="add_comment" class="inline-reply-submit-btn">Vastaa</button>
                                            <button type="button" class="inline-reply-cancel-btn" onclick="toggleCommentReplyBox('reply-box-<?= $comment['id'] ?>')" title="Peruuta">&times;</button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Alikommentit / Vastaukset (Threaded Replies) -->
                                <?php if (!empty($comment['replies'])): ?>
                                    <div class="comment-replies-list">
                                        <?php foreach ($comment['replies'] as $reply): ?>
                                            <?php $replyAvatarUrl = getUserAvatarUrl($reply['author_avatar'] ?? null); ?>
                                            <div class="comment-item reply-comment-item" id="comment-<?= $reply['id'] ?>">
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
                                                        <button type="button" class="btn-reply-action" onclick="toggleCommentReplyBox('reply-box-<?= $comment['id'] ?>', '@<?= htmlspecialchars($reply['author'], ENT_QUOTES, 'UTF-8') ?> ')">↩ Vastaa</button>
                                                        <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$reply['user_id']): ?>
                                                            <form method="POST" class="delete-comment-form" onsubmit="return confirm('Poistetaanko vastaus?');">
                                                                <input type="hidden" name="comment_id" value="<?= $reply['id'] ?>">
                                                                <input type="hidden" name="post_id" value="<?= $postId ?>">
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
                <?php endif; ?>
            </div>

        </div>


        <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$content['user_id']): ?>
            <!-- Edit form -->
            <form
                class="edit-form"
                method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= $content["id"] ?>">

                <div class="post-user">
                    <strong class="post-author"><?= htmlspecialchars($content["author_display_name"] ?? $content["author"], ENT_QUOTES, "UTF-8") ?></strong>
                    <span class="post-handle">@<?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?></span>
                    <span>(muokkaus)</span>
                </div>

                <textarea
                    class="post-text"
                    name="content"
                    maxlength="140"
                    required><?= htmlspecialchars(trim($content["content"] ?? ''), ENT_QUOTES, "UTF-8") ?></textarea>

                <div class="edit-actions">
                    <button
                        type="submit"
                        name="update_post"
                        onclick="return confirm('Haluatko varmasti päivittää tämän julkaisun?')">
                        Tallenna
                    </button>
                    <button
                        type="button"
                        class="cancel-edit-btn"
                        onclick="cancelEditForm(this)">
                        Peruuta
                    </button>
                </div>

            </form>
        <?php endif; ?>

    </div>

</article>