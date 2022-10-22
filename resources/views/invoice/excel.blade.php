
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
        <?php $column = 'A'; $second_colum = false ?>
        <tr>
            @foreach($members as $m)
                <?php $column++; ?>
                <td>
                @if(is_string($m))
                    {{ $m }}
                @else
                    {{ sprintf('%.2f', $m) }}
                @endif
                </td>
            @endforeach
            {{--<?php $column--; $column--; ?>--}}
            {{--<td>=SUM(B{{ $row }}: {{ chr($column) . $row }})</td>--}}
        </tr>
        <?php $row++; ?>
    @endforeach
    <?php $row--; $column = 'B'; ?>
    <tr>
        <td></td>
          @for($i=0, $column = 'B' ; $i < $products->count() +2, $column < 'ZZ'; $i++, $column++)
              <?php
                if ($i === ($products->count() +2)) {
                    break;
                    break;
                }
              ?>
            <td>=SUM({{ $column }}2:{{ $column . $row }})</td>
         @endfor

    </tr>
</table>
