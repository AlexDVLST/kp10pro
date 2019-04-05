@auth
@if($notifications = App\Models\Notification::whereNotNull('view')->whereViewed(0)->get())
    @foreach ($notifications as $notification)
        <div id="notification-{{$notification->view}}">
            {{-- Get component  --}}
            <{{$notification->view}} :notification-id="{{$notification->id}}"></{{$notification->view}}>
        </div>
        {{-- Include script --}}
        <script src="{{url('/js/notifications/'.$notification->view.'.min.js')}}"></script>
        <link rel="stylesheet" href="{{url('/css/notifications/'.$notification->view.'.min.css')}}">
    @endforeach
@endif
@endauth