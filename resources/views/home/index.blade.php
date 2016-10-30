@extends('layout.master')


@section('content')
    <div class="col-lg-6">

        <div class="panel panel-default">
            <div class="panel-heading">
                Last Five Orders
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" >
                        <thead>
                            <tr>
                                <td> Date </td>
                                <td> Name </td>
                                <td> Amount </td>
                                <td> Product </td>
                            </tr>
                        </thead>
                    @foreach ($orders as $m)
                        <tr>
                            <td>{{ $m->created_at }}</td>
                            <td>{{ isset($m->ownerable) ? isset($m->ownerable->name) ?  $m->ownerable->name : $m->ownerable->firstname . ' ' . $m->ownerable->lastname : 'verwijderd' }}</td>
                            <td>{{ $m->amount }}</td>
                            <td>{{ $m->product->name }}</td>
                        </tr>
                    @endforeach
                     </table>
                </div>
            </div>
        </div>

    </div>

@stop