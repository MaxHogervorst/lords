<div x-data="groupEditModal" x-show="$store.modals.editModal.type === 'group'">
    <div class="modal-header">
        <h5 class="modal-title" x-text="group.name"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>

    <div class="modal-body">
        <form id="group-edit-form" @submit.prevent="updateGroup">
            <div class="mb-3">
                <label for="groupName" class="form-label">Group Name</label>
                <input type="text" id="groupName" name="name" :value="group.name" class="form-control" data-testid="group-name-input">
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-danger" @click="deleteGroup" :disabled="isLoading" data-testid="group-delete-button">
            <i data-lucide="trash-2"></i>
            <span x-text="isLoading ? 'Deleting...' : 'Delete Group'"></span>
        </button>
        <button type="submit" class="btn btn-primary" form="group-edit-form" :disabled="isLoading" data-testid="group-save-button">
            <i data-lucide="save"></i>
            <span x-text="isLoading ? 'Saving...' : 'Save Changes'"></span>
        </button>
    </div>
</div>
