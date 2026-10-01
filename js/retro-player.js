/**
 * Mini-X - Instagram Story Style Music Sticker Script
 * Hallinnoi modernin musiikkitarran toistoa, soundwave-animaatiota, play/pause-painiketta ja edistymispalkkia.
 */

let activeRetroAudio = null;

function initRetroPlayer() {
    const player = document.getElementById('retro-music-player');
    const audio = document.getElementById('hi5-audio-element');

    // Jos aiempi ääni soi ja elementti vaihtui DOMissa, pysäytä se
    if (activeRetroAudio && activeRetroAudio !== audio) {
        try {
            activeRetroAudio.pause();
        } catch (e) {}
        activeRetroAudio = null;
    }

    if (!player || !audio) return;
    activeRetroAudio = audio;

    const playBtn = document.getElementById('ig-music-play-btn');
    const playIcon = player.querySelector('.ig-btn-icon-play, .ig-icon-play');
    const pauseIcon = player.querySelector('.ig-btn-icon-pause, .ig-icon-pause');
    const progressBar = player.querySelector('.ig-music-progress-bar');
    const progressFill = document.getElementById('retro-scrubber-fill');

    // Päivittää soittimen UI-tilan
    function updateUI(playing) {
        if (playing) {
            player.classList.add('is-playing');
            if (playIcon) playIcon.style.display = 'none';
            if (pauseIcon) pauseIcon.style.display = 'block';
            player.setAttribute('aria-label', 'Pysäytä musiikki');
            player.setAttribute('title', 'Pysäytä musiikki');
            if (playBtn) {
                playBtn.setAttribute('aria-label', 'Pysäytä musiikki');
                playBtn.setAttribute('title', 'Pysäytä musiikki');
            }
        } else {
            player.classList.remove('is-playing');
            if (playIcon) playIcon.style.display = 'block';
            if (pauseIcon) pauseIcon.style.display = 'none';
            player.setAttribute('aria-label', 'Soita musiikki');
            player.setAttribute('title', 'Soita musiikki');
            if (playBtn) {
                playBtn.setAttribute('aria-label', 'Soita musiikki');
                playBtn.setAttribute('title', 'Soita musiikki');
            }
        }
    }

    // Toggle Play / Pause
    function togglePlay() {
        if (!audio) return;
        if (audio.paused) {
            // Jos src puuttuu suoraan audio-elementiltä, tarkistetaan source-tagi
            if (!audio.src && audio.querySelector('source')) {
                audio.src = audio.querySelector('source').src;
            }
            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(() => {
                    updateUI(true);
                }).catch((err) => {
                    console.warn('Playback prevented or audio error:', err);
                    updateUI(false);
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Musiikin toisto estettiin tai tiedostoa ei voitu toistaa.');
                    }
                });
            }
        } else {
            audio.pause();
            updateUI(false);
        }
    }

    // Erillinen Play / Pause -painike
    if (playBtn) {
        playBtn.onclick = (e) => {
            e.stopPropagation();
            togglePlay();
        };
    }

    // Klikkaus tarran mihin tahansa kohtaan (Instagram-tyyliin)
    player.onclick = (e) => {
        if (e.target.closest('.ig-music-progress-bar')) return;
        togglePlay();
    };

    // Edistymispalkin klikkaus (Seek)
    if (progressBar) {
        progressBar.onclick = (e) => {
            e.stopPropagation();
            if (!audio.duration || isNaN(audio.duration)) return;
            const rect = progressBar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const pct = Math.max(0, Math.min(1, clickX / rect.width));
            audio.currentTime = pct * audio.duration;
            if (progressFill) {
                progressFill.style.width = `${pct * 100}%`;
            }
        };
    }

    // Äänen aikatiedot ja edistymispalkin päivitys
    audio.ontimeupdate = () => {
        if (progressFill && audio.duration && !isNaN(audio.duration) && audio.duration > 0) {
            const pct = (audio.currentTime / audio.duration) * 100;
            progressFill.style.width = `${pct}%`;
        }
    };

    // Äänitapahtumat
    audio.onplay = () => updateUI(true);
    audio.onpause = () => updateUI(false);
    audio.onended = () => {
        // Luuppaa kappale automaattisesti taustamusiikkina
        audio.currentTime = 0;
        const p = audio.play();
        if (p !== undefined) {
            p.then(() => updateUI(true)).catch(() => updateUI(false));
        }
    };
    audio.onerror = () => {
        console.warn('Audio playback error on element:', audio.error);
        updateUI(false);
    };

    // Alusta UI oikeaan tilaan
    updateUI(!audio.paused && audio.currentTime > 0);
}

// Globaalisti saataville AJAX-päivityksiä ja muita skriptejä varten
window.initRetroPlayer = initRetroPlayer;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRetroPlayer);
} else {
    initRetroPlayer();
}
