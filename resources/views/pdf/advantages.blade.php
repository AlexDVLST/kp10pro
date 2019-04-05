@foreach ($advantages as $advantage)
    <table style="width: 100%">
    @php
        $icon1 = $icon2 = '';
        $fullWidth1 = false;
        $fullWidth2 = false;
        if(isset($advantage[0])){
            preg_match("/fa-[\w-]+/", $advantage[0]->find('i', 0)->class, $icon1);
            $fullWidth1 = strpos($advantage[0]->class, 'col-md-12');
        }
        if(isset($advantage[1])){
            preg_match("/fa-[\w-]+/", $advantage[1]->find('i', 0)->class, $icon2);
            $fullWidth2 = strpos($advantage[1]->class, 'col-md-12');
        }
    @endphp
    @if($fullWidth1 && isset($advantage[0]))
    <tr nobr="true">
        <td style="width: 100%" colspan="2"><!--
        --><i style="font-family:fontawesome; color:{{$corporateColor}};font-size:24px">{{$faCodes[$icon1[0]]}}</i><br><!--
        --><span style="color:{{$corporateColor}};">{!!$advantage[0]->find('.advantage-title', 0)->plaintext!!}</span><br><!--
        --><span>{!! trim($advantage[0]->find('.media-body div', 1)->plaintext) !!}</span><br><!--
        --></td>
        <td></td>
    </tr>
    @endif
    @if(!$fullWidth1 || !$fullWidth2)
    <tr nobr="true">
        <td style="width: 50%"><!--
            @if(!$fullWidth1 && isset($advantage[0]))
        --><i style="font-family:fontawesome; color:{{$corporateColor}};font-size:24px">{{$faCodes[$icon1[0]]}}</i><br><!--
        --><span style="color:{{$corporateColor}};">{!!$advantage[0]->find('.advantage-title', 0)->plaintext!!}</span><br><!--
        --><span>{!! trim($advantage[0]->find('.media-body div', 1)->plaintext) !!}</span><br><!--
            @endif
        --></td>
        <td style="width: 50%"><!--
            @if(!$fullWidth2 && isset($advantage[1]))
        --><i style="font-family:fontawesome;color:{{$corporateColor}};font-size:24px">{{$faCodes[$icon2[0]]}}</i><br><!--
        --><span style="color:{{$corporateColor}};">{!!$advantage[1]->find('.advantage-title', 0)->plaintext!!}</span><br><!--
        --><span>{!! trim($advantage[1]->find('.media-body div', 1)->plaintext) !!}</span><br><!--
            @endif
        --></td>
    </tr>
    @endif
    @if($fullWidth2 && isset($advantage[1]))
    <tr nobr="true">
        <td style="width: 100%" colspan="2"><!--
        --><i style="font-family:fontawesome; color:{{$corporateColor}};font-size:24px">{{$faCodes[$icon2[0]]}}</i><br><!--
        --><span style="color:{{$corporateColor}};">{!!$advantage[1]->find('.advantage-title', 0)->plaintext!!}</span><br><!--
        --><span>{!! trim($advantage[1]->find('.media-body div', 1)->plaintext) !!}</span><br><!--
        --></td>
        <td></td>
    </tr>
    @endif
    </table>
@endforeach