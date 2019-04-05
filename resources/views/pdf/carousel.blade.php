@if(is_array($carouselImages))
<table style="width: 100%">
    @foreach ($carouselImages as $item)
        <tr nobr="true">
            <td style="width: 50%">
            @if(isset($item[0]))
            <img src="{{str_replace($userDomain, '', $item[0]->children[0]->attr['src'])}}"><br>
            <span>{!! $item[0]->children[1]->plaintext !!}</span><br>
            @endif
            </td>
            <td style="width: 50%">
            @if(isset($item[1]))
            <img src="{{str_replace($userDomain, '', $item[1]->children[0]->attr['src'])}}"><br>
            <span>{!! $item[1]->children[1]->plaintext !!}</span><br>
            @endif
            </td>
        </tr>
        @endforeach
    </table>
@endif