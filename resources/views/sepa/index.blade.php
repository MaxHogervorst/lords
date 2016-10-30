@extends('layout.master')

@section('content')
    <form id="sepaform" role="form" method="POST" action="{{ url('sepa') }}">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label for="creditorName" class="control-label">Creditor Name</label>
                    <input type="text" name="creditorName" value="{{ Settings::get('creditorName') }}" class="form-control" autocomplete="off">

        </div>
        <div class="form-group">
            <label for="creditorAccountIBAN" class="control-label">Creditor Account IBAN</label>
                    <input type="text" name="creditorAccountIBAN" value="{{ Settings::get('creditorAccountIBAN') }}" class="form-control" autocomplete="off">

        </div>
        <div class="form-group">
            <label for="creditorAgentBIC" class="control-label">Creditor BIC</label>
                    <input type="text" name="creditorAgentBIC" value="{{ Settings::get('creditorAgentBIC') }}" class="form-control" autocomplete="off">

        </div>
        <div class="form-group">
            <label for="creditorId" class="control-label">Creditor Id</label>
                    <input type="text" name="creditorId" value="{{ Settings::get('creditorId') }}" class="form-control" autocomplete="off">

        </div>
        <div class="form-group">
            <label for="creditorPain" class="control-label">Type Pain</label>
                    <input type="text" name="creditorPain" value="{{ Settings::get('creditorPain') }}" class="form-control" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="ReqdColltnDt" class="control-label">Collection Days</label>
                    <input type="text" name="ReqdColltnDt" value="{{ Settings::get('ReqdColltnDt') }}" class="form-control" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="creditorMaxMoneyPerBatch" class="control-label">Max Total Money Per Batch</label>
                    <input type="text" name="creditorMaxMoneyPerBatch" value="{{ Settings::get('creditorMaxMoneyPerBatch') }}" class="form-control" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="creditorMaxMoneyPerTransaction" class="control-label">Max Money Per Transaction</label>
                    <input type="text" name="creditorMaxMoneyPerTransaction" value="{{ Settings::get('creditorMaxMoneyPerTransaction') }}" class="form-control" autocomplete="off">
        </div>
        <div class="form-group">
            <label for="creditorMaxTransactionsPerBatch" class="control-label">Max Transactions per Batch</label>
                    <input type="text" name="creditorMaxTransactionsPerBatch" value="{{ Settings::get('creditorMaxTransactionsPerBatch') }}" class="form-control" autocomplete="off">
        </div>
            <button type="button" class="btn btn-outline btn-primary" data-ajax-submit="#sepaform" data-ajax-type="POST" data-ajax-callback-function="reload"><i class="fa fa-save fa-fw">  </i>Save SEPA Settings</button>

    </form>

@stop
