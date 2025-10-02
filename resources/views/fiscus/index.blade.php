@extends('layout.master')


@section('content')
    <form id="product-form" name="member-form" class="form-inline" action="{{ url('product') }}" method="post">
        <input type="search" id="filter" name="name" placeholder="Search or Add" class="form-control" autofocus="" autocomplete="off">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
    </form>

    <div class="row">&nbsp;</div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="products">

            <thead>
            <tr>

                <th>Name</th>
                {{--<th>Price</th>--}}
                <th class="col-sm-1">Actions</th>
            </tr>
            </thead>

            <tbody>
            @foreach ($invoice_products as $m)
                <tr id="{{ $m->id }}">
                    <td>{{ $m->name }}</td>
                    {{--<td>{{ $m->price }}</td>--}}

                    <td>
                        <a href="{{ url('fiscus/edit') }}" class="btn btn-sm btn-primary"><i class="fa fa-edit fa-fw">  </i></a>
                    </td>

                </tr>
            @endforeach
            </tbody>

        </table>
    </div>

@stop