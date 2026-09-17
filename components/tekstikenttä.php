<?php 
$createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); 
$createPostDisplayName = $_SESSION['display_name'] ?? ($_SESSION['username'] ?? '');
?>
<form method="POST" class="create-post">

    <div class="create-post-avatar">
        <?php if ($createPostAvatar): ?>
            <img src="<?= $createPostAvatar ?>" alt="Avatar" class="avatar-img">
        <?php else: ?>
            <?= getUserInitials($createPostDisplayName) ?>
        <?php endif; ?>
    </div>

    <div class="create-post-content">
        <div class="create-post-user-info">
            <span class="create-post-username"><?= htmlspecialchars($createPostDisplayName) ?></span>
            <span class="create-post-handle">@<?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        </div>
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