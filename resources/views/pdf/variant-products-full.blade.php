@php 
$products = $variant->products->sortBy('index')->filter(function($product){return $product->group == '0';})->values()->toArray();
$products = array_chunk($products, 2); 
@endphp
<style>
    b {color: #95959C}
</style>
@foreach($products as $index => $product)
<table style="width: 100%" nobr="true">
    @if($index == 0)
    <tr>
        <td colspan="4"><h2 style="font-weight: normal;">Описание товаров и услуг к варианту {{$variant->name}}</h2><div style="line-height: 5px"></div></td>
    </tr>
    @endif
    <tr>
        <td style="width: 5%"></td>
        <td style="width: 45%"><!--
            @if(isset($product[0]))
            --><img width="200" height="200" src="{!! str_replace($userDomain, '', $product[0]['image']) !!}"><br><br><!--
                @if(is_array($product[0]['values']))
                    @foreach ($product[0]['values'] as $value)
                        @if($value['type'] == 'name')
                        --><span><strong>{{ $value['value'] }}</strong></span><br><!--
                        @endif
                        @if($value['type'] == 'price')
                        --><span><b>Цена:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                        @if($value['type'] == 'count')
                        --><span><b>Количество:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                        @if($value['type'] == 'discount'  && $value['value'])
                        --><span><b>Скидка:</b> {{ $value['value'] }} %</span><br><!--
                        @endif
                        @if($value['type'] == 'cost')
                        --><span><b>Стоимость:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                    @endforeach
                    @if($product[0]['description'])
                    --><span>{!! nl2br($product[0]['description']) !!}</span><br><!--
                    @endif
                @endif
            @endif
        --></td>
        <td style="width: 5%"></td>
        <td style="width: 45%"><!--
            @if(isset($product[1]))
            --><img width="200" height="200" src="{!! str_replace($userDomain, '', $product[1]['image']) !!}"><br><br><!--
                @if(is_array($product[1]['values']))
                    @foreach ($product[1]['values'] as $value)
                        @if($value['type'] == 'name')
                        --><span><strong>{{ $value['value'] }}</strong></span><br><!--
                        @endif
                        @if($value['type'] == 'price')
                        --><span><b>Цена:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                        @if($value['type'] == 'count')
                        --><span><b>Количество:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                        @if($value['type'] == 'discount' && $value['value'])
                        --><span><b>Скидка:</b> {{ $value['value'] }} %</span><br><!--
                        @endif
                        @if($value['type'] == 'cost')
                        --><span><b>Стоимость:</b> {{ $value['value'] }}</span><br><!--
                        @endif
                    @endforeach
                    @if($product[1]['description'])
                    --><span>{!! nl2br($product[1]['description']) !!}</span><br><!--
                    @endif
                @endif 
            @endif
        --></td>
    </tr>
</table>
@endforeach