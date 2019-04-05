@if(\App\Models\UserMeta::getMeta('show-tour', true))
@section('styles')
    <link rel="stylesheet" href="{{asset('plugins/bootstrap-tour/css/bootstrap-tour.min.css')}}">
    <link rel="stylesheet" href="{{asset('/css/bootstrap-tour.css')}}">
@stop
@section('scripts')
    <script src="{{asset('plugins/bootstrap-tour/js/bootstrap-tour.min.js')}}"></script>
    <script src="{{asset('bower_components/datatables.net/js/jquery.dataTables.js')}}"></script>
@stop
@endif