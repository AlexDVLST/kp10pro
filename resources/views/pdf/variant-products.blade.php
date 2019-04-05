@if($variant->fields && $variant->products)
@php
$countFields = $variant->fields->count();
$nameWidth = 58.33333333; // 4
if($countFields > 4){
    //If variant has discount
    $discount = false;
    $variant->fields->each(function($field) use (&$discount){if($field->type=='discount'){$discount = true;}});
    //bootstrap cols 12 - same logic
    $nameWidth = 8.33333333 * (12 - ($countFields - 1 + ($discount ? 3 : 2)));
}
@endphp 
<style>
.tbl td {font-size: 10px;}
.tbl-right{text-align:right;}
.tbl-center{text-align:center;}
</style>
<table style="width: 100%" class="tbl" nobr="true" cellpadding="0" cellspacing="0">
    <tr>
        <td colspan="{{count($variant->fields)}}" style="line-height: 1;"><!--
        --><table cellpadding="0" cellspacing="0">
                <tr><td colspan="3" style="height: 15px"></td></tr>
                <tr>
                    <td style="width: 10px;"></td>
                    <td colspan="2"><h2 style="font-weight: bolder;">{{$variant->name}}</h2></td>
                </tr>
                @if(false === strpos($variantDescription, 'Введите краткое описание'))
                <tr><td colspan="3" style="height: 15px"></td></tr>
                <tr>
                    <td style="width: 10px;"></td>
                    <td style="width: 40px;"><img width="20" height="20" src="{!! $managerImg !!}"></td>
                    <td style="width: 500px;">{!! $variantDescription !!}</td>
                </tr>
                @endif
                <tr><td colspan="3" style="height: 15px"></td></tr>
            </table><!--
            
        --></td>
    </tr>
    <tr>
        @foreach($variant->fields->sortBy('index') as $fIndex => $field)
            @switch($field->type)
                @case('name')
                @php $width = $nameWidth; $position = ''; @endphp
                    @break
                @case('count')
                @php $width = '16.6'; $position = 'tbl-center'; @endphp
                    @break        
                @case('price')
                @php $width = '8.3'; $position = 'tbl-center'; @endphp
                    @break        
                @case('discount')
                @php $width = '8.3'; $position = 'tbl-center'; @endphp
                    @break        
                @case('price-with-discount')
                @php $width = '16.6'; $position = 'tbl-center'; @endphp
                    @break        
                @case('good-coll')
                @php $width = '8.3'; $position = 'tbl-center'; @endphp
                    @break        
                @case('cost')
                @php $width = '16.6'; $position = 'tbl-right'; @endphp
                    @break        
                @default
                    @php $width = '8.3'; $position = ''; @endphp
            @endswitch
            <td style="font-size: 10px; height:20px; width: {{$width}}%" class="{{$position}}"><!--
            @if($position != '' && $position != 'tbl-right')
            -->{{ mb_convert_case($field->name, MB_CASE_TITLE, "UTF-8") }}<!--
            @elseif($position == '')
            --><table style="width:100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width: 10px"></td>
                        <td style="width: 90%">{!! mb_convert_case($field->name, MB_CASE_TITLE, "UTF-8") !!}</td>
                    </tr>
                </table><!--
            @elseif($position == 'tbl-right')
            --><table style="width:100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="width: 90%">{!! mb_convert_case($field->name, MB_CASE_TITLE, "UTF-8") !!}</td>
                        <td style="width: 10px"></td>
                    </tr>
                </table><!--
            @endif
            --></td>
        @endforeach
    </tr>
    @foreach($variant->products->sortBy('index') as $pIndex => $product)
    <tr style="{{$pIndex % 2 == 0?'':'background-color:#f8f8f8;'}}">
    @if($product->values)
        @foreach($product->values->sortBy('index') as $vIndex => $value)
            @php $sign = ''; @endphp
            @if($product->group == 0)
                @switch($value->type)
                    @case('name')
                    @php $width = $nameWidth; $position = ''; @endphp
                        @break
                    @case('count')
                    @php $width = '16.6'; $position = 'tbl-center'; @endphp
                        @break        
                    @case('price')
                    @php $width = '8.3'; $position = 'tbl-center'; @endphp
                        @break        
                    @case('discount')
                    @php $width = '8.3'; $position = 'tbl-center'; $sign = ' %'; @endphp
                        @break        
                    @case('price-with-discount')
                    @php $width = '16.6'; $position = 'tbl-center'; @endphp
                        @break     
                    @case('good-coll')
                    @php $width = '8.3'; $position = 'tbl-center'; @endphp
                        @break   
                    @case('cost')
                    @php $width = '16.6'; $position = 'tbl-right'; $sign = ' <i>'.$currencyFont.'</i>'; @endphp
                        @break        
                    @default
                        @php $width = '8.3'; $position = ''; @endphp
                @endswitch
                <td style="width: {{$width}}%;" class="{{$position}}"><!--
                    @if($position != '' && $position != 'tbl-right')
                    --><table style="width:100%" cellpadding="0" cellspacing="0">
                            <tr style="line-height: 5px"><td></td></tr>
                            <tr><td style="width: 100%">{{$value->value}}{{$sign}}</td></tr>
                            <tr style="line-height: 5px"><td></td></tr>
                        </table>
                    @elseif($position == '')
                    --><table style="width:100%" cellpadding="0" cellspacing="0">
                            <tr style="line-height: 5px"><td colspan="2"></td></tr>
                            <tr>
                                <td style="width: 10px"></td>
                                <td style="width: 90%">{{$value->value}}</td>
                            </tr>
                            <tr style="line-height: 5px"><td colspan="2"></td></tr>
                        </table>
                    @elseif($position == 'tbl-right')
                    --><table style="width:100%" cellpadding="0" cellspacing="0">
                            <tr style="line-height: 5px"><td colspan="2"></td></tr>
                            <tr>
                                <td style="width: 90%">{{$value->value}}{!! $sign !!}</td>
                                <td style="width: 10px"></td>
                            </tr>
                            <tr style="line-height: 5px"><td colspan="2"></td></tr>
                        </table>
                    @endif
                </td>
            @else
            {{-- Group --}}
            <td colspan="{{$variant->fields->count()}}" style="font-weight: bolder"><!--
            --><table style="width:100%" cellpadding="0" cellspacing="0">
                    <tr style="line-height: 5px"><td colspan="2"></td></tr>
                    <tr>
                        <td style="width: 10px"></td>
                        <td style="width: 90%">{{$value->value}} ({{$product->totalCost}} <i>{{$currencyFont}}</i>)</td>
                    </tr>
                    <tr style="line-height: 5px"><td colspan="2"></td></tr>
                </table>
            </td>
            @endif
        @endforeach
    @endif
    </tr>
    @endforeach
    <tr>
        <td colspan="{{count($variant->fields)}}"><!--
        --><table style="width: 100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="width: 10px"></td>
                    <td style="width: 31%;line-height: 1;"><!--
                    --><div>
                            <div><!--
                            @if($variant->specialDiscounts)
                                @foreach ($variant->specialDiscounts as $discount)
                                --><span style="color:#95959C;font-size:10px;">{{$discount->name}} ({{$discount->value}} <i>{{$currencyFont}}</i>)</span><br><!--
                                @endforeach
                            @endif
                            --></div><!--
                        --></div>
                    </td>
                    <td style="width: 33%;line-height: 1;" align="right"><!--
                        --><div>
                        @if($variant->costWithoutDiscount > 0)
                            <div style="height:13px; color:#95959C; font-size:9px; line-height: 10px;">ЦЕНА БЕЗ СКИДКИ</div>
                            <div style="font-size:16px; color:#95959C; line-height: 10px; text-decoration: line-through;">{{$variant->costWithoutDiscount}} <i>{{$currencyFont}}</i></div>
                        @endif
                        </div>
                    </td>
                    <td style="width: 33%;line-height: 1;" align="right"><!--
                        --><div>
                            <div style="height:13px; color:#95959C; font-size:9px;line-height: 10px;">СТОИМОСТЬ</div>
                            <div style="font-size:16px; line-height: 10px;">{{$variant->costWithDiscount}} <i>{{$currencyFont}}</i></div>
                            @if($variant->tax == 1)
                            @php
                                $tax  = number_format((floatval($variant->costWithDiscount) / 1.18) * 0.18, 2, '.', '');    
                            @endphp
                            <span style="color:#95959C;font-size:10px;">Включая НДС {{$tax}}<i>{{$currencyFont}}</i></span>
                            @endif
                            @if($variant->tax == 2)
                            <span style="color:#95959C;font-size:8px;line-height: 5px;">НДС НЕ ОБЛАГАЕТСЯ</span><br>
                            <span style="color:#95959C;font-size:8px;line-height: 1px;">(СОГЛАСНО П.2, СТ.346.11 НК РФ)</span>
                            @endif
                        </div>
                    </td>
                    <td style="width: 10px"></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif