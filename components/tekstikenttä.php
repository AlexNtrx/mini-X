<?php 
$createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); 
$createPostDisplayName = !empty($_SESSION['display_name']) ? $_SESSION['display_name'] : ($_SESSION['username'] ?? '');
?>
<form method="POST" class="create-post" enctype="multipart/form-data">
    <input type="hidden" name="create_post" value="1">

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
                <button type="button" class="btn-media-action" id="btn-trigger-media" title="Lisää kuva" aria-label="Lisää kuva" style="display: inline-flex !important; align-items: center !important; justify-content: center !important; width: 36px !important; height: 36px !important; min-width: 36px !important; min-height: 36px !important; padding: 0 !important; margin: 0 !important; background: transparent !important; border: none !important; border-radius: 50% !important; color: #1d9bf0 !important; cursor: pointer !important; float: none !important; box-sizing: border-box !important;">
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: block !important; width: 22px !important; height: 22px !important; stroke: currentColor !important; pointer-events: none !important;">
                        <rect x="3" y="3" width="18" height="18" rx="5" ry="5"/>
                        <circle cx="15.5" cy="8.5" r="1.5" fill="currentColor" stroke="none"/>
                        <path d="M5 17.5L10 11.5L18.5 17.5"/>
                    </svg>
                </button>
            </div>

            <button type="submit" name="create_post" class="btn-publish-post">
                Julkaise
            </button>
        </div>

    </div>

</form>