// require('../../bootstrap');

// Instance the tour
let tour = new Tour({
	steps: [
		{
			element: ".tour-step-1",
			title: "Выберите шаблон",
			content: "Для	теста	мы	сделали	несколько	готовых	шаблонов.	Выберите \
				подходящий	Вам	по	тематике.	Не	переживайте	если именно	вашей \
				отрасли	не	будет",
			placement: "bottom",
		}
	],
	template:
		" <div class='popover tour'>\
		<div class='arrow'></div>\
			<div class='box box-success box-solid'> \
				<div class='box-header with-border'> \
					<h3 class='box-title popover-title'></h3> \
				</div> \
				<div class='box-body popover-content'> \
				</div> \
				\<div class='box-footer'> \
				 <button id='end-tour' class='btn btn-success btn-flat' data-role='end'>Завершить тур</button> \
				</div> \
			</div>\
		</div>",
	onEnd: function () {
		axios.post('/home/dismiss-tour').then(function () {
			location.reload();
		});
	}
});

// Initialize the tour
tour.init();

// Start the tour
tour.start();

//Disable element on the page
$('header.main-header, aside.main-sidebar, footer.main-footer').each(function () {
	$(this).css({ 'opacity': '0.4', 'pointer-events': 'none' });
});
$('.content-wrapper').css('background', 'rgba(255,255,255,0.6)');