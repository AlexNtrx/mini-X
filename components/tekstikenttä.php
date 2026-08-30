<?php $createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); ?>
<form method="POST" class="create-post">

    <div class="create-post-avatar">
        <?php if ($createPostAvatar): ?>
            <img src="<?= $createPostAvatar ?>" alt="Avatar" class="avatar-img">
        <?php else: ?>
            <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?>
        <?php endif; ?>
    </div>

    <div class="create-post-content">
        <div class="create-post-user-info">
            <span class="create-post-username"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span>
        </div>
        <textarea
            name="content"
            placeholder="Mitä tapahtuu?"
            required></textarea>

        <button type="submit" name="create_post">
            Julkaise
        </button>

    </div>

</form>