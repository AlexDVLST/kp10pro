@if(\App\Models\UserMeta::getMeta('show-tour', true))
@section('scripts')
	<script>
		/* Disable show tour settings */
		$('#tour-dismiss').click(function () {
			axios.post('/home/dismiss-tour');
		});
		//Disable element on the page
		$('header.main-header, aside.main-sidebar, footer.main-footer').each(function () {
			$(this).css({'opacity':'0.4', 'pointer-events':'none'});
		});
		$('.content-wrapper').css('background', 'rgba(255,255,255,0.6)');

	</script>
@stop
<div class="row">
	<div class="col-md-6 col-sm-6 col-xs-12">
		<div class="box box-solid">
			<div class="box-header with-border">
				<i class="fa fa-text-width"></i>

				<h3 class="box-title">Создайте первое КП за 10 минут</h3>
			</div>
			<div class="box-body">
				<h4>Мы создали этот сервиc для того, чтобы максимально автоматизировать работу по созданию коммерческого
					предложения.</h4>
				<h4>Мы не хотим отнимать Ваше время, поэтому предлагаем за 10 минут создать и отправить свое превое
					КП</h4>
				<a href="{{url('/offers')}}" class="btn btn-success btn-flat">Поехали</a>
				<button type="button" class="btn btn-default btn-flat" id="tour-dismiss">Больше не показывать
				</button>
			</div>
		</div>

	</div>
</div>
@endif