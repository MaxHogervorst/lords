
<table class="table">
    <tr>
        <th>Naam</th>
        <th>Manor Expenses</th>
        @foreach($products as $p)
            <th>{{ $p->name }}</th>
        @endforeach
        <th>Totaal</th>
    </tr>
    <?php $row = 2; ?>
     @foreach($result as $members)
        <?php $column = 66; ?>
        <tr>
            @foreach($members as $m)
                <?php $column++; ?>
                <td>
                @if(is_string($m))
                    {{ $m }}
                @else
                    {{ money_format('%.2n', $m) }}
                @endif
                </td>
            @endforeach
            {{--<?php $column--; $column--; ?>--}}
            {{--<td>=SUM(B{{ $row }}: {{ chr($column) . $row }})</td>--}}
        </tr>
        <?php $row++; ?>
    @endforeach
    <?php $row--; $column = 66; ?>
    <tr>
        <td></td>
          @for($i=0; $i < $products->count()+2; $i++)
            <td>=SUM({{ chr($column) }}2:{{ chr($column) . $row }})</td>
             <?php $column++; ?>
         @endfor

    </tr>
</table>
