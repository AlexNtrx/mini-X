<!-- Report Modal -->
<div class="report-modal-overlay" id="reportModalOverlay" style="display: none;" onclick="closeReportModal(event)">
    <div class="report-modal-card" onclick="event.stopPropagation()">
        <div class="report-modal-header">
            <h3 class="report-modal-title">🚨 Ilmianna sisältö</h3>
            <button type="button" class="report-modal-close" onclick="closeReportModal()">&times;</button>
        </div>
        <form method="POST" class="report-modal-form" id="reportModalForm">
            <input type="hidden" name="submit_report" value="1">
            <input type="hidden" name="post_id" id="report-post-id" value="">
            <input type="hidden" name="comment_id" id="report-comment-id" value="">

            <div class="report-form-group">
                <label for="report-reason" class="report-label">Syy ilmiantoon:</label>
                <select name="reason" id="report-reason" class="report-select" required>
                    <option value="hate_speech">Häirintä tai vihapuhe</option>
                    <option value="inappropriate">Sopimaton tai loukkaava sisältö</option>
                    <option value="spam">Roskaposti tai mainonta</option>
                    <option value="misinformation">Harhaanjohtava tieto</option>
                    <option value="other">Muu syy</option>
                </select>
            </div>

            <div class="report-form-group">
                <label for="report-details" class="report-label">Lisätiedot (vapaaehtoinen):</label>
                <textarea name="details" id="report-details" class="report-textarea" rows="3" placeholder="Kerro lyhyesti miksi tämä sisältö tulisi poistaa..."></textarea>
            </div>

            <div class="report-modal-actions">
                <button type="button" class="btn-report-cancel" onclick="closeReportModal()">Peruuta</button>
                <button type="submit" class="btn-report-submit">Lähetä ilmianto</button>
            </div>
        </form>
    </div>
</div>

<style>
.report-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(4px);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px;
}
.report-modal-card {
    background: #16181c;
    border: 1px solid #2f3336;
    border-radius: 16px;
    width: 100%;
    max-width: 440px;
    padding: 24px;
    color: #e7e9ea;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    animation: reportFadeIn 0.2s ease-out;
}
@keyframes reportFadeIn {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}
.report-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.report-modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
}
.report-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #71767b;
    cursor: pointer;
    line-height: 1;
}
.report-modal-close:hover {
    color: #e7e9ea;
}
.report-form-group {
    margin-bottom: 16px;
}
.report-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #71767b;
    margin-bottom: 6px;
}
.report-select, .report-textarea {
    width: 100%;
    box-sizing: border-box;
    background: #202327;
    border: 1px solid #333639;
    border-radius: 8px;
    padding: 10px 12px;
    color: #e7e9ea;
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: border-color 0.2s;
}
.report-select:focus, .report-textarea:focus {
    border-color: #1d9bf0;
}
.report-textarea {
    resize: vertical;
}
.report-modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}
.btn-report-cancel {
    padding: 8px 16px;
    background: transparent;
    border: 1px solid #536471;
    border-radius: 9999px;
    color: #e7e9ea;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
}
.btn-report-cancel:hover {
    background: rgba(239, 243, 244, 0.1);
}
.btn-report-submit {
    padding: 8px 18px;
    background: #f4212e;
    border: none;
    border-radius: 9999px;
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
}
.btn-report-submit:hover {
    background: #dc1e29;
}
</style>

<script>
function openReportModal(targetId, type) {
    const overlay = document.getElementById('reportModalOverlay');
    const postInput = document.getElementById('report-post-id');
    const commentInput = document.getElementById('report-comment-id');
    if (!overlay) return;

    if (type === 'comment') {
        if (commentInput) commentInput.value = targetId;
        if (postInput) postInput.value = '';
    } else {
        if (postInput) postInput.value = targetId;
        if (commentInput) commentInput.value = '';
    }

    overlay.style.display = 'flex';
}

function closeReportModal(e) {
    if (e && e.target !== e.currentTarget && !e.target.classList.contains('report-modal-close') && !e.target.classList.contains('btn-report-cancel')) {
        return;
    }
    const overlay = document.getElementById('reportModalOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}
</script>
