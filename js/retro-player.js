/**
 * Mini-X - Instagram Story Style Music Sticker Script
 * Hallinnoi modernin musiikkitarran toistoa, soundwave-animaatiota, play/pause-painiketta ja edistymispalkkia.
 */
document.addEventListener('DOMContentLoaded', () => {
    const player = document.getElementById('retro-music-player');
    const audio = document.getElementById('hi5-audio-element');
    if (!player || !audio) return;

    const playBtn = document.getElementById('ig-music-play-btn');
    const playIcon = player.querySelector('.ig-btn-icon-play, .ig-icon-play');
    const pauseIcon = player.querySelector('.ig-btn-icon-pause, .ig-icon-pause');
    const progressBar = player.querySelector('.ig-music-progress-bar');
    const progressFill = document.getElementById('retro-scrubber-fill');

    let isPlaying = false;

    // Päivittää soittimen UI-tilan
    function updateUI(playing) {
        isPlaying = playing;
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
        if (audio.paused) {
            audio.play().then(() => {
                updateUI(true);
            }).catch((err) => {
                console.warn('Playback prevented or audio error:', err);
                updateUI(false);
            });
        } else {
            audio.pause();
            updateUI(false);
        }
    }

    // Erillinen Play / Pause -painike (toisto toimii vain tästä painikkeesta)
    if (playBtn) {
        playBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePlay();
        });
    }

    // Edistymispalkin klikkaus (Seek)
    if (progressBar) {
        progressBar.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!audio.duration || isNaN(audio.duration)) return;
            const rect = progressBar.getBoundingClientRect();
            const clickX = e.clientX - rect.left;
            const pct = Math.max(0, Math.min(1, clickX / rect.width));
            audio.currentTime = pct * audio.duration;
            if (progressFill) {
                progressFill.style.width = `${pct * 100}%`;
            }
        });
    }

    // Äänen aikatiedot ja edistymispalkin päivitys
    audio.addEventListener('timeupdate', () => {
        if (progressFill && audio.duration && !isNaN(audio.duration) && audio.duration > 0) {
            const pct = (audio.currentTime / audio.duration) * 100;
            progressFill.style.width = `${pct}%`;
        }
    });

    // Äänitapahtumat
    audio.addEventListener('play', () => updateUI(true));
    audio.addEventListener('pause', () => updateUI(false));
    audio.addEventListener('ended', () => {
        // Luuppaa kappale automaattisesti taustamusiikkina
        audio.currentTime = 0;
        audio.play().then(() => updateUI(true)).catch(() => updateUI(false));
    });
});
