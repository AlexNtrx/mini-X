<?php
/**
 * Mini-X - Instagram Story Style Music Sticker Component
 * 
 * @var array $customization
 * @var bool $isOwnProfile
 */
if (!empty($customization['song_url'])): ?>
    <div class="ig-music-wrapper">
        <div class="ig-music-sticker" id="retro-music-player">
            <!-- Cover Art Squircle -->
            <div class="ig-music-cover-wrapper">
                <?php if (!empty($customization['song_artwork'])): ?>
                    <img src="<?= htmlspecialchars($customization['song_artwork'], ENT_QUOTES) ?>" alt="Kansi" class="ig-music-cover-img">
                <?php else: ?>
                    <div class="ig-music-cover-fallback">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/></svg>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Track & Artist Info -->
            <div class="ig-music-meta">
                <div class="ig-music-title-wrap">
                    <span class="ig-music-title" title="<?= htmlspecialchars($customization['song_title'] ?: 'Kappale') ?>"><?= htmlspecialchars($customization['song_title'] ?: 'Kappale') ?></span>
                </div>
                <div class="ig-music-artist-wrap">
                    <span class="ig-music-artist" title="<?= htmlspecialchars($customization['song_artist'] ?: 'Artisti') ?>"><?= htmlspecialchars($customization['song_artist'] ?: 'Artisti') ?></span>
                </div>
            </div>

            <!-- Animated Sound Wave (Instagram Equalizer) -->
            <div class="ig-music-wave" id="ig-music-wave" aria-label="Sound Wave">
                <span class="ig-wave-bar"></span>
                <span class="ig-wave-bar"></span>
                <span class="ig-wave-bar"></span>
                <span class="ig-wave-bar"></span>
            </div>

            <!-- Dedicated Always-Visible Play/Pause Button -->
            <button type="button" class="ig-music-play-btn" id="ig-music-play-btn" aria-label="Soita musiikki" title="Soita musiikki">
                <svg class="ig-btn-icon-play" width="13" height="13" viewBox="0 0 24 24" fill="currentColor">
                    <polygon points="6 4 20 12 6 20 6 4"/>
                </svg>
                <svg class="ig-btn-icon-pause" width="13" height="13" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                    <rect x="6" y="4" width="4" height="16" rx="1.5"/>
                    <rect x="14" y="4" width="4" height="16" rx="1.5"/>
                </svg>
            </button>

            <!-- Micro Progress Line -->
            <div class="ig-music-progress-bar" title="Kappaleen edistyminen">
                <div class="ig-music-progress-fill" id="retro-scrubber-fill" style="width: 0%;"></div>
            </div>

            <audio id="hi5-audio-element" src="<?= htmlspecialchars($customization['song_url'], ENT_QUOTES) ?>" preload="metadata"></audio>
        </div>
    </div>
<?php elseif (!empty($isOwnProfile)): ?>
    <button type="button" class="profile-add-hint-btn" onclick="openEditProfileModal()" style="margin-top: 10px;">
        + Lisää profiilimusiikki
    </button>
<?php endif; ?>
