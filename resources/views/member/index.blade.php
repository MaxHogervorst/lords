@extends('layout.master')

@section('content')
    <div x-data='membersManager(@json($members))' x-cloak>
        <!-- Search and Add Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form x-ref="addMemberForm" @submit.prevent="addMember" action="{{ url('member') }}" method="post">
                    <div class="row g-2">
                        <div class="col">
                            <input
                                type="text"
                                x-ref="firstNameSearch"
                                name="name"
                                placeholder="First Name"
                                class="form-control"
                                autofocus
                                autocomplete="off"
                                @input="searchFirstName = $event.target.value">
                        </div>
                        <div class="col">
                            <input
                                type="text"
                                name="lastname"
                                placeholder="Last Name"
                                class="form-control"
                                autocomplete="off"
                                @input="searchLastName = $event.target.value">
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <div class="col-auto">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                data-testid="add-member-button"
                                :disabled="$store.app.isLoading">
                                <i data-lucide="plus"></i>
                                <span x-text="$store.app.isLoading ? 'Adding...' : 'Add Member'"></span>
                            </button>
                        </div>
                    </div>

                    @can('admin')
                    <div class="row mt-3">
                        <div class="col-auto">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" x-model="filterBankInfo">
                                <span class="form-check-label">Filter Bankinfo</span>
                            </label>
                        </div>
                        <div class="col-auto">
                            <label class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" x-model="filterCollection">
                                <span class="form-check-label">Filter Had Collection</span>
                            </label>
                        </div>
                    </div>
                    @endcan
                </form>
            </div>
        </div>

        <!-- Members Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-vcenter card-table">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th class="w-1">Actions</th>
                            @can('admin')
                                <th>BIC</th>
                                <th>Iban</th>
                                <th class="w-1">Had Collection</th>
                            @endcan
                        </tr>
                    </thead>

                    <tbody>
                        <template x-for="member in filteredMembers" :key="member.id">
                            <tr>
                                <td x-text="member.firstname"></td>
                                <td x-text="member.lastname"></td>
                                <td class="text-nowrap">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-ghost-primary"
                                        :data-testid="'member-order-' + member.id"
                                        @click="loadOrderModal(member.id)">
                                        <i data-lucide="plus"></i>
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-ghost-primary"
                                        :data-testid="'member-edit-' + member.id"
                                        @click="loadEditModal(member.id)">
                                        <i data-lucide="edit"></i>
                                    </button>
                                </td>
                                @can('admin')
                                    <td x-text="member.bic || ''"></td>
                                    <td x-text="member.iban || ''"></td>
                                    <td x-text="member.had_collection ? 'Yes' : 'No'"></td>
                                @endcan
                            </tr>
                        </template>

                        <template x-if="filteredMembers.length === 0">
                            <tr>
                                @can('admin')
                                    <td colspan="6" class="text-center text-muted">
                                        No members found
                                    </td>
                                @else
                                    <td colspan="3" class="text-center text-muted">
                                        No members found
                                    </td>
                                @endcan
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
            @include('member.order')
        </div>
    </div>
</div>

<div class="modal modal-blur fade" id="member-edit" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            @include('member.edit')
        </div>
    </div>
</div>
@stop