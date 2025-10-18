<div x-data="memberEditModal" x-show="$store.modals.editModal.type === 'member'">
    <div class="modal-header">
        <h5 class="modal-title" x-text="member.firstname ? (member.firstname + ' ' + member.lastname) : ''"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <form id="member-edit-form" @submit.prevent="updateMember">
            <div class="mb-3">
                <label for="memberName" class="form-label">First Name</label>
                <input type="text" id="memberName" name="name" :value="member.firstname" class="form-control" data-testid="member-firstname-input">
            </div>

            <div class="mb-3">
                <label for="memberLastName" class="form-label">Last Name</label>
                <input type="text" id="memberLastName" name="lastname" :value="member.lastname" class="form-control" data-testid="member-lastname-input">
            </div>

            <div class="mb-3">
                <label for="bic" class="form-label">BIC</label>
                <input type="text" id="bic" name="bic" :value="member.bic" class="form-control" data-testid="member-bic-input">
            </div>

            <div class="mb-3">
                <label for="iban" class="form-label">IBAN</label>
                <input type="text" id="iban" name="iban" :value="member.iban" class="form-control" data-testid="member-iban-input">
            </div>

            <div class="mb-3">
                <label class="form-check form-switch">
                    <input type="checkbox" :checked="member.had_collection" id="had_collection" name="had_collection" class="form-check-input" data-testid="member-had-collection-checkbox">
                    <span class="form-check-label">Had Collection</span>
                </label>
            </div>

            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-danger" @click="deleteMember" :disabled="isLoading" data-testid="member-delete-button">
            <i data-lucide="trash-2"></i>
            <span x-text="isLoading ? 'Deleting...' : 'Delete Member'"></span>
        </button>
        <button type="submit" class="btn btn-primary" form="member-edit-form" :disabled="isLoading" data-testid="member-save-button">
            <i data-lucide="save"></i>
            <span x-text="isLoading ? 'Saving...' : 'Save Changes'"></span>
        </button>
    </div>
</div>
