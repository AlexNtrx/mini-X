<?php
/**
 * Mini-X - Avatar Circle Cropper Modal Component
 */
?>
<!-- Avatar Circle Cropper Modal Dialog -->
<div id="avatar-cropper-modal" class="avatar-cropper-overlay" style="display: none;" role="dialog" aria-modal="true">
    <div class="avatar-cropper-card">
        <div class="avatar-cropper-header">
            <h3 class="avatar-cropper-title">Rajaa profiilikuva</h3>
            <button type="button" class="avatar-cropper-close" onclick="closeAvatarCropper()" aria-label="Sulje">&times;</button>
        </div>
        
        <div class="avatar-cropper-body">
            <div class="avatar-cropper-stage" id="avatar-cropper-stage">
                <img id="avatar-cropper-img" src="" alt="Kuva rajattavaksi" draggable="false">
                <div class="avatar-cropper-mask"></div>
                <div class="avatar-cropper-ring"></div>
            </div>

            <div class="avatar-cropper-zoom-row">
                <button type="button" class="cropper-zoom-btn" onclick="adjustCropperZoomStep(-0.15)" title="Pienennä">−</button>
                <input type="range" id="avatar-cropper-zoom" min="1" max="3" step="0.01" value="1.15" oninput="onCropperZoomInput(this.value)">
                <button type="button" class="cropper-zoom-btn" onclick="adjustCropperZoomStep(0.15)" title="Suurenna">+</button>
            </div>
        </div>

        <div class="avatar-cropper-footer">
            <button type="button" class="cropper-btn-cancel" onclick="closeAvatarCropper()">Peruuta</button>
            <button type="button" class="cropper-btn-apply" onclick="applyAvatarCrop()">Käytä kuvaa</button>
        </div>
    </div>
</div>
