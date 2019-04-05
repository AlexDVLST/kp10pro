@if(is_array($sliderImages))
<table style="width: 100%">
    @foreach ($sliderImages as $item)
        <tr nobr="true">
            <td style="width: 100%">
            <img src="{{str_replace($userDomain, '', $item->attr['src'])}}">
            </td>
        </tr>
    @endforeach
</table>
@endif