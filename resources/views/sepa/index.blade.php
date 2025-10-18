@extends('layout.master')

@section('script')
    <script>
        // Alpine.js component for SEPA Settings page
        document.addEventListener('alpine:init', () => {
            Alpine.data('sepaManager', () => ({
                isLoading: false,

                async saveSettings(event) {
                    event.preventDefault();
                    this.isLoading = true;

                    try {
                        const formData = new FormData(event.target);
                        const response = await http.post('{{ url("sepa") }}', formData);

                        if (response.data.success) {
                            Alpine.store('notifications').success(response.data.message || 'SEPA settings saved successfully');
                        }
                    } catch (error) {
                        Alpine.store('notifications').error(error.response?.data?.message || 'Error saving SEPA settings');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
@stop

@section('content')
    <div class="card" x-data="sepaManager" x-cloak>
        <div class="card-header">
            <h3 class="card-title">SEPA Settings</h3>
        </div>
        <div class="card-body">
            <form @submit.prevent="saveSettings" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="mb-3">
                    <label for="creditorName" class="form-label">Creditor Name</label>
                    <input
                        type="text"
                        name="creditorName"
                        id="creditorName"
                        value="{{ Settings::get('creditorName') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorAccountIBAN" class="form-label">Creditor Account IBAN</label>
                    <input
                        type="text"
                        name="creditorAccountIBAN"
                        id="creditorAccountIBAN"
                        value="{{ Settings::get('creditorAccountIBAN') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorAgentBIC" class="form-label">Creditor BIC</label>
                    <input
                        type="text"
                        name="creditorAgentBIC"
                        id="creditorAgentBIC"
                        value="{{ Settings::get('creditorAgentBIC') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorId" class="form-label">Creditor Id</label>
                    <input
                        type="text"
                        name="creditorId"
                        id="creditorId"
                        value="{{ Settings::get('creditorId') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorPain" class="form-label">Type Pain</label>
                    <input
                        type="text"
                        name="creditorPain"
                        id="creditorPain"
                        value="{{ Settings::get('creditorPain') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="ReqdColltnDt" class="form-label">Collection Days</label>
                    <input
                        type="text"
                        name="ReqdColltnDt"
                        id="ReqdColltnDt"
                        value="{{ Settings::get('ReqdColltnDt') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorMaxMoneyPerBatch" class="form-label">Max Total Money Per Batch</label>
                    <input
                        type="text"
                        name="creditorMaxMoneyPerBatch"
                        id="creditorMaxMoneyPerBatch"
                        value="{{ Settings::get('creditorMaxMoneyPerBatch') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorMaxMoneyPerTransaction" class="form-label">Max Money Per Transaction</label>
                    <input
                        type="text"
                        name="creditorMaxMoneyPerTransaction"
                        id="creditorMaxMoneyPerTransaction"
                        value="{{ Settings::get('creditorMaxMoneyPerTransaction') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <div class="mb-3">
                    <label for="creditorMaxTransactionsPerBatch" class="form-label">Max Transactions per Batch</label>
                    <input
                        type="text"
                        name="creditorMaxTransactionsPerBatch"
                        id="creditorMaxTransactionsPerBatch"
                        value="{{ Settings::get('creditorMaxTransactionsPerBatch') }}"
                        class="form-control"
                        autocomplete="off">
                </div>

                <button
                    type="submit"
                    class="btn btn-primary"
                    :disabled="isLoading">
                    <i data-lucide="save"></i>
                    <span x-text="isLoading ? 'Saving...' : 'Save SEPA Settings'"></span>
                </button>
            </form>
        </div>
    </div>

@stop
