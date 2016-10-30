@extends('layout.master')

@section('content')

Totaal batches: {{ $total }} <br>

Sepa Download:
<ul>
@foreach($batchlink as $b)
     <li><a href="{{ url('downloadSEPA/' . $b)  }}"> {{ $b }}</a> <br></li>
@endforeach
</ul>

Members with to high transactions:
<ul>
@foreach($memberswithtohightransaction as $m)
    <li>{{ $m->firstname . ' ' . $m->lastname }} </li>
@endforeach
</ul>

Members withoutbankinfo:
<ul>
@foreach($memberswithoutbankinfo as $m)
    <li>{{ $m->firstname . ' ' . $m->lastname }} </li>
@endforeach
</ul>

@stop