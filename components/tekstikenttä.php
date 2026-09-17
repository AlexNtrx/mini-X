<?php 
$createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); 
$createPostDisplayName = !empty($_SESSION['display_name']) ? $_SESSION['display_name'] : ($_SESSION['username'] ?? '');
?>
<form method="POST" class="create-post">

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
            placeholder="Mitä tapahtuu?"
            maxlength="140"
            required></textarea>

        <button type="submit" name="create_post">
            Julkaise
        </button>

    </div>

</form>