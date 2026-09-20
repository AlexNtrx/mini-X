<?php
/**
 * Mini-X - Edit Profile Modal Component
 * 
 * @var array $customization
 * @var string $profileDisplayName
 * @var string $bannerUrl
 * @var string $avatarUrl
 * @var array $presetThemes
 * @var string $currentBgType
 * @var string $currentBgVal
 * @var string|null $customBgUrl
 * @var array $bgPresets
 */
?>
<!-- Edit Profile Modal -->
<div id="edit-profile-modal" class="profile-modal-overlay" aria-hidden="true" role="dialog" aria-modal="true">
    <div class="profile-modal-card">
        <form method="POST" enctype="multipart/form-data" id="edit-profile-form">
            <input type="hidden" name="update_profile_modal" value="1">
            <input type="hidden" name="delete_avatar_flag" id="delete_avatar_flag" value="0">
            <input type="hidden" name="delete_banner_flag" id="delete_banner_flag" value="0">
            <input type="hidden" name="delete_song_flag" id="delete_song_flag" value="0">

            <input type="hidden" name="song_title" id="modal-song-title" value="<?= htmlspecialchars($customization['song_title'] ?? '') ?>">
            <input type="hidden" name="song_artist" id="modal-song-artist" value="<?= htmlspecialchars($customization['song_artist'] ?? '') ?>">
            <input type="hidden" name="song_artwork" id="modal-song-artwork" value="<?= htmlspecialchars($customization['song_artwork'] ?? '') ?>">
            <input type="hidden" name="song_url" id="modal-song-url" value="<?= htmlspecialchars($customization['song_url'] ?? '') ?>">
            <input type="hidden" name="theme_accent" id="modal-theme-accent-input" value="<?= htmlspecialchars($customization['theme_accent'] ?? '#1d9bf0') ?>">
            <input type="hidden" name="banner_pos_y" id="modal-banner-pos-y" value="<?= (int)($customization['banner_pos_y'] ?? 50) ?>">
            <input type="hidden" name="avatar_cropped_data" id="modal-avatar-cropped-data" value="">

            <!-- Modal Header -->
            <div class="profile-modal-header">
                <div class="modal-header-left">
                    <button type="button" class="modal-header-close" onclick="closeEditProfileModal()" aria-label="Sulje">&times;</button>
                    <h3 class="modal-header-title">Muokkaa profiilia</h3>
                </div>
                <button type="submit" class="modal-save-btn">Tallenna</button>
            </div>

            <!-- Modal Body -->
            <div class="profile-modal-body">
                <!-- Banner Area with "Vaihda banneri" and Slider Repositioning -->
                <div class="modal-banner-area" id="modal-banner-area" 
                     <?= !empty($bannerUrl) ? 'style="background-image: url(\'' . $bannerUrl . '\'); background-position: center ' . (int)($customization['banner_pos_y'] ?? 50) . '%;"' : '' ?>>
                    <input type="file" id="edit-banner-input" name="banner" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onclick="this.value=null;" onchange="handleBannerSelect(this)">

                    <!-- Position slider widget -->
                    <div class="modal-banner-slider-bar" id="modal-banner-slider-bar" style="<?= empty($bannerUrl) ? 'display: none;' : '' ?>" onclick="event.stopPropagation()">
                        <span class="modal-banner-slider-title">Sijainti</span>
                        <input type="range" id="modal-banner-slider" min="0" max="100" value="<?= (int)($customization['banner_pos_y'] ?? 50) ?>" oninput="onBannerSliderInput(this.value)">
                        <span id="modal-banner-pos-val"><?= (int)($customization['banner_pos_y'] ?? 50) ?>%</span>
                    </div>

                    <div class="modal-banner-actions">
                        <label for="edit-banner-input" class="modal-media-btn" title="Vaihda kansikuva / banneri" style="cursor: pointer;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            Vaihda banneri
                        </label>
                        <button type="button" class="modal-media-btn-danger" id="modal-banner-del-btn" onclick="removeModalBanner()" style="<?= empty($bannerUrl) ? 'display: none;' : '' ?>">Poista</button>
                    </div>
                </div>

                <!-- Avatar Section -->
                <div class="modal-avatar-section">
                    <input type="file" id="edit-avatar-input" name="avatar" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onclick="this.value=null;" onchange="handleAvatarSelect(this)">
                    
                    <div class="modal-avatar-wrapper">
                        <label for="edit-avatar-input" class="modal-avatar-preview" id="modal-avatar-preview-container" data-fallback="<?= getUserInitials($profileDisplayName) ?>" title="Klikkaa vaihtaaksesi profiilikuvan" style="cursor: pointer;">
                            <?php if ($avatarUrl): ?>
                                <img src="<?= $avatarUrl ?>" alt="Avatar" class="modal-avatar-img" id="modal-avatar-img">
                            <?php else: ?>
                                <span class="modal-avatar-fallback"><?= getUserInitials($profileDisplayName) ?></span>
                            <?php endif; ?>
                        </label>
                        <label for="edit-avatar-input" class="modal-avatar-badge-btn" title="Vaihda profiilikuva" style="cursor: pointer;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                <circle cx="12" cy="13" r="4"/>
                            </svg>
                        </label>
                    </div>

                    <div class="modal-avatar-actions">
                        <button type="button" class="modal-avatar-recrop-btn" id="modal-avatar-recrop-btn" onclick="openAvatarCropperForCurrent()" style="<?= empty($avatarUrl) ? 'display: none;' : '' ?>" title="Rajaa tai kohdista nykyinen profiilikuva">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px;"><path d="M6 2v14a2 2 0 0 0 2 2h14"/><path d="M18 22V8a2 2 0 0 0-2-2H2"/></svg>
                            Rajaa
                        </button>
                        <button type="button" class="modal-avatar-remove-btn" id="modal-avatar-del-btn" onclick="removeModalAvatar()" style="<?= empty($avatarUrl) ? 'display: none;' : '' ?>" title="Poista profiilikuva">
                            Poista kuva
                        </button>
                    </div>
                </div>

                <!-- Segmented Tabs Navigation -->
                <div class="modal-tabs-nav" role="tablist">
                    <button type="button" class="modal-tab-btn active" data-tab="modal-tab-profile" onclick="switchModalTab('modal-tab-profile')" role="tab" aria-selected="true">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>Profiili</span>
                    </button>
                    <button type="button" class="modal-tab-btn" data-tab="modal-tab-appearance" onclick="switchModalTab('modal-tab-appearance')" role="tab" aria-selected="false">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                        </svg>
                        <span>Tausta & Teema</span>
                    </button>
                    <button type="button" class="modal-tab-btn" data-tab="modal-tab-music" onclick="switchModalTab('modal-tab-music')" role="tab" aria-selected="false">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 18V5l12-2v13"/>
                            <circle cx="6" cy="18" r="3"/>
                            <circle cx="18" cy="16" r="3"/>
                        </svg>
                        <span>Musiikki</span>
                    </button>
                </div>

                <!-- Form Content (Tab Panes) -->
                <div class="modal-form-content">
                    <!-- TAB 1: Profiili (Perustiedot, Värit & Efektit) -->
                    <div id="modal-tab-profile" class="modal-tab-pane active" role="tabpanel">
                        <!-- Nimi (Display Name) -->
                        <div class="modal-form-group">
                            <label for="modal-display-name" class="modal-form-label">Näyttönimi</label>
                            <input type="text" id="modal-display-name" name="display_name" class="modal-form-input" value="<?= htmlspecialchars($profileDisplayName) ?>" required minlength="1" maxlength="25">
                        </div>

                        <!-- Tilaviesti -->
                        <div class="modal-form-group">
                            <label for="modal-bio" class="modal-form-label">Tilaviesti</label>
                            <textarea id="modal-bio" name="bio" class="modal-form-textarea" placeholder="Kirjoita tilaviesti..." maxlength="255"><?= htmlspecialchars($customization['bio'] ?? '') ?></textarea>
                        </div>

                        <!-- Teema & Korostusväri -->
                        <div class="modal-form-group">
                            <label class="modal-form-label">Korostusväri</label>
                            <div class="modal-color-swatches">
                                <?php foreach ($presetThemes['colors'] as $c): ?>
                                    <button type="button" 
                                            class="modal-color-swatch <?= strtolower($customization['theme_accent']) === strtolower($c['hex']) ? 'active' : '' ?>" 
                                            style="background-color: <?= $c['hex'] ?>;" 
                                            data-color="<?= $c['hex'] ?>"
                                            title="<?= htmlspecialchars($c['name']) ?> (<?= $c['hex'] ?>)"
                                            onclick="selectModalAccent('<?= $c['hex'] ?>', this)">
                                    </button>
                                <?php endforeach; ?>
                                <div class="modal-custom-color-picker" title="Valitse oma väri">
                                    <input type="color" id="modal-theme-accent-picker" class="modal-custom-color-input" value="<?= htmlspecialchars($customization['theme_accent']) ?>" onchange="selectModalAccent(this.value)">
                                    <span class="modal-custom-color-text">Oma väri</span>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: Tausta & Teema -->
                    <div id="modal-tab-appearance" class="modal-tab-pane" role="tabpanel">
                        <div class="modal-bg-section">
                            <div class="modal-bg-header">
                                <div class="modal-bg-header-text">
                                    <label class="modal-form-label" style="color: #ffffff; margin-bottom: 0;">Profiilin tausta</label>
                                </div>
                                <button type="button" class="modal-bg-reset-btn" id="modal-bg-reset-btn" onclick="removeModalCustomBg()" style="<?= ($currentBgType === 'default' && empty($customBgUrl)) ? 'display: none;' : '' ?>" title="Palauta oletustausta">
                                    Poista tausta
                                </button>
                            </div>

                            <!-- Hidden Inputs for Form Submission -->
                            <input type="hidden" id="modal-theme-bg-type" name="theme_bg_type" value="<?= htmlspecialchars($currentBgType) ?>">
                            <input type="hidden" id="modal-theme-bg-val" name="theme_bg_val" value="<?= htmlspecialchars($currentBgVal) ?>">
                            <input type="hidden" id="delete_bg_flag" name="delete_bg_flag" value="0">

                            <!-- Oma Taustakuva Upload Row -->
                            <div class="modal-bg-upload-row">
                                <input type="file" id="modal-bg-file" name="background_image" accept="image/jpeg,image/png,image/webp,image/gif" style="display: none;" onchange="handleCustomBgSelect(this)">
                                
                                <label for="modal-bg-file" class="modal-bg-upload-btn" title="Lataa oma taustakuva (JPG, PNG, WEBP max 8MB)">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <polyline points="17 8 12 3 7 8"/>
                                        <line x1="12" y1="3" x2="12" y2="15"/>
                                    </svg>
                                    Lataa oma taustakuva
                                </label>

                                <div id="modal-bg-custom-preview-card" class="modal-bg-custom-card <?= $currentBgType === 'custom_image' ? 'active' : '' ?>" style="<?= ($currentBgType === 'custom_image' && !empty($customBgUrl)) ? 'display: flex;' : 'display: none;' ?>">
                                    <img id="modal-bg-custom-thumb" src="<?= $customBgUrl ?? '' ?>" alt="Oma taustakuva" class="modal-bg-custom-img">
                                    <span class="modal-bg-custom-label">Oma kuva</span>
                                    <button type="button" class="modal-bg-custom-del" onclick="removeModalCustomBg()" title="Poista oma kuva">&times;</button>
                                </div>
                            </div>

                            <!-- Valmiit Teemat (Preset Grid) -->
                            <div class="modal-bg-grid">
                                <?php foreach ($bgPresets as $preset): ?>
                                    <?php 
                                        $isSelected = ($currentBgType === $preset['id']);
                                    ?>
                                    <div class="modal-bg-card <?= $isSelected ? 'active' : '' ?>" 
                                         data-bg-id="<?= htmlspecialchars($preset['id']) ?>" 
                                         data-bg-css="<?= htmlspecialchars($preset['css'], ENT_QUOTES) ?>"
                                         onclick="selectModalBackground('<?= $preset['id'] ?>', this.getAttribute('data-bg-css'), '')">
                                        <div class="modal-bg-card-preview" style="<?= $preset['type'] === 'image' ? "background-image: url('{$preset['thumb']}'); background-size: cover; background-position: center;" : "background: {$preset['thumb']};" ?>">
                                            <span class="modal-bg-card-check">✓</span>
                                        </div>
                                        <div class="modal-bg-card-info">
                                            <span class="modal-bg-card-name"><?= htmlspecialchars($preset['name']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: Musiikki -->
                    <div id="modal-tab-music" class="modal-tab-pane" role="tabpanel">
                        <div class="modal-music-section">
                            <div class="modal-music-header">
                                <label class="modal-form-label" style="color: #ffffff; margin-bottom: 0;">Profiilimusiikki</label>
                            </div>

                            <!-- Hakukenttä -->
                            <div class="modal-music-search-row">
                                <input type="text" id="modal-music-search-input" class="modal-music-search-input" placeholder="Hae kappaletta..." onkeydown="if(event.key==='Enter'){event.preventDefault();doModalMusicSearch();}">
                                <button type="button" class="modal-music-search-btn" onclick="doModalMusicSearch()">Etsi</button>
                            </div>

                            <!-- Pikaehdotukset -->
                            <div class="modal-music-chips">
                                <button type="button" class="music-chip" onclick="quickSearchModalMusic('Bodyslam')">Bodyslam</button>
                                <button type="button" class="music-chip" onclick="quickSearchModalMusic('Three Man Down')">Three Man Down</button>
                                <button type="button" class="music-chip" onclick="quickSearchModalMusic('Kamikaze')">Kamikaze</button>
                                <button type="button" class="music-chip" onclick="quickSearchModalMusic('Taylor Swift')">Taylor Swift</button>
                                <button type="button" class="music-chip" onclick="quickSearchModalMusic('Avril Lavigne')">Avril Lavigne</button>
                            </div>

                            <!-- Latausindikaattori & Tulokset -->
                            <div id="modal-music-loading" style="display: none; padding: 12px; text-align: center; color: #71767b; font-size: 12px;">Etsitään...</div>
                            <div id="modal-music-results" class="modal-music-results" style="display: none;"></div>

                            <!-- Valittu kappale -->
                            <div id="modal-selected-song-container" class="modal-selected-song" style="<?= empty($customization['song_url']) ? 'display: none;' : '' ?>">
                                <img id="modal-selected-song-art" src="<?= htmlspecialchars($customization['song_artwork'] ?? '') ?>" alt="Kansi" class="modal-selected-song-art" style="<?= empty($customization['song_artwork']) ? 'display: none;' : '' ?>">
                                <div class="modal-selected-song-info">
                                    <span class="modal-selected-song-badge">VALITTU KAPPALE</span>
                                    <span id="modal-selected-song-title" class="modal-selected-song-title"><?= htmlspecialchars($customization['song_title'] ?? '') ?></span>
                                    <span id="modal-selected-song-artist" class="modal-selected-song-artist"><?= htmlspecialchars($customization['song_artist'] ?? '') ?></span>
                                </div>
                                <button type="button" class="modal-btn-remove-song" onclick="removeModalSelectedSong()" title="Poista musiikki">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
