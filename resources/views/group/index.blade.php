@extends('layout.master')

@section('content')

<div x-data='groupsManager(@json($results[0]))' x-cloak>
    <!-- Search and Add Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form x-ref="addGroupForm" @submit.prevent="addGroup" action="{{ url('group') }}" method="post">
                <div class="row g-2">
                    <div class="col">
                        <input
                            type="text"
                            x-ref="searchInput"
                            id="filter"
                            name="name"
                            @input="searchQuery = $event.target.value"
                            placeholder="Search or Add"
                            class="form-control"
                            autofocus
                            autocomplete="off">
                    </div>
                    <div class="col">
                        <input
                            type="text"
                            x-ref="groupDatePicker"
                            id="groupDate"
                            name="groupdate"
                            autocomplete="off"
                            placeholder="Group Date"
                            class="form-control"
                            value="{{ $results[1] }}">
                    </div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="col-auto">
                        <button
                            type="submit"
                            class="btn btn-primary"
                            data-testid="add-group-button"
                            :disabled="$store.app.isLoading">
                            <i data-lucide="plus"></i>
                            <span x-text="$store.app.isLoading ? 'Adding...' : 'Add Group'"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="members">
                <thead>
                    <tr>
                        <th>Group Name</th>
                        <th style="width: 120px;">Created</th>
                        <th class="w-1">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <template x-for="group in filteredGroups" :key="group.id">
                        <tr>
                            <td x-text="group.name"></td>
                            <td class="text-muted" x-text="group.created_at"></td>
                            <td class="text-nowrap">
                                <button
                                    type="button"
                                    class="btn btn-sm btn-ghost-primary"
                                    :data-testid="'group-order-' + group.id"
                                    :data-id="group.id"
                                    @click="loadOrderModal(group.id)">
                                    <i data-lucide="plus"></i>
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-ghost-primary"
                                    :data-testid="'group-edit-' + group.id"
                                    :data-id="group.id"
                                    @click="loadEditModal(group.id)">
                                    <i data-lucide="edit"></i>
                                </button>
                            </td>
                        </tr>
                    </template>

                    <template x-if="filteredGroups.length === 0">
                        <tr>
                            <td colspan="3" class="text-center text-muted">No groups found</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

@stop

@section('modal')
<div class="modal modal-blur fade" id="member-order" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            @include('group.order')
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="group-edit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @include('group.edit')
        </div>
    </div>
</div>
@stop
