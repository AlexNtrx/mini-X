<?php 
/**
 * Mini-X - Create Post Box Component
 */
$createPostAvatar = getUserAvatarUrl($_SESSION['avatar'] ?? null); 
$createPostDisplayName = !empty($_SESSION['display_name']) ? $_SESSION['display_name'] : ($_SESSION['username'] ?? '');
$availableRooms = function_exists('getAvailableRooms') ? getAvailableRooms() : [];
$currentPostRoom = trim($_GET['room'] ?? 'general');
if ($currentPostRoom === 'all' || !isset($availableRooms[$currentPostRoom])) {
    $currentPostRoom = 'general';
}
?>
<form method="POST" class="create-post" enctype="multipart/form-data">
    <input type="hidden" name="create_post" value="1">
    <input type="hidden" name="post_type" id="create-post-type-input" value="general">

    <a href="index.php?page=profile" class="create-post-avatar" aria-label="Siirry profiiliin" title="Näytä profiili">
        <?php if ($createPostAvatar): ?>
            <img src="<?= $createPostAvatar ?>" alt="Avatar" class="avatar-img">
        <?php else: ?>
            <?= getUserInitials($createPostDisplayName) ?>
        <?php endif; ?>
    </a>

    <div class="create-post-content">
        <!-- Yläpalkki: Huone & Julkaisutyyppi (Meta Bar) -->
        <div class="create-post-meta-bar">
            <!-- Valitse huone (Rooms Dropdown) -->
            <div class="create-post-room-picker" title="Valitse huone">
                <select name="room" id="create-post-room-select" class="create-post-room-select" aria-label="Valitse huone">
                    <?php foreach ($availableRooms as $rKey => $rData): ?>
                        <option value="<?= htmlspecialchars($rKey, ENT_QUOTES, 'UTF-8') ?>" <?= $currentPostRoom === $rKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($rData['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Julkaisutyyppi (Post Type Toggle: Yleinen / Kysymys) -->
            <div class="create-post-type-toggle">
                <button type="button" class="type-toggle-btn active" id="btn-type-general" onclick="setPostType('general', this)">
                    Yleinen
                </button>
                <button type="button" class="type-toggle-btn" id="btn-type-question" onclick="setPostType('question', this)">
                    Kysymys
                </button>
            </div>
        </div>

        <textarea
            name="content"
            id="create-post-textarea"
            placeholder="Mitä tapahtuu?"
            maxlength="140"></textarea>

        <!-- Kuvan esikatselualue -->
        <div class="post-preview-container" id="post-preview-container">
            <div class="post-preview-wrapper">
                <img id="post-preview-img" src="" alt="Kuvan esikatselu">
                <button type="button" class="btn-remove-preview" id="btn-remove-preview" title="Poista kuva" aria-label="Poista kuva">&times;</button>
            </div>
        </div>

        <div class="create-post-footer">
            <div class="create-post-actions">
                <input type="file" name="image" id="post-image-input" class="post-image-input" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;">
                <button type="button" class="btn-media-action" id="btn-trigger-media" title="Lisää kuva" aria-label="Lisää kuva" onclick="document.getElementById('post-image-input').click()">
                    <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
<script>
function setPostType(type, btn) {
    const input = document.getElementById('create-post-type-input');
    const textarea = document.getElementById('create-post-textarea');
    if (input) input.value = type;
    
    document.querySelectorAll('.type-toggle-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    if (btn) btn.classList.add('active');

    if (textarea) {
        if (type === 'question') {
            textarea.placeholder = "Mitä haluat kysyä?";
        } else {
            textarea.placeholder = "Mitä tapahtuu?";
        }
    }
}
</script>
