<?php
require_once "functions/init.php";
/** 
 * @var array $content 
 * @var mysqli $conn 
 */
?>
<article
    class="post"
    id="post-<?= $content["id"] ?>">

    <!-- Post Avatar -->
    <div class="post-avatar">
        <?= strtoupper(substr($content['author'] ?? 'U', 0, 2)) ?>
    </div>

    <div class="post-content">

        <!-- Normal post -->
        <div class="post-view">
            <div class="post-top">

                <div class="post-user">
                    <strong class="post-author">
                        <?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?>
                    </strong>
                    <span>
                        <?= htmlspecialchars($content["created_at"], ENT_QUOTES, "UTF-8") ?>
                    </span>
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
                                    Poistaa
                                </button>
                            </form>


                        </div>

                    </div>
                <?php endif; ?>

            </div>
            <?php
            $postId = (int)$content['id'];
            $likeCount = (isset($conn) && $conn) ? getLikeCount($conn, $postId) : 0; 
            $isLiked = (isset($conn) && $conn) ? isPostLikedByUser($conn, $postId, $_SESSION['user_id'] ?? 0) : false;
            $comments = (isset($conn) && $conn) ? getCommentsByPost($conn, $postId) : [];
            $commentCount = count($comments);
            ?>
            <p class="post-text">
                <?= htmlspecialchars($content["content"], ENT_QUOTES, "UTF-8") ?>
            </p>

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
                        <input type="text" name="comment_content" class="comment-input" placeholder="Kirjoita kommentti..." required autocomplete="off">
                        <button type="submit" name="add_comment" class="comment-submit-btn">Vastaa</button>
                    </div>
                </form>

                <!-- Comments List -->
                <?php if (!empty($comments)): ?>
                    <div class="comments-list">
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item">
                                <div class="comment-avatar">
                                    <?= strtoupper(substr($comment['author'] ?? 'U', 0, 2)) ?>
                                </div>
                                <div class="comment-body">
                                    <div class="comment-header">
                                        <strong class="comment-author"><?= htmlspecialchars($comment['author'], ENT_QUOTES, 'UTF-8') ?></strong>
                                        <span class="comment-date"><?= htmlspecialchars($comment['created_at'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === (int)$comment['user_id']): ?>
                                            <form method="POST" class="delete-comment-form" onsubmit="return confirm('Poistetaanko kommentti?');">
                                                <input type="hidden" name="comment_id" value="<?= $comment['id'] ?>">
                                                <input type="hidden" name="post_id" value="<?= $postId ?>">
                                                <button type="submit" name="delete_comment" class="delete-comment-btn" title="Poista kommentti">&times;</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-text"><?= htmlspecialchars($comment['content'], ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
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
                    <strong class="post-author"><?= htmlspecialchars($content["author"], ENT_QUOTES, "UTF-8") ?></strong>
                    <span>(muokkaus)</span>
                </div>

                <textarea
                    class="post-text"
                    name="content"
                    required><?= htmlspecialchars($content["content"], ENT_QUOTES, "UTF-8") ?></textarea>

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