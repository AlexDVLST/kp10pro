@if(!empty($details))
<table>
    @foreach($details as $detail)
    <tr><!--
    --><td style="height: 20px">{!! $detail->children[0]->innertext !!}</td><!--
    --><td>{!! $detail->children[1]->innertext !!}</td>
    </tr>
    @endforeach
</table>
@endif