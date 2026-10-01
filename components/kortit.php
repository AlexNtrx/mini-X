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
                    $isPinned = !empty($content['is_pinned']);
                    ?>
                    <?php if ($postRoom !== 'general' && isset($availableRooms[$postRoom])): ?>
                        <a href="index.php?page=home&room=<?= urlencode($postRoom) ?>" class="post-badge-room" title="<?= htmlspecialchars($availableRooms[$postRoom]['desc'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($availableRooms[$postRoom]['name'], ENT_QUOTES, 'UTF-8') ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($isPinned): ?>
                        <span class="post-badge-pinned" style="background: rgba(29, 155, 240, 0.15); color: #1d9bf0; font-size: 11px; padding: 2px 8px; border-radius: 9999px; font-weight: 700; border: 1px solid rgba(29, 155, 240, 0.3);" title="Ylläpidon kiinnitetty ilmoitus">📌 Kiinnitetty</span>
                    <?php endif; ?>

                    <?php if ($postType === 'question'): ?>
                        <span class="post-badge-question" title="Kysymysjulkaisu">Kysymys</span>
                        <?php if ($bestCommentId): ?>
                            <span class="post-badge-resolved" title="Ratkaistu (paras vastaus valittu)">✓ Ratkaistu</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php 
                $isOwner = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$content['user_id']);
                $isUserAdmin = (function_exists('isAdmin') && isAdmin());
                ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="post-menu">
                        <button
                            type="button"
                            class="menu-button"
                            onclick="toggleMenu(this)"
                            aria-label="Valikko">
                            ...
                        </button>

                        <div class="menu-dropdown">
                            <?php if ($isOwner): ?>
                                <button
                                    type="button"
                                    onclick="showEditForm(this)">
                                    Muokkaa
                                </button>
                                <form method="POST">
                                    <input type="hidden" name="id" value="<?= $content["id"] ?>">
                                    <button
                                        type="submit"
                                        name="delete_post"
                                        onclick="return confirm('Haluatko varmasti poistaa tämän julkaisun?')">
                                        Poista
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($isUserAdmin): ?>
                                <form method="POST">
                                    <input type="hidden" name="admin_action" value="admin_toggle_pin">
                                    <input type="hidden" name="post_id" value="<?= $content["id"] ?>">
                                    <button type="submit">
                                        <?= $isPinned ? '📌 Poista kiinnitys' : '📌 Kiinnitä ilmoitukseksi' ?>
                                    </button>
                                </form>
                                <?php if (!$isOwner): ?>
                                    <form method="POST">
                                        <input type="hidden" name="admin_action" value="admin_delete_post">
                                        <input type="hidden" name="post_id" value="<?= $content["id"] ?>">
                                        <button type="submit" style="color: #f4212e;" onclick="return confirm('YLLÄPITO: Haluatko varmasti poistaa tämän julkaisun?')">
                                            🗑️ Poista (Ylläpito)
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php if (!$isOwner): ?>
                                <button
                                    type="button"
                                    onclick="openReportModal(<?= (int)$content['id'] ?>, 'post')"
                                    style="color: #ff9800;">
                                    🚨 Ilmianna julkaisu
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
            <?php
            $postId = (int)$content['id'];
            $isPostOwner = (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$content['user_id']);
            $likeCount = (isset($conn) && $conn) ? getLikeCount($conn, $postId) : 0; 
            $isLiked = (isset($conn) && $conn) ? isPostLikedByUser($conn, $postId, $_SESSION['user_id'] ?? 0) : false;
            $commentCount = (isset($conn) && $conn) ? getCommentCount($conn, $postId) : 0;
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

                <!-- Comments List Wrapper -->
                <div class="comments-list-wrapper" id="comments-list-wrapper-<?= $postId ?>">
                    <?= renderCommentsListHtml($conn, $postId, $bestCommentId, (int)($content['user_id'] ?? 0)) ?>
                </div>
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