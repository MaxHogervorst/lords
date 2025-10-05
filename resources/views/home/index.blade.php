@extends('layout.master')


@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Last Five Orders</h3>
        </div>
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Product</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($orders as $m)
                    <tr>
                        <td>{{ $m->created_at }}</td>
                        <td>{{ isset($m->ownerable) ? isset($m->ownerable->name) ?  $m->ownerable->name : $m->ownerable->firstname . ' ' . $m->ownerable->lastname : 'verwijderd' }}</td>
                        <td>{{ $m->amount }}</td>
                        <td>{{ $m->product->name }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop