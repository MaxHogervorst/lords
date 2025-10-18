@extends('layout.master')


@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="product-form" name="member-form" action="{{ url('product') }}" method="post">
                <div class="row g-2">
                    <div class="col">
                        <input type="search" id="filter" name="name" placeholder="Search or Add" class="form-control" autofocus="" autocomplete="off">
                    </div>
                </div>
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table" id="products">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th class="w-1">Actions</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($invoice_products as $m)
                    <tr id="{{ $m->id }}">
                        <td>{{ $m->name }}</td>

                        <td class="text-nowrap">
                            <a href="{{ url('fiscus/edit') }}?product={{ $m->id }}" class="btn btn-sm btn-ghost-primary">
                                <i data-lucide="edit"></i>
                            </a>
                        </td>

                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

@stop