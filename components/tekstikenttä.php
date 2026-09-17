<?php 
$createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); 
$createPostDisplayName = !empty($_SESSION['display_name']) ? $_SESSION['display_name'] : ($_SESSION['username'] ?? '');
?>
<form method="POST" class="create-post" enctype="multipart/form-data">

    <a href="index.php?page=profile" class="create-post-avatar" aria-label="Siirry profiiliin" title="Näytä profiili">
        <?php if ($createPostAvatar): ?>
            <img src="<?= $createPostAvatar ?>" alt="Avatar" class="avatar-img">
        <?php else: ?>
            <?= getUserInitials($createPostDisplayName) ?>
        <?php endif; ?>
    </a>

    <div class="create-post-content">
        <textarea
            name="content"
            id="create-post-textarea"
            placeholder="Mitä tapahtuu?"
            maxlength="140"></textarea>

        <!-- Kuvan esikatselualue -->
        <div class="post-preview-container" id="post-preview-container" style="display: none;">
            <div class="post-preview-wrapper">
                <img id="post-preview-img" src="" alt="Kuvan esikatselu">
                <button type="button" class="btn-remove-preview" id="btn-remove-preview" title="Poista kuva" aria-label="Poista kuva">&times;</button>
            </div>
        </div>

        <div class="create-post-footer">
            <div class="create-post-actions">
                <input type="file" name="image" id="post-image-input" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                <button type="button" class="btn-media-action" id="btn-trigger-media" title="Lisää kuva" aria-label="Lisää kuva">
                    <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" fill="currentColor">
                        <path d="M3 5.5C3 4.119 4.119 3 5.5 3h13C19.881 3 21 4.119 21 5.5v13c0 1.381-1.119 2.5-2.5 2.5h-13C4.119 21 3 19.881 3 18.5v-13zM5.5 5c-.276 0-.5.224-.5.5v9.086l3.293-3.293a1 1 0 0 1 1.414 0l3.293 3.293 3.293-3.293a1 1 0 0 1 1.414 0L19 12.586V5.5c0-.276-.224-.5-.5-.5h-13zM19 15.414l-2-2-3.293 3.293a1 1 0 0 1-1.414 0L9 13.414l-4 4V18.5c0 .276.224.5.5.5h13c.276 0 .5-.224.5-.5v-3.086zM8.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3z"/>
                    </svg>
                </button>
            </div>

            <button type="submit" name="create_post" class="btn-publish-post">
                Julkaise
            </button>
        </div>

    </div>

</form>