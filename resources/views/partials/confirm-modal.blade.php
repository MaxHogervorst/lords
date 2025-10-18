<!-- Confirmation Modal -->
<div class="modal fade" id="confirm-modal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true"
    x-data="confirmModal">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel" x-text="title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p x-text="message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal" @click="handleCancel()"
                    x-text="cancelText"></button>
                <button type="button" class="btn" :class="isDangerous ? 'btn-danger' : 'btn-primary'"
                    @click="handleConfirm()" data-testid="confirm-action-button" x-text="confirmText"></button>
            </div>
        </div>
    </div>
</div>
