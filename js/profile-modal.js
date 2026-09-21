/**
 * Mini-X - Profile Modal Script
 * Hallinnoi suoraa profiilin muokkausta:
 * - Bannerin reaaliaikainen pystysijainnin säätö (Drag to Reposition)
 * - Profiilikuvan rajaus ja zoomaus (Avatar Circle Cropper)
 * - Nimi, Bio, Facebook-tyylinen musiikkihaku ja teeman korostusväri
 */

let activeModalPreviewAudio = null;
let activeModalPreviewBtn = null;

// Avaa profiilin muokkaus -modalin
function openEditProfileModal() {
    const modal = document.getElementById('edit-profile-modal');
    if (!modal) return;
    modal.classList.add('active');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
}

// Sulkee profiilin muokkaus -modalin
function closeEditProfileModal() {
    const modal = document.getElementById('edit-profile-modal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    stopModalAudioPreview();
}

// Vaihtaa modalin välilehteä (Segmented Tabs)
function switchModalTab(tabId) {
    const buttons = document.querySelectorAll('.modal-tab-btn');
    const panes = document.querySelectorAll('.modal-tab-pane');

    buttons.forEach(btn => {
        if (btn.getAttribute('data-tab') === tabId) {
            btn.classList.add('active');
            btn.setAttribute('aria-selected', 'true');
        } else {
            btn.classList.remove('active');
            btn.setAttribute('aria-selected', 'false');
        }
    });

    panes.forEach(pane => {
        if (pane.id === tabId) {
            pane.classList.add('active');
        } else {
            pane.classList.remove('active');
        }
    });
}

// Suljetaan ESC-näppäimellä
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const cropperModal = document.getElementById('avatar-cropper-modal');
        if (cropperModal && cropperModal.style.display !== 'none') {
            closeAvatarCropper();
            return;
        }

        const modal = document.getElementById('edit-profile-modal');
        if (modal && modal.classList.contains('active')) {
            closeEditProfileModal();
        }
    }
});

// Suljetaan jos klikataan taustalle
document.addEventListener('click', (e) => {
    const modal = document.getElementById('edit-profile-modal');
    if (modal && e.target === modal) {
        closeEditProfileModal();
    }
    const cropperModal = document.getElementById('avatar-cropper-modal');
    if (cropperModal && e.target === cropperModal) {
        closeAvatarCropper();
    }
});

// ==========================================
// 1. BANNERIN SÄÄTÖ (Drag to Reposition)
// ==========================================

let isDraggingBanner = false;
let bannerStartY = 0;
let bannerStartPos = 50;

function initBannerDrag() {
    const bannerArea = document.getElementById('modal-banner-area');
    if (!bannerArea) return;
    if (bannerArea.dataset.bannerInitialized === 'true') return;
    bannerArea.dataset.bannerInitialized = 'true';

    // Mouse drag
    bannerArea.addEventListener('mousedown', (e) => {
        if (e.target.closest('.modal-banner-actions') || e.target.closest('.modal-banner-slider-bar')) return;
        if (!bannerArea.style.backgroundImage || bannerArea.style.backgroundImage === 'none') return;

        isDraggingBanner = true;
        bannerStartY = e.clientY;
        const currentPosInput = document.getElementById('modal-banner-pos-y');
        bannerStartPos = currentPosInput ? (parseFloat(currentPosInput.value) || 50) : 50;
        bannerArea.classList.add('is-dragging');
        e.preventDefault();
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDraggingBanner) return;
        const diffY = e.clientY - bannerStartY;
        const deltaPercent = (diffY / 160) * 100;
        let newPos = bannerStartPos - deltaPercent;
        newPos = Math.max(0, Math.min(100, Math.round(newPos)));
        applyBannerPosY(newPos);
    });

    window.addEventListener('mouseup', () => {
        if (isDraggingBanner) {
            isDraggingBanner = false;
            const bannerArea = document.getElementById('modal-banner-area');
            if (bannerArea) bannerArea.classList.remove('is-dragging');
        }
    });

    // Touch drag (mobiili)
    bannerArea.addEventListener('touchstart', (e) => {
        if (e.target.closest('.modal-banner-actions') || e.target.closest('.modal-banner-slider-bar')) return;
        if (!bannerArea.style.backgroundImage || bannerArea.style.backgroundImage === 'none') return;
        if (e.touches.length !== 1) return;

        isDraggingBanner = true;
        bannerStartY = e.touches[0].clientY;
        const currentPosInput = document.getElementById('modal-banner-pos-y');
        bannerStartPos = currentPosInput ? (parseFloat(currentPosInput.value) || 50) : 50;
        bannerArea.classList.add('is-dragging');
    }, { passive: true });

    window.addEventListener('touchmove', (e) => {
        if (!isDraggingBanner || e.touches.length !== 1) return;
        const diffY = e.touches[0].clientY - bannerStartY;
        const deltaPercent = (diffY / 160) * 100;
        let newPos = bannerStartPos - deltaPercent;
        newPos = Math.max(0, Math.min(100, Math.round(newPos)));
        applyBannerPosY(newPos);
    }, { passive: true });

    window.addEventListener('touchend', () => {
        if (isDraggingBanner) {
            isDraggingBanner = false;
            const bannerArea = document.getElementById('modal-banner-area');
            if (bannerArea) bannerArea.classList.remove('is-dragging');
        }
    });
}

function onBannerSliderInput(val) {
    applyBannerPosY(val);
}

function applyBannerPosY(val) {
    const num = Math.max(0, Math.min(100, Math.round(val)));
    const bannerArea = document.getElementById('modal-banner-area');
    const input = document.getElementById('modal-banner-pos-y');
    const slider = document.getElementById('modal-banner-slider');
    const label = document.getElementById('modal-banner-pos-val');

    if (bannerArea) {
        bannerArea.style.backgroundPositionY = num + '%';
    }
    if (input) input.value = num;
    if (slider) slider.value = num;
    if (label) label.textContent = num + '%';
}

// Banner-tiedoston valinta ja esikatselu
function handleBannerSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 5 * 1024 * 1024) {
        if (typeof Toast !== 'undefined') {
            Toast.error('Bannerikuvan koko saa olla enintään 5 MB.');
        } else {
            alert('Bannerikuvan koko saa olla enintään 5 MB.');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const bannerArea = document.getElementById('modal-banner-area');
        if (bannerArea) {
            bannerArea.style.backgroundImage = `url('${e.target.result}')`;
        }
        const deleteFlag = document.getElementById('delete_banner_flag');
        if (deleteFlag) deleteFlag.value = '0';

        const delBtn = document.getElementById('modal-banner-del-btn');
        if (delBtn) delBtn.style.display = 'inline-flex';

        const dragHint = document.getElementById('modal-banner-drag-hint');
        if (dragHint) dragHint.style.display = 'inline-flex';

        const sliderBar = document.getElementById('modal-banner-slider-bar');
        if (sliderBar) sliderBar.style.display = 'inline-flex';

        applyBannerPosY(50);
    };
    reader.readAsDataURL(file);
}

// Poista banneri modalissa
function removeModalBanner() {
    const input = document.getElementById('edit-banner-input');
    if (input) input.value = '';

    const deleteFlag = document.getElementById('delete_banner_flag');
    if (deleteFlag) deleteFlag.value = '1';

    const bannerArea = document.getElementById('modal-banner-area');
    if (bannerArea) {
        bannerArea.style.backgroundImage = '';
    }

    const delBtn = document.getElementById('modal-banner-del-btn');
    if (delBtn) delBtn.style.display = 'none';

    const dragHint = document.getElementById('modal-banner-drag-hint');
    if (dragHint) dragHint.style.display = 'none';

    const sliderBar = document.getElementById('modal-banner-slider-bar');
    if (sliderBar) sliderBar.style.display = 'none';
}

// ==========================================
// 2. PROFIILIKUVAN RAJAUS & ZOOM (Cropper)
// ==========================================

let cropperState = {
    image: null,
    naturalWidth: 0,
    naturalHeight: 0,
    baseScale: 1,
    zoom: 1.15,
    panX: 0,
    panY: 0,
    isDragging: false,
    startX: 0,
    startY: 0,
    startPanX: 0,
    startPanY: 0
};

const CROP_DIAMETER = 240; // Halkaisija pikseleinä (vastaa maskia ja kehystä 240x240)

function handleAvatarSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 10 * 1024 * 1024) {
        if (typeof Toast !== 'undefined') {
            Toast.error('Profiilikuvan koko saa olla enintään 10 MB.');
        } else {
            alert('Profiilikuvan koko saa olla enintään 10 MB.');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        openAvatarCropper(e.target.result);
    };
    reader.readAsDataURL(file);
}

// Avaa nykyisen profiilikuvan rajauksen
function openAvatarCropperForCurrent() {
    const previewImg = document.getElementById('modal-avatar-img');
    if (previewImg && previewImg.src) {
        openAvatarCropper(previewImg.src);
    }
}

function openAvatarCropper(dataUrl) {
    const modal = document.getElementById('avatar-cropper-modal');
    const img = document.getElementById('avatar-cropper-img');
    const zoomSlider = document.getElementById('avatar-cropper-zoom');
    if (!modal || !img) return;

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');

    cropperState.isDragging = false;
    cropperState.panX = 0;
    cropperState.panY = 0;

    img.onload = function () {
        cropperState.image = img;
        cropperState.naturalWidth = img.naturalWidth || img.width;
        cropperState.naturalHeight = img.naturalHeight || img.height;

        cropperState.baseScale = Math.max(
            CROP_DIAMETER / cropperState.naturalWidth,
            CROP_DIAMETER / cropperState.naturalHeight
        );
        // Aloitetaan 1.15 zoomilla, jotta kuvassa on heti liikkumavaraa joka suuntaan
        cropperState.zoom = 1.15;
        cropperState.panX = 0;
        cropperState.panY = 0;

        if (zoomSlider) {
            zoomSlider.min = '1';
            zoomSlider.max = '3';
            zoomSlider.step = '0.01';
            zoomSlider.value = '1.15';
        }
        clampCropperPan();
        updateCropperTransform();
    };

    img.crossOrigin = 'anonymous';
    img.src = dataUrl;

    if (img.complete && img.naturalWidth > 0) {
        img.onload();
    }
}

function closeAvatarCropper() {
    const modal = document.getElementById('avatar-cropper-modal');
    if (modal) {
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
    }
    const input = document.getElementById('edit-avatar-input');
    const croppedData = document.getElementById('modal-avatar-cropped-data');
    if (input && (!croppedData || !croppedData.value)) {
        input.value = '';
    }
}

function onCropperZoomInput(val) {
    cropperState.zoom = Math.max(1, Math.min(3, parseFloat(val) || 1));
    clampCropperPan();
    updateCropperTransform();
}

function adjustCropperZoomStep(delta) {
    const slider = document.getElementById('avatar-cropper-zoom');
    if (!slider) return;
    let newZoom = (parseFloat(slider.value) || 1) + delta;
    newZoom = Math.max(1, Math.min(3, newZoom));
    slider.value = newZoom.toFixed(2);
    onCropperZoomInput(newZoom);
}

function clampCropperPan() {
    const scale = cropperState.baseScale * cropperState.zoom;
    const renderedW = cropperState.naturalWidth * scale;
    const renderedH = cropperState.naturalHeight * scale;

    // Vähintään 60px liikkumavara (extraLeeway) takaa, että myös 1:1 neliökuvia
    // ja 1x zoomilla olevaa kuvaa voi AINA siirtää vapaasti ja vaivattomasti.
    const extraLeeway = 60;
    const maxPanX = Math.max(extraLeeway, ((renderedW - CROP_DIAMETER) / 2) + extraLeeway);
    const maxPanY = Math.max(extraLeeway, ((renderedH - CROP_DIAMETER) / 2) + extraLeeway);

    cropperState.panX = Math.max(-maxPanX, Math.min(maxPanX, cropperState.panX));
    cropperState.panY = Math.max(-maxPanY, Math.min(maxPanY, cropperState.panY));
}

function updateCropperTransform() {
    const img = document.getElementById('avatar-cropper-img');
    if (!img) return;
    const scale = cropperState.baseScale * cropperState.zoom;
    const w = Math.round(cropperState.naturalWidth * scale);
    const h = Math.round(cropperState.naturalHeight * scale);
    img.style.width = w + 'px';
    img.style.height = h + 'px';
    img.style.transform = `translate(calc(-50% + ${Math.round(cropperState.panX)}px), calc(-50% + ${Math.round(cropperState.panY)}px))`;
}

function initAvatarCropperEvents() {
    const stage = document.getElementById('avatar-cropper-stage');
    if (!stage) return;
    if (stage.dataset.cropperInitialized === 'true') return;
    stage.dataset.cropperInitialized = 'true';

    // Mouse drag
    stage.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return;
        cropperState.isDragging = true;
        cropperState.startX = e.clientX;
        cropperState.startY = e.clientY;
        cropperState.startPanX = cropperState.panX;
        cropperState.startPanY = cropperState.panY;
        stage.classList.add('is-dragging');
        e.preventDefault();
    });

    window.addEventListener('mousemove', (e) => {
        if (!cropperState.isDragging) return;
        const dx = e.clientX - cropperState.startX;
        const dy = e.clientY - cropperState.startY;
        cropperState.panX = cropperState.startPanX + dx;
        cropperState.panY = cropperState.startPanY + dy;
        clampCropperPan();
        updateCropperTransform();
    });

    window.addEventListener('mouseup', () => {
        if (cropperState.isDragging) {
            cropperState.isDragging = false;
            const s = document.getElementById('avatar-cropper-stage');
            if (s) s.classList.remove('is-dragging');
        }
    });

    // Touch drag & Pinch zoom (mobiili)
    let touchStartDist = 0;
    let touchStartZoom = 1;

    stage.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
            cropperState.isDragging = true;
            cropperState.startX = e.touches[0].clientX;
            cropperState.startY = e.touches[0].clientY;
            cropperState.startPanX = cropperState.panX;
            cropperState.startPanY = cropperState.panY;
            stage.classList.add('is-dragging');
        } else if (e.touches.length === 2) {
            cropperState.isDragging = false;
            touchStartDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            touchStartZoom = cropperState.zoom;
        }
    }, { passive: false });

    window.addEventListener('touchmove', (e) => {
        if (cropperState.isDragging && e.touches.length === 1) {
            const dx = e.touches[0].clientX - cropperState.startX;
            const dy = e.touches[0].clientY - cropperState.startY;
            cropperState.panX = cropperState.startPanX + dx;
            cropperState.panY = cropperState.startPanY + dy;
            clampCropperPan();
            updateCropperTransform();
            e.preventDefault();
        } else if (e.touches.length === 2 && touchStartDist > 0) {
            const currentDist = Math.hypot(
                e.touches[0].clientX - e.touches[1].clientX,
                e.touches[0].clientY - e.touches[1].clientY
            );
            const factor = currentDist / touchStartDist;
            let newZoom = Math.max(1, Math.min(3, touchStartZoom * factor));
            const slider = document.getElementById('avatar-cropper-zoom');
            if (slider) slider.value = newZoom.toFixed(2);
            cropperState.zoom = newZoom;
            clampCropperPan();
            updateCropperTransform();
            e.preventDefault();
        }
    }, { passive: false });

    window.addEventListener('touchend', (e) => {
        if (e.touches.length === 0) {
            cropperState.isDragging = false;
            touchStartDist = 0;
            const s = document.getElementById('avatar-cropper-stage');
            if (s) s.classList.remove('is-dragging');
        }
    });

    // Wheel zoom
    stage.addEventListener('wheel', (e) => {
        e.preventDefault();
        const delta = e.deltaY < 0 ? 0.08 : -0.08;
        adjustCropperZoomStep(delta);
    }, { passive: false });
}

function applyAvatarCrop() {
    if (!cropperState.image) return;

    const scale = cropperState.baseScale * cropperState.zoom;
    const CROP_OUTPUT_SIZE = 400; // 400x400 px korkealaatuinen tulos

    const canvas = document.createElement('canvas');
    canvas.width = CROP_OUTPUT_SIZE;
    canvas.height = CROP_OUTPUT_SIZE;
    const ctx = canvas.getContext('2d');

    ctx.imageSmoothingEnabled = true;
    ctx.imageSmoothingQuality = 'high';
    ctx.clearRect(0, 0, CROP_OUTPUT_SIZE, CROP_OUTPUT_SIZE);

    // Tarkka WYSIWYG-skaalaus: Cropper-kehästä (240px) suoraan 400x400 canvasille
    const ratio = CROP_OUTPUT_SIZE / CROP_DIAMETER;
    const canvasImgW = cropperState.naturalWidth * scale * ratio;
    const canvasImgH = cropperState.naturalHeight * scale * ratio;

    const destCenterX = (CROP_OUTPUT_SIZE / 2) + (cropperState.panX * ratio);
    const destCenterY = (CROP_OUTPUT_SIZE / 2) + (cropperState.panY * ratio);

    const destX = destCenterX - (canvasImgW / 2);
    const destY = destCenterY - (canvasImgH / 2);

    ctx.drawImage(cropperState.image, destX, destY, canvasImgW, canvasImgH);

    const croppedDataUrl = canvas.toDataURL('image/png', 0.95);

    const hiddenData = document.getElementById('modal-avatar-cropped-data');
    if (hiddenData) hiddenData.value = croppedDataUrl;

    const previewContainer = document.getElementById('modal-avatar-preview-container');
    if (previewContainer) {
        previewContainer.innerHTML = `<img src="${croppedDataUrl}" alt="Avatar" class="modal-avatar-img" id="modal-avatar-img">`;
    }

    const deleteFlag = document.getElementById('delete_avatar_flag');
    if (deleteFlag) deleteFlag.value = '0';

    const delBtn = document.getElementById('modal-avatar-del-btn');
    if (delBtn) delBtn.style.display = 'inline-flex';

    const recropBtn = document.getElementById('modal-avatar-recrop-btn');
    if (recropBtn) recropBtn.style.display = 'inline-flex';

    closeAvatarCropper();
}

// Poista avatar modalissa
function removeModalAvatar() {
    const input = document.getElementById('edit-avatar-input');
    if (input) input.value = '';

    const croppedData = document.getElementById('modal-avatar-cropped-data');
    if (croppedData) croppedData.value = '';

    const deleteFlag = document.getElementById('delete_avatar_flag');
    if (deleteFlag) deleteFlag.value = '1';

    const container = document.getElementById('modal-avatar-preview-container');
    const fallbackText = container?.getAttribute('data-fallback') || 'U';
    if (container) {
        container.innerHTML = `<span class="modal-avatar-fallback">${fallbackText}</span>`;
    }

    const delBtn = document.getElementById('modal-avatar-del-btn');
    if (delBtn) delBtn.style.display = 'none';

    const recropBtn = document.getElementById('modal-avatar-recrop-btn');
    if (recropBtn) recropBtn.style.display = 'none';
}

// ==========================================
// 3. MUSIIKKIHAKU MODALISSA (Facebook-Style)
// ==========================================

function quickSearchModalMusic(term) {
    const input = document.getElementById('modal-music-search-input');
    if (input) {
        input.value = term;
        doModalMusicSearch();
    }
}

function doModalMusicSearch() {
    const input = document.getElementById('modal-music-search-input');
    const resultsBox = document.getElementById('modal-music-results');
    const loading = document.getElementById('modal-music-loading');

    if (!input || !resultsBox) return;
    const term = input.value.trim();
    if (!term) return;

    stopModalAudioPreview();

    if (loading) loading.style.display = 'block';
    resultsBox.style.display = 'none';
    resultsBox.innerHTML = '';

    fetch('api/search-music.php?term=' + encodeURIComponent(term))
        .then(res => res.json())
        .then(data => {
            if (loading) loading.style.display = 'none';
            resultsBox.style.display = 'flex';

            if (!data.success || !data.results || data.results.length === 0) {
                resultsBox.innerHTML = '<div style="padding: 10px; text-align: center; color: #71767b; font-size: 12px;">Ei löytynyt kappaleita hakusanalla "' + escapeHtml(term) + '".</div>';
                return;
            }

            let html = '';
            data.results.forEach(song => {
                const songJson = encodeURIComponent(JSON.stringify(song));
                html += `
                    <div class="music-result-item">
                        <img src="${escapeHtml(song.artwork)}" alt="Kansi" class="music-result-art">
                        <div class="music-result-info">
                            <span class="music-result-title">${escapeHtml(song.title)}</span>
                            <span class="music-result-artist">${escapeHtml(song.artist)}</span>
                        </div>
                        <div class="music-result-actions">
                            <button type="button" class="btn-music-preview" onclick="toggleModalAudioPreview('${escapeHtml(song.preview_url)}', this)">▶ Kuuntele</button>
                            <button type="button" class="btn-music-select" onclick="selectModalMusicTrack('${songJson}')">Valitse</button>
                        </div>
                    </div>
                `;
            });
            resultsBox.innerHTML = html;
        })
        .catch(err => {
            if (loading) loading.style.display = 'none';
            resultsBox.style.display = 'block';
            resultsBox.innerHTML = '<div style="padding: 10px; text-align: center; color: #f4212e; font-size: 12px;">Haku epäonnistui.</div>';
            console.error('Modal music search error:', err);
        });
}

function toggleModalAudioPreview(url, btn) {
    if (activeModalPreviewAudio && activeModalPreviewAudio.src === url && !activeModalPreviewAudio.paused) {
        stopModalAudioPreview();
        return;
    }

    stopModalAudioPreview();

    const audio = new Audio(url);
    activeModalPreviewAudio = audio;
    activeModalPreviewBtn = btn;
    btn.textContent = '❚❚ Pysäytä';
    btn.style.color = '#ff007f';
    btn.style.borderColor = '#ff007f';

    audio.play().catch(() => {
        stopModalAudioPreview();
    });

    audio.onended = () => {
        stopModalAudioPreview();
    };
}

function stopModalAudioPreview() {
    if (activeModalPreviewAudio) {
        activeModalPreviewAudio.pause();
        activeModalPreviewAudio = null;
    }
    if (activeModalPreviewBtn) {
        activeModalPreviewBtn.textContent = '▶ Kuuntele';
        activeModalPreviewBtn.style.color = '';
        activeModalPreviewBtn.style.borderColor = '';
        activeModalPreviewBtn = null;
    }
}

function selectModalMusicTrack(encodedJson) {
    stopModalAudioPreview();
    try {
        const song = JSON.parse(decodeURIComponent(encodedJson));

        const titleInput = document.getElementById('modal-song-title');
        const artistInput = document.getElementById('modal-song-artist');
        const artworkInput = document.getElementById('modal-song-artwork');
        const urlInput = document.getElementById('modal-song-url');
        const deleteSongFlag = document.getElementById('delete_song_flag');

        if (titleInput) titleInput.value = song.title;
        if (artistInput) artistInput.value = song.artist;
        if (artworkInput) artworkInput.value = song.artwork;
        if (urlInput) urlInput.value = song.preview_url;
        if (deleteSongFlag) deleteSongFlag.value = '0';

        const selectedContainer = document.getElementById('modal-selected-song-container');
        const selectedArt = document.getElementById('modal-selected-song-art');
        const selectedTitle = document.getElementById('modal-selected-song-title');
        const selectedArtist = document.getElementById('modal-selected-song-artist');

        if (selectedContainer) selectedContainer.style.display = 'flex';
        if (selectedArt) {
            selectedArt.src = song.artwork;
            selectedArt.style.display = song.artwork ? 'block' : 'none';
        }
        if (selectedTitle) selectedTitle.textContent = song.title;
        if (selectedArtist) selectedArtist.textContent = song.artist;

        const resultsBox = document.getElementById('modal-music-results');
        if (resultsBox) resultsBox.style.display = 'none';
    } catch (e) {
        console.error('Error selecting track:', e);
    }
}

function removeModalSelectedSong() {
    stopModalAudioPreview();

    const titleInput = document.getElementById('modal-song-title');
    const artistInput = document.getElementById('modal-song-artist');
    const artworkInput = document.getElementById('modal-song-artwork');
    const urlInput = document.getElementById('modal-song-url');
    const deleteSongFlag = document.getElementById('delete_song_flag');

    if (titleInput) titleInput.value = '';
    if (artistInput) artistInput.value = '';
    if (artworkInput) artworkInput.value = '';
    if (urlInput) urlInput.value = '';
    if (deleteSongFlag) deleteSongFlag.value = '1';

    const selectedContainer = document.getElementById('modal-selected-song-container');
    if (selectedContainer) selectedContainer.style.display = 'none';
}

// ==========================================
// 4. TEEMAN KOROSTUSVÄRI MODALISSA
// ==========================================

function selectModalAccent(hexColor, btn) {
    if (!hexColor) return;
    const input = document.getElementById('modal-theme-accent-input');
    const picker = document.getElementById('modal-theme-accent-picker');

    if (input) input.value = hexColor;
    if (picker) picker.value = hexColor;

    document.querySelectorAll('.modal-color-swatch').forEach(b => b.classList.remove('active'));
    if (btn) {
        btn.classList.add('active');
    } else {
        const matchingBtn = Array.from(document.querySelectorAll('.modal-color-swatch')).find(b => b.getAttribute('data-color')?.toLowerCase() === hexColor.toLowerCase());
        if (matchingBtn) {
            matchingBtn.classList.add('active');
        }
    }

    document.documentElement.style.setProperty('--profile-accent', hexColor);
    if (/^#[0-9A-Fa-f]{6}$/.test(hexColor)) {
        const r = parseInt(hexColor.slice(1, 3), 16);
        const g = parseInt(hexColor.slice(3, 5), 16);
        const b = parseInt(hexColor.slice(5, 7), 16);
        document.documentElement.style.setProperty('--profile-accent-rgb', `${r}, ${g}, ${b}`);
    }
}

// ==========================================
// 5. TAUSTATEEMAN JA KUVAN HALLINTA (Background)
// ==========================================

function selectModalBackground(bgId, cssStyle, val) {
    const typeInput = document.getElementById('modal-theme-bg-type');
    const valInput = document.getElementById('modal-theme-bg-val');
    const deleteFlag = document.getElementById('delete_bg_flag');
    const customCard = document.getElementById('modal-bg-custom-preview-card');
    const resetBtn = document.getElementById('modal-bg-reset-btn');
    const fileInput = document.getElementById('modal-bg-file');

    if (typeInput) typeInput.value = bgId;
    if (valInput) valInput.value = val || '';
    if (deleteFlag) deleteFlag.value = '0';
    if (fileInput) fileInput.value = '';

    // Päivitetään aktiivinen luokka preset-korteille
    document.querySelectorAll('.modal-bg-card').forEach(c => {
        if (c.getAttribute('data-bg-id') === bgId) {
            c.classList.add('active');
        } else {
            c.classList.remove('active');
        }
    });

    if (customCard) customCard.classList.remove('active');
    if (resetBtn) resetBtn.style.display = (bgId === 'default') ? 'none' : 'inline-block';

    // Live Preview sivun taustalle
    applyLiveBackground(cssStyle);
}

function handleCustomBgSelect(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];

    if (file.size > 8 * 1024 * 1024) {
        if (typeof Toast !== 'undefined') {
            Toast.error('Taustakuvan koko saa olla enintään 8 MB.');
        } else {
            alert('Taustakuvan koko saa olla enintään 8 MB.');
        }
        input.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const dataUrl = e.target.result;

        const typeInput = document.getElementById('modal-theme-bg-type');
        const valInput = document.getElementById('modal-theme-bg-val');
        const deleteFlag = document.getElementById('delete_bg_flag');
        const customCard = document.getElementById('modal-bg-custom-preview-card');
        const customThumb = document.getElementById('modal-bg-custom-thumb');
        const resetBtn = document.getElementById('modal-bg-reset-btn');

        if (typeInput) typeInput.value = 'custom_image';
        if (valInput) valInput.value = '';
        if (deleteFlag) deleteFlag.value = '0';

        // Poistetaan valinnat esiasetetuista
        document.querySelectorAll('.modal-bg-card').forEach(c => c.classList.remove('active'));

        // Näytetään oman kuvan kortti aktiivisena
        if (customThumb) customThumb.src = dataUrl;
        if (customCard) {
            customCard.style.display = 'flex';
            customCard.classList.add('active');
        }
        if (resetBtn) resetBtn.style.display = 'inline-block';

        // Live Preview sivun taustalle
        const css = `background-color: #000000; background-image: url('${dataUrl}'); background-size: cover; background-position: center; background-attachment: fixed; background-repeat: no-repeat;`;
        applyLiveBackground(css);
    };
    reader.readAsDataURL(file);
}

function removeModalCustomBg() {
    const fileInput = document.getElementById('modal-bg-file');
    if (fileInput) fileInput.value = '';

    const typeInput = document.getElementById('modal-theme-bg-type');
    const valInput = document.getElementById('modal-theme-bg-val');
    const deleteFlag = document.getElementById('delete_bg_flag');
    const customCard = document.getElementById('modal-bg-custom-preview-card');
    const resetBtn = document.getElementById('modal-bg-reset-btn');

    if (typeInput) typeInput.value = 'default';
    if (valInput) valInput.value = '';
    if (deleteFlag) deleteFlag.value = '1';

    if (customCard) {
        customCard.style.display = 'none';
        customCard.classList.remove('active');
    }

    // Asetetaan oletuskortti aktiiviseksi
    document.querySelectorAll('.modal-bg-card').forEach(c => {
        if (c.getAttribute('data-bg-id') === 'default') {
            c.classList.add('active');
        } else {
            c.classList.remove('active');
        }
    });

    if (resetBtn) resetBtn.style.display = 'none';

    // Palautetaan oletustausta
    applyLiveBackground('background-color: #000000; background-image: none;');
}

function applyLiveBackground(cssText) {
    let liveStyleEl = document.getElementById('live-preview-bg-style');
    if (!liveStyleEl) {
        liveStyleEl = document.createElement('style');
        liveStyleEl.id = 'live-preview-bg-style';
        document.head.appendChild(liveStyleEl);
    }
    if (!cssText || cssText === 'background-color: #000000; background-image: none;') {
        liveStyleEl.innerHTML = `
            html, body {
                background-color: #000000 !important;
                background-image: none !important;
            }
        `;
    } else {
        liveStyleEl.innerHTML = `
            html, body {
                ${cssText}
            }
            .feed {
                background-color: rgba(0, 0, 0, 0.85) !important;
                backdrop-filter: blur(12px) !important;
                -webkit-backdrop-filter: blur(12px) !important;
            }
        `;
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Alustukset kun sivu latautuu
document.addEventListener('DOMContentLoaded', () => {
    initBannerDrag();
    initAvatarCropperEvents();
});

// Suoritetaan myös heti siltä varalta että DOM oli jo valmis
initBannerDrag();
initAvatarCropperEvents();
