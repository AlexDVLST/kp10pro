<?php header("Location: https://kp10.pro"); exit; ?>

<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
	<meta charset="utf-8">
	<!-- CSRF Token -->
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="//rentafont.com/web_fonts/webfontcss/MTEyNDRvcmRlcjY4NDk=?fonts=427,429,431,433,435&amp;formats=woff-woff2-svg-ttf&amp;by_style=1&amp;by_id=1"
	 rel="stylesheet" type="text/css" media="all">

	<link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/manifest.json">

	<meta name="msapplication-TileColor" content="#ffffff">
	<meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
	<meta name="theme-color" content="#ffffff">

	<link rel="stylesheet" href="https://static.tildacdn.com/css/tilda-grid-3.0.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="https://project109175.tilda.ws/tilda-blocks-2.12.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="https://static.tildacdn.com/css/tilda-animation-1.0.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="https://static.tildacdn.com/css/tilda-popup-1.1.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="https://static.tildacdn.com/css/tilda-slds-1.4.min.css" rel="stylesheet" media="screen">
	<link rel="stylesheet" href="https://static.tildacdn.com/css/tilda-zoom-2.0.min.css" rel="stylesheet" media="screen">

	<script src="https://static.tildacdn.com/js/jquery-1.10.2.min.js"></script>
	<script src="https://static.tildacdn.com/js/tilda-scripts-2.8.min.js"></script>
	<script src="https://project109175.tilda.ws/tilda-blocks-2.7.js"></script>
	<script src="https://static.tildacdn.com/js/lazyload-1.3.min.js"></script>
	<script src="https://static.tildacdn.com/js/tilda-animation-1.0.min.js"></script>
	<script src="https://static.tildacdn.com/js/tilda-slds-1.4.min.js"></script>
	<script src="https://static.tildacdn.com/js/hammer.min.js"></script>
	<script src="https://static.tildacdn.com/js/tilda-zoom-2.0.min.js"></script>
	<script src="https://static.tildacdn.com/js/tilda-forms-1.0.min.js"></script>

	<title>КП10 - КОНСТРУКТОР КОММЕРЧЕСКИХ ПРЕДЛОЖЕНИЙ</title>
	<meta name="description" content="Это универсальный конструктор коммерческих предложений, который в несколько раз увеличивает скорость подготовки КП и подчёркивает ваше УТП на фоне конкурентов"
	/>
	<meta property="og:title" content="КП10 - КОНСТРУКТОР КОММЕРЧЕСКИХ ПРЕДЛОЖЕНИЙ" />
	<meta property="og:description" content="Это универсальный конструктор коммерческих предложений, который в несколько раз увеличивает скорость подготовки КП и подчёркивает ваше УТП на фоне конкурентов"
	/> {{--
	<meta property="og:url" content="https://kp10.pro" /> --}} {{--
	<meta property="og:image" content="/apple-icon-180x180.png" /> --}}
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />

	<style>
		html,
		body {
			margin: 0;
		}
	</style>

	<!-- Facebook Pixel Code -->
	<script>
		!function(f,b,e,v,n,t,s)
		{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
		n.callMethod.apply(n,arguments):n.queue.push(arguments)};
		if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
		n.queue=[];t=b.createElement(e);t.async=!0;
		t.src=v;s=b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t,s)}(window,document,'script',
		'https://connect.facebook.net/en_US/fbevents.js');
		
		fbq('init', '334596583952163');
		fbq('track', 'PageView');
		</script>
		<noscript>
		<img height="1" width="1"
		src="https://www.facebook.com/tr?id=334596583952163&ev=PageView
		&noscript=1"/>
		</noscript>
	<!-- End Facebook Pixel Code -->
	<!-- Yandex.Metrika counter -->
	<script type="text/javascript">
		(function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter50570539 = new Ya.Metrika2({
                    id:50570539,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    webvisor:true
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/tag.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks2");
	</script>
	<noscript><div><img src="https://mc.yandex.ru/watch/50570539" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
	<!-- /Yandex.Metrika counter -->

</head>

<body>
	<!-- BEGIN JIVOSITE CODE {literal} -->
	<script type='text/javascript'>
		(function(){ var widget_id = '5rS2eI5ES9';var d=document;var w=window;function l(){var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true;s.src = '//code.jivosite.com/script/widget/'+widget_id; var ss = document.getElementsByTagName('script')[0]; ss.parentNode.insertBefore(s, ss);}if(d.readyState=='complete'){l();}else{if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})();
	</script>
	<!-- {/literal} END JIVOSITE CODE -->

	<!--allrecords-->
	<div id="allrecords" class="t-records" data-hook="blocks-collection-content-node" data-tilda-project-id="109175" data-tilda-page-id="3637264"
	 data-tilda-page-alias="1111" data-tilda-formskey="6ad581e2725bb5b362f1fb3539f6238b">
		<div id="rec67749405" class="r t-rec" style=" " data-animationappear="off" data-record-type="257">
			<!-- T228 -->
			<div id="nav67749405marker"></div>
			<div class="t228__mobile">
				<div class="t228__mobile_container">
					<div class="t228__mobile_text t-name t-name_md" field="text">&nbsp;</div>
					<div class="t228__burger"> <span></span> <span></span> <span></span> <span></span> </div>
				</div>
			</div>
			<div id="nav67749405" class="t228 t228__hidden t228__positionabsolute " style="background-color: rgba(0,0,0,0.0); height:100px; "
			 data-bgcolor-hex="#000000" data-bgcolor-rgba="rgba(0,0,0,0.0)" data-navmarker="nav67749405marker" data-appearoffset=""
			 data-bgopacity-two="" data-menushadow="" data-bgopacity="0.0" data-menu-items-align="right" data-menu="yes">
				<div class="t228__maincontainer " style="height:100px;">
					<div class="t228__padding40px"></div>
					<div class="t228__leftside">
						<div class="t228__leftcontainer">
							<a href="https://kp10.pro" style="color:#ffffff;font-size:18px;font-weight:700;letter-spacing:1.5px;">
								<div class="t228__logo t-title" field="title" style="color:#ffffff;font-size:18px;font-weight:700;letter-spacing:1.5px;">
									<div style="font-size:30px;" data-customstyle="yes">КП10</div>
								</div>
							</a>
						</div>
					</div>
					<div class="t228__centerside t228__menualign_right">
						<div class="t228__centercontainer">
							<ul class="t228__list ">
								<li class="t228__list_item"><a class="t-menu__link-item" href="#rec67749411" style="color:#ffffff;font-weight:600;" data-menu-item-number="1">Как это работает</a></li>
								<li class="t228__list_item"><a class="t-menu__link-item" href="#rec67765498" style="color:#ffffff;font-weight:600;" data-menu-item-number="2">Примеры КП</a></li>
								<li class="t228__list_item"><a class="t-menu__link-item" href="#rec67768860" style="color:#ffffff;font-weight:600;" data-menu-item-number="3">Интеграция с CRM</a></li>
								<li class="t228__list_item"><a class="t-menu__link-item" href="#rec67752739" style="color:#ffffff;font-weight:600;" data-menu-item-number="4">Тарифы</a></li>
							</ul>
						</div>
					</div>
					<div class="t228__rightside">
						<div class="t228__rightcontainer">
							<div class="t228__right_buttons">
								<div class="t228__right_buttons_wrap">
									<div class="t228__right_buttons_but">
										<a href="#registerkp10" target="" class="t-btn " style="color:#ffffff;background-color:#3c8dbc;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">
											<table style="width:100%; height:100%;">
												<tr>
													<td>Пробовать бесплатно</td>
												</tr>
											</table>
										</a>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="t228__padding40px"></div>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function() { t228_highlight(); });
				$(window).resize(function() { t228_setBg('67749405');
				});
				$(document).ready(function() { t228_setBg('67749405');
				});
			</script>
			<script type="text/javascript">
				$(document).ready(function() { t228_createMobileMenu('67749405'); });
			</script>
			<style>
				#rec67749405 .t-btn:not(.t-animate_no-hover):hover {
					background-color: #ffffff !important;
					color: #3c8dbc !important;
				}

				#rec67749405 .t-btn:not(.t-animate_no-hover) {
					-webkit-transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out, border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
					transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out, border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
				}
			</style>
			<!--[if IE 8]>
	<style>
	#rec67749405 .t228 { filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#D9000000', endColorstr='#D9000000');
	}
	</style>
	<![endif]-->
		</div>
		{{-- <div id="rec67749406" class="r t-rec" style=" " data-animationappear="off" data-record-type="18">
			<!-- cover -->
			<div class="t-cover" id="recorddiv67749406" bgimgfield="img" style="height:100vh; background-image:url('https://static.tildacdn.com/tild6335-3166-4166-a335-643939336261/-/resize/20x/workplace1245776_1920.jpg');">
				<div class="t-cover__carrier" id="coverCarry67749406" data-content-cover-id="67749406" data-content-cover-bg="https://static.tildacdn.com/tild6335-3166-4166-a335-643939336261/workplace1245776_1920.jpg"
				 data-content-cover-height="100vh" data-content-cover-parallax="" style="height:100vh;background-attachment:scroll; background-position:center bottom;"></div>
				<div class="t-cover__filter" style="height:100vh;background-image: -moz-linear-gradient(top, rgba(0,0,0,0.80), rgba(0,0,0,0.90));background-image: -webkit-linear-gradient(top, rgba(0,0,0,0.80), rgba(0,0,0,0.90));background-image: -o-linear-gradient(top, rgba(0,0,0,0.80), rgba(0,0,0,0.90));background-image: -ms-linear-gradient(top, rgba(0,0,0,0.80), rgba(0,0,0,0.90));background-image: linear-gradient(top, rgba(0,0,0,0.80), rgba(0,0,0,0.90));filter: progid:DXImageTransform.Microsoft.gradient(startColorStr='#33000000', endColorstr='#19000000');"></div>
				<div class="t-container">
					<div class="t-col t-col_12 ">
						<div class="t-cover__wrapper t-valign_middle" style="height:100vh;">
							<div class="t001 t-align_center">
								<div class="t001__wrapper" data-hook-content="covercontent">
									<div class="t001__title t-title t-title_xl t-animate" data-animate-style="fadeinup" data-animate-group="yes" style="" field="title">КОНСТРУКТОР КОММЕРЧЕСКИХ ПРЕДЛОЖЕНИЙ <span style="color: rgb(60, 141, 188);"><u>КП10</u></span></div>
									<div class="t001__descr t-descr t-descr_xl t001__descr_center t-animate" data-animate-style="fadeinup" data-animate-group="yes"
									 style="opacity:1;" field="descr">Готовое коммерческое предложение за 10 кликов<br /></div> <span class="space"></span> </div>
							</div>
						</div>
					</div>
				</div>
				<!-- arrow -->
				<div class="t-cover__arrow">
					<div class="t-cover__arrow-wrapper t-cover__arrow-wrapper_animated">
						<div class="t-cover__arrow_mobile"><svg class="t-cover__arrow-svg" style="fill:#ffffff;" x="0px" y="0px" width="38.417px" height="18.592px" viewBox="0 0 38.417 18.592"
							 style="enable-background:new 0 0 38.417 18.592;"><g><path d="M19.208,18.592c-0.241,0-0.483-0.087-0.673-0.261L0.327,1.74c-0.408-0.372-0.438-1.004-0.066-1.413c0.372-0.409,1.004-0.439,1.413-0.066L19.208,16.24L36.743,0.261c0.411-0.372,1.042-0.342,1.413,0.066c0.372,0.408,0.343,1.041-0.065,1.413L19.881,18.332C19.691,18.505,19.449,18.592,19.208,18.592z"/></g></svg></div>
					</div>
				</div>
				<!-- arrow -->
			</div>
		</div> --}}
		<div id="rec69983652" class="r t-rec" style=" " data-animationappear="off" data-record-type="396">
			<!-- T396 -->
			<style>
				#rec69983652 .t396__artboard {
					min-height: 600px;
					height: 100vh;
					background-color: #ffffff;
				}

				#rec69983652 .t396__filter {
					min-height: 600px;
					height: 100vh;
					background-image: -webkit-gradient( linear, left top, left bottom, from(rgba(0, 0, 0, 0.7)), to(rgba(0, 0, 0, 0.7)));
					background-image: -webkit-linear-gradient(top, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
					background-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7));
				}

				#rec69983652 .t396__carrier {
					min-height: 600px;
					height: 100vh;
					background-position: center center;
					background-attachment: scroll;
					background-image: url('https://static.tildacdn.com/tild3035-6637-4438-b264-326337616634/-/resize/20x/image.png');
					background-size: cover;
					background-repeat: no-repeat;
				}

				@media screen and (max-width: 1199px) {
					#rec69983652 .t396__artboard {
						min-height: 800px;
					}
					#rec69983652 .t396__filter {
						min-height: 800px;
					}
					#rec69983652 .t396__carrier {
						min-height: 800px;
						background-attachment: scroll;
					}
				}

				@media screen and (max-width: 959px) {
					#rec69983652 .t396__artboard {
						min-height: 910px;
					}
					#rec69983652 .t396__filter {
						min-height: 910px;
					}
					#rec69983652 .t396__carrier {
						min-height: 910px;
					}
				}

				@media screen and (max-width: 639px) {
					#rec69983652 .t396__artboard {
						min-height: 780px;
					}
					#rec69983652 .t396__filter {
						min-height: 780px;
					}
					#rec69983652 .t396__carrier {
						min-height: 780px;
					}
				}

				@media screen and (max-width: 479px) {
					#rec69983652 .t396__artboard {
						min-height: 520px;
					}
					#rec69983652 .t396__filter {
						min-height: 520px;
					}
					#rec69983652 .t396__carrier {
						min-height: 520px;
					}
				}

				#rec69983652 .tn-elem[data-elem-id="1470209944682"] {
					color: #ffffff;
					z-index: 2;
					top: calc(50vh - 0px + -185.5px);
					left: calc(50% - 345px + 506px);
					width: 690px;
				}

				#rec69983652 .tn-elem[data-elem-id="1470209944682"] .tn-atom {
					color: #ffffff;
					font-size: 49px;
					font-family: 'Arial';
					line-height: 1.15;
					font-weight: 700;
					background-position: center center;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {
					#rec69983652 .tn-elem[data-elem-id="1470209944682"] .tn-atom {
						font-size: 44px;
					}
				}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {
					#rec69983652 .tn-elem[data-elem-id="1470209944682"] .tn-atom {
						font-size: 32px;
					}
				}

				@media screen and (max-width: 479px) {
					#rec69983652 .tn-elem[data-elem-id="1470209944682"] .tn-atom {
						font-size: 22px;
					}
				}

				#rec69983652 .tn-elem[data-elem-id="1504792630047"] {
					z-index: 5;
					top: calc(100vh + -34px);
					left: calc(50% - 20px + 0px);
					width: 40px;
				}

				#rec69983652 .tn-elem[data-elem-id="1504792630047"] .tn-atom {
					background-position: center center;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {}

				#rec69983652 .tn-elem[data-elem-id="1539088657485"] {
					z-index: 6;
					top: calc(50vh - 300px + 10px);
					left: calc(50% - 600px + -46px);
					width: 783px;
					height: 459px;
				}

				#rec69983652 .tn-elem[data-elem-id="1539088657485"] .tn-atom {
					background-position: center center;
					background-size: cover;
					background-repeat: no-repeat;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {}

				#rec69983652 .tn-elem[data-elem-id="1539089124396"] {
					color: #ffffff;
					text-align: center;
					z-index: 7;
					top: calc(50vh - 300px + 342px);
					left: calc(50% - 600px + 757px);
					width: 210px;
					height: 55px;
				}

				#rec69983652 .tn-elem[data-elem-id="1539089124396"] .tn-atom {
					color: #ffffff;
					font-size: 14px;
					font-family: 'Arial';
					line-height: 1.55;
					font-weight: 600;
					border-width: 1px;
					border-radius: 30px;
					background-color: #3c8dbc;
					background-position: center center;
					border-color: transparent;
					border-style: solid;
					transition: background-color 0.2s ease-in-out, color 0.2s ease-in-out, border-color 0.2s ease-in-out;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {}

				#rec69983652 .tn-elem[data-elem-id="1539089234640"] {
					color: #ffffff;
					z-index: 8;
					top: calc(50vh - 300px + 231px);
					left: calc(50% - 600px + 726px);
					width: 560px;
				}

				#rec69983652 .tn-elem[data-elem-id="1539089234640"] .tn-atom {
					color: #ffffff;
					font-size: 20px;
					font-family: 'FuturaPT';
					line-height: 1.55;
					font-weight: 400;
					background-position: center center;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {}

				#rec69983652 .tn-elem[data-elem-id="1539089600586"] {
					z-index: 9;
					top: calc(50vh - 300px + 501px);
					left: calc(50% - 600px + -40px);
					width: 50px;
				}

				#rec69983652 .tn-elem[data-elem-id="1539089600586"] .tn-atom {
					background-position: center center;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {}

				#rec69983652 .tn-elem[data-elem-id="1539089669309"] {
					color: #ffffff;
					z-index: 10;
					top: calc(50vh - 300px + 511px);
					left: calc(50% - 600px + 25px);
					width: 560px;
				}

				#rec69983652 .tn-elem[data-elem-id="1539089669309"] .tn-atom {
					color: #ffffff;
					font-size: 20px;
					font-family: 'FuturaPT';
					line-height: 1.55;
					font-weight: 400;
					background-position: center center;
					border-color: transparent;
					border-style: solid;
				}

				@media screen and (max-width: 1199px) {}

				@media screen and (max-width: 959px) {}

				@media screen and (max-width: 639px) {}

				@media screen and (max-width: 479px) {
					#rec69983652 .tn-elem[data-elem-id="1539089669309"] .tn-atom {
						font-size: 17px;
					}
				}
			</style>
			<div class='t396'>
				<div class="t396__artboard" data-artboard-recid="69983652" data-artboard-height="600" data-artboard-height-res-960="800"
				 data-artboard-height-res-640="910" data-artboard-height-res-480="780" data-artboard-height-res-320="520" data-artboard-height_vh="100"
				 data-artboard-valign="center" data-artboard-ovrflw="">
					<div class="t396__carrier t-bgimg" data-artboard-recid="69983652" data-original="https://static.tildacdn.com/tild3035-6637-4438-b264-326337616634/image.png"></div>
					<div class="t396__filter" data-artboard-recid="69983652"></div>
					<div class='t396__elem tn-elem tn-elem__699836521470209944682' data-elem-id='1470209944682' data-elem-type='text'
					 data-field-top-value="-185.5" data-field-top-res-960-value="-344" data-field-top-res-640-value="-325" data-field-top-res-480-value="-254.5"
					 data-field-top-res-320-value="-184" data-field-left-value="506" data-field-left-res-960-value="20" data-field-left-res-640-value="10"
					 data-field-left-res-480-value="16" data-field-left-res-320-value="3" data-field-width-value="690" data-field-width-res-960-value="940"
					 data-field-width-res-640-value="620" data-field-width-res-480-value="460" data-field-width-res-320-value="300" data-field-axisy-value="center"
					 data-field-axisx-value="center" data-field-container-value="grid" data-field-topunits-value="" data-field-leftunits-value=""
					 data-field-heightunits-value="" data-field-widthunits-value="">
						<div class='tn-atom' field='tn_text_1470209944682'>КОНСТРУКТОР КОММЕРЧЕСКИХ ПРЕДЛОЖЕНИЙ <br>
							<strong><span data-redactor-tag="span" style="color: rgb(60, 141, 188);">КП10</span></strong></div>
					</div>
					<div class='t396__elem tn-elem tn-elem__699836521504792630047' data-elem-id='1504792630047' data-elem-type='image' data-field-top-value="-34"
					 data-field-top-res-480-value="-11" data-field-left-value="0" data-field-left-res-480-value="-4" data-field-width-value="40"
					 data-field-width-res-480-value="30" data-field-axisy-value="bottom" data-field-axisx-value="center" data-field-container-value="window"
					 data-field-topunits-value="" data-field-leftunits-value="" data-field-heightunits-value="" data-field-widthunits-value="">
						<div class='tn-atom'> <img class='tn-atom__img t-img' data-original='https://static.tildacdn.com/tild3966-3462-4563-b235-333731376634/arrow_white.gif'
							 imgfield='tn_img_1504792630047'> </div>
					</div>
					<div class='t396__elem tn-elem tn-elem__699836521539088657485' data-elem-id='1539088657485' data-elem-type='video' data-field-top-value="10"
					 data-field-top-res-960-value="186" data-field-top-res-640-value="254" data-field-top-res-480-value="283" data-field-top-res-320-value="140"
					 data-field-left-value="-46" data-field-left-res-960-value="20" data-field-left-res-640-value="21" data-field-left-res-480-value="23"
					 data-field-left-res-320-value="5" data-field-height-value="459" data-field-height-res-640-value="354" data-field-height-res-480-value="263"
					 data-field-height-res-320-value="194" data-field-width-value="783" data-field-width-res-640-value="606" data-field-width-res-480-value="445"
					 data-field-width-res-320-value="311" data-field-axisy-value="top" data-field-axisx-value="left" data-field-container-value="grid"
					 data-field-topunits-value="" data-field-leftunits-value="" data-field-heightunits-value="" data-field-widthunits-value="">
						<div class='tn-atom t-bgimg' data-atom-video-has-cover='y' data-original="https://static.tildacdn.com/tild6464-3338-4132-a438-376238663138/image.png"
						 style="height:100%;background-image:url('https://static.tildacdn.com/tild6464-3338-4132-a438-376238663138/-/resize/20x/image.png');">
							<div class="tn-atom__video-play-link">
								<div class="tn-atom__video-play-icon"><svg width="70px" height="70px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50" style="background-color:#fff;border-radius:40px;-moz-border-radius:40px;-webkit-border-radius:40px;"><path fill="#000" d="M25 50c13.8 0 25-11.2 25-25S38.8 0 25 0 0 11.2 0 25s11.2 25 25 25zm-5-33.3l14.2 8.8L20 34.3V16.7z" class="st0"/></svg></div>
							</div>
							<style>
								.tn-atom__video-play-link {
									display: inline-block;
									position: absolute;
									left: 0;
									right: 0;
									top: 50%;
									-moz-transform: translateY(-50%);
									-ms-transform: translateY(-50%);
									-webkit-transform: translateY(-50%);
									-o-transform: translateY(-50%);
									transform: translateY(-50%);
									z-index: 1;
									cursor: pointer;
								}

								.tn-atom__video-play-icon {
									width: 70px;
									height: 70px;
									margin: 0 auto;
									-webkit-transition: all ease-in-out .2s;
									-moz-transition: all ease-in-out .2s;
									-o-transition: all ease-in-out .2s;
									transition: all ease-in-out .2s;
									display: block;
								}
							</style>
							<div class='tn-atom__videoiframe' data-youtubeid="Jl3qX4Fmg5c" style="width:100%; height:100%;"></div>
						</div>
					</div>
					<div class='t396__elem tn-elem tn-elem__699836521539089124396' data-elem-id='1539089124396' data-elem-type='button'
					 data-field-top-value="342" data-field-top-res-960-value="690" data-field-top-res-640-value="720" data-field-top-res-480-value="626"
					 data-field-top-res-320-value="410" data-field-left-value="757" data-field-left-res-960-value="40" data-field-left-res-640-value="200"
					 data-field-left-res-480-value="126" data-field-left-res-320-value="55" data-field-height-value="55" data-field-height-res-320-value="35"
					 data-field-width-value="210" data-field-width-res-320-value="210" data-field-axisy-value="top" data-field-axisx-value="left"
					 data-field-container-value="grid" data-field-topunits-value="" data-field-leftunits-value="" data-field-heightunits-value=""
					 data-field-widthunits-value="">
					 	<a href="#registerkp10" target="" class="t-btn " style="color:#ffffff;background-color:#3c8dbc;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
							<table style="width:100%; height:100%;">
								<tr>
									<td>Пробовать бесплатно</td>
								</tr>
							</table>
						</a>
					</div>
					<div class='t396__elem tn-elem tn-elem__699836521539089234640' data-elem-id='1539089234640' data-elem-type='text' data-field-top-value="231"
					 data-field-top-res-960-value="140" data-field-top-res-640-value="220" data-field-top-res-480-value="237" data-field-top-res-320-value="230"
					 data-field-left-value="726" data-field-left-res-960-value="-10" data-field-left-res-640-value="180" data-field-left-res-480-value="-14"
					 data-field-left-res-320-value="-789" data-field-width-value="560" data-field-width-res-320-value="280" data-field-axisy-value="top"
					 data-field-axisx-value="left" data-field-container-value="grid" data-field-topunits-value="" data-field-leftunits-value=""
					 data-field-heightunits-value="" data-field-widthunits-value="">
						<div class='tn-atom' field='tn_text_1539089234640'>
							<div style="margin-left: 40px;"> Готовое коммерческое предложение за 10 кликов
							</div>
						</div>
					</div>
					<div class='t396__elem tn-elem tn-elem__699836521539089600586' data-elem-id='1539089600586' data-elem-type='image' data-field-top-value="501"
					 data-field-top-res-960-value="661" data-field-top-res-640-value="621" data-field-top-res-480-value="557" data-field-top-res-320-value="350"
					 data-field-left-value="-40" data-field-left-res-960-value="750" data-field-left-res-640-value="560" data-field-left-res-480-value="36"
					 data-field-left-res-320-value="270" data-field-width-value="50" data-field-width-res-320-value="30" data-field-axisy-value="top"
					 data-field-axisx-value="left" data-field-container-value="grid" data-field-topunits-value="" data-field-leftunits-value=""
					 data-field-heightunits-value="" data-field-widthunits-value=""> <a class='tn-atom' href="https://t.me/KP10club" target="_blank"> <img class='tn-atom__img t-img' data-original='https://static.tildacdn.com/tild3162-6664-4130-a166-353337303339/image.png' imgfield='tn_img_1539089600586'> </a>						</div>
					<div class='t396__elem tn-elem tn-elem__699836521539089669309' data-elem-id='1539089669309' data-elem-type='text'
					 data-field-top-value="511" data-field-top-res-960-value="670" data-field-top-res-640-value="620" data-field-top-res-480-value="557"
					 data-field-top-res-320-value="352" data-field-left-value="25" data-field-left-res-960-value="470" data-field-left-res-640-value="280"
					 data-field-left-res-480-value="96" data-field-left-res-320-value="21" data-field-width-value="560" data-field-width-res-320-value="230"
					 data-field-axisy-value="top" data-field-axisx-value="left" data-field-container-value="grid" data-field-topunits-value=""
					 data-field-leftunits-value="" data-field-heightunits-value="" data-field-widthunits-value="">
						<div class='tn-atom' field='tn_text_1539089669309'>Оперативно ответим в <a href="https://t.me/KP10club" target="_blank" style="color:#ffffff !important;border-bottom-color: #ffffff;">Telegram</a></div>
					</div>
				</div>
			</div>
			<script>
				$( document ).ready(function() { t396_init('69983652');
	});
			</script>
			<!-- /T396 -->
		</div>
		<div id="rec68200247" class="r t-rec t-rec_pt_0 t-rec_pb_0" style="padding-top:0px;padding-bottom:0px; " data-record-type="270">
			<div class="t270" data-offset-top="100"></div>
			<script type="text/javascript">
				$(document).ready(function(){ setTimeout(function(){ var $root = $('html, body'); $('a[href*=#]:not([href=#],.carousel-control,.t-carousel__control,[href^=#price],[href^=#popup],[href^=#prodpopup],[href^=#order])').click(function() { var target = $(this.hash); if (target.length == 0){ target = $('a[name="' + this.hash.substr(1) + '"]'); } if (target.length == 0){ return true; } $root.animate({ scrollTop: target.offset().top - 100 }, 500); return false; }); }, 500); });
			</script>
		</div>
		<div id="rec67749407" class="r t-rec" style=" " data-record-type="215">
			<a name="about" style="font-size:0;"></a>
		</div>
		<div id="rec67749408" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px; " data-record-type="467">
			<!-- T467 -->
			<div class="t467">
				<div class="t-container t-align_center">
					<div class="t-col t-col_10 t-prefix_1">
						<div class="t467__title t-title t-title_lg t-margin_auto" field="title" style="font-size:42px;">О сервисе</div>
						<div class="t467__descr t-descr t-descr_xl t-margin_auto" field="descr" style="">КП10 — это универсальный конструктор коммерческих предложений, который в несколько раз увеличивает скорость подготовки
							КП и подчёркивает ваше УТП на фоне конкурентов<br /></div>
					</div>
				</div>
			</div>
		</div>
		<div id="rec67749409" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px;background-color:#f0f0f0; "
		 data-record-type="490" data-bg-color="#f0f0f0">
			<!-- t490 -->
			<div class="t490">
				<div class="t-section__container t-container">
					<div class="t-col t-col_12">
						<div class="t-section__topwrapper t-align_center">
							<div class="t-section__title t-title t-title_xs" field="btitle">Зачем конструктор КП вам ?</div>
							<div class="t-section__descr t-descr t-descr_xl t-margin_auto" field="bdescr">Мы тестировали систему на себе 2 года и воплотили в нашем сервисе самые эффективные инструменты</div>
						</div>
					</div>
				</div>
				<div class="t-container">
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/lib/tildaicon/31306262-6530-4432-a630-393239646162/-/empty/kideducate_honor.svg"
						 data-original="https://static.tildacdn.com/lib/tildaicon/31306262-6530-4432-a630-393239646162/kideducate_honor.svg"
						 class="t490__img t-img" imgfield="li_img__1476968690512" style="width:80px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="" field="li_title__1476968690512">В два раза быстрее, чем сейчас</div>
							<div class="t-descr t-descr_xxs" style="" field="li_descr__1476968690512">Скорость подготовки КП менеджером увеличивается, минимум — в 2 раза <br /></div>
						</div>
					</div>
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/lib/tildaicon/62653366-6130-4162-b430-663434663161/-/empty/re_man.svg" data-original="https://static.tildacdn.com/lib/tildaicon/62653366-6130-4162-b430-663434663161/re_man.svg"
						 class="t490__img t-img" imgfield="li_img__1476968700508" style="width:80px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="" field="li_title__1476968700508">Сразу 3 ценовых варианта<br /></div>
							<div class="t-descr t-descr_xxs" style="" field="li_descr__1476968700508">Предлагайте клиентам несколько вариантов на выбор, это поможет увеличить средний чек</div>
						</div>
					</div>
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/lib/tildaicon/62386234-3663-4332-b337-616465393430/-/empty/Event_agency_miracle.svg"
						 data-original="https://static.tildacdn.com/lib/tildaicon/62386234-3663-4332-b337-616465393430/Event_agency_miracle.svg"
						 class="t490__img t-img" imgfield="li_img__1476968722790" style="width:80px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="" field="li_title__1476968722790">Качайте КП в pdf, excel или в web</div>
							<div class="t-descr t-descr_xxs" style="" field="li_descr__1476968722790">Наши опытные профессионалы помогут расти вашему бизнесу, внедряя новые технологии работы.<br /></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="rec67749410" class="r t-rec" style=" " data-record-type="215">
			<a name="services" style="font-size:0;"></a>
		</div>
		<div id="rec67749411" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px;background-color:#ffffff; "
		 data-record-type="509" data-bg-color="#ffffff">
			<!-- t509 -->
			<div class="t509">
				<div class="t-section__container t-container">
					<div class="t-col t-col_12">
						<div class="t-section__topwrapper t-align_center">
							<div class="t-section__title t-title t-title_xs" field="btitle">Как это работает</div>
							<div class="t-section__descr t-descr t-descr_xl t-margin_auto" field="bdescr">Всё очень просто, зарегистрируйтесь<br /> и попробуйте сами бесплатно<br /></div>
						</div>
					</div>
				</div>
				<div class="t-container">
					<div class="t509__colwrapper t-col t-col_12 ">
						<div class="t509__col t-col t-col_6 t509__mobileimg">
							<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1476897493817" data-original="https://static.tildacdn.com/tild3865-3263-4430-b739-386137653731/image.png"
							 style="background-image:url('https://static.tildacdn.com/tild3865-3263-4430-b739-386137653731/-/resize/20x/image.png');"
							 data-image-width="800" data-image-height="450"></div>
						</div>
						<div class="t509__col t-col t-col_6 t509__leftcol t509__desktopimg">
							<div class="t509__imgwrapper" style="max-width:800px;">
								<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1476897493817" data-original="https://static.tildacdn.com/tild3865-3263-4430-b739-386137653731/image.png"
								 style="background-image:url('https://static.tildacdn.com/tild3865-3263-4430-b739-386137653731/-/resize/20x/image.png');"
								 data-image-width="800" data-image-height="450"></div>
							</div>
						</div>
						<div class="t509__col t-col t-col_6 t509__rightcol">
							<div class="t509__textwrapper t-align_left" style="max-width:550px;">
								<div class="t509__content t-valign_middle">
									<div class="t509__box">
										<div class="t509__title t-heading t-heading_xs t-margin_auto" field="li_title__1476897493817" style="">Безопасно и индивидуально <br /><strong></strong></div>
										<div class="t509__descr t-descr t-descr_sm t-margin_auto" field="li_descr__1476897493817" style="">
											<ul>
												<li>На нашем портале у вас будет личный кабинет </li>
												<li>Вся информация хранится на защищённом сервере </li>
												<li>Можете отредактировать любое ранее сделанное вами КП </li>
												<li>Новое КП на основе шаблона или ранее созданного КП</li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="t509__separator t-clear" style=""></div>
						<div class="t509__col t-col t-col_6 t509__mobileimg">
							<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1481206097863" data-original="https://static.tildacdn.com/tild3934-3132-4565-a162-363465396565/image.png"
							 style="background-image:url('https://static.tildacdn.com/tild3934-3132-4565-a162-363465396565/-/resize/20x/image.png');"
							 data-image-width="800" data-image-height="450"></div>
						</div>
						<div class="t509__col t-col t-col_6 t509__leftcol">
							<div class="t509__textwrapper t-align_left" style="max-width:550px;">
								<div class="t509__content t-valign_middle">
									<div class="t509__box">
										<div class="t509__title t-heading t-heading_xs t-margin_auto" field="li_title__1481206097863" style="">Просто и понятно <br /></div>
										<div class="t509__descr t-descr t-descr_sm t-margin_auto" field="li_descr__1481206097863" style="">
											<ul>
												<li>Выбираете из десятков готовых шаблонов</li>
												<li>Любые блоки можно просто выделить и переместить </li>
												<li>Всё выполнено в едином стиле </li>
												<li>Встроенный фоторедактор </li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="t509__col t-col t-col_6 t509__rightcol t509__desktopimg">
							<div class="t509__imgwrapper" style="max-width:800px;">
								<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1481206097863" data-original="https://static.tildacdn.com/tild3934-3132-4565-a162-363465396565/image.png"
								 style="background-image:url('https://static.tildacdn.com/tild3934-3132-4565-a162-363465396565/-/resize/20x/image.png');"
								 data-image-width="800" data-image-height="450"></div>
							</div>
						</div>
						<div class="t509__separator t-clear" style=""></div>
						<div class="t509__col t-col t-col_6 t509__mobileimg">
							<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1481206099920" data-original="https://static.tildacdn.com/tild3562-6439-4663-b666-646435303565/POLYTELL-0khrq.jpg"
							 style="background-image:url('https://static.tildacdn.com/tild3562-6439-4663-b666-646435303565/-/resize/20x/POLYTELL-0khrq.jpg');"
							 data-image-width="800" data-image-height="450"></div>
						</div>
						<div class="t509__col t-col t-col_6 t509__leftcol t509__desktopimg">
							<div class="t509__imgwrapper" style="max-width:800px;">
								<div class="t509__blockimg t-bgimg" bgimgfield="li_img__1481206099920" data-original="https://static.tildacdn.com/tild3562-6439-4663-b666-646435303565/POLYTELL-0khrq.jpg"
								 style="background-image:url('https://static.tildacdn.com/tild3562-6439-4663-b666-646435303565/-/resize/20x/POLYTELL-0khrq.jpg');"
								 data-image-width="800" data-image-height="450"></div>
							</div>
						</div>
						<div class="t509__col t-col t-col_6 t509__rightcol">
							<div class="t509__textwrapper t-align_left" style="max-width:550px;">
								<div class="t509__content t-valign_middle">
									<div class="t509__box">
										<div class="t509__title t-heading t-heading_xs t-margin_auto" field="li_title__1481206099920" style="">Удобно и быстро <br /></div>
										<div class="t509__descr t-descr t-descr_sm t-margin_auto" field="li_descr__1481206099920" style="">
											<ul>
												<li>КП собирается за 10 минут при помощи конструктора </li>
												<li>Разобраться в системе — полчаса </li>
												<li>Список товаров можно загрузить из Excel </li>
												<li>Удобное хранение фото по папкам </li>
												<li>У всех менеджеров компании — единая база КП с удобным поиском </li>
												<li>Получаете уведомление, когда клиент открывает ваше КП </li>
												<li>Статус КП меняется автоматически </li>
											</ul>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				$(window).resize(function() { t509_setHeight('67749411'); }); $(document).ready(function() { t509_setHeight('67749411'); });
			</script>
		</div>
		<div id="rec67765498" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px;background-color:#efefef; "
		 data-animationappear="off" data-record-type="774" data-bg-color="#efefef">
			<!-- T774 -->
			<div class="t774 ">
				<div class="t-section__container t-container">
					<div class="t-col t-col_12">
						<div class="t-section__topwrapper t-align_center">
							<div class="t-section__title t-title t-title_xs" field="btitle">Примеры КП</div>
							<div class="t-section__descr t-descr t-descr_xl t-margin_auto" field="bdescr">Ознакомьтесь с примерами коммерческих предложений, которые с лёгкостью можно создать в КП10</div>
						</div>
					</div>
				</div>
				<div class="t774__container t-container t774__container_mobile-grid" data-blocks-per-row="3">
					<div class="t774__col t-col t-col_4 t-align_left t-item t774__col_mobile-grid">
						<div class="t774__wrapper" style="">
							<a href="https://kp10.pro/JStl7" target="_blank">
								<div class="t774__imgwrapper" style="padding-bottom:305.55555555556%;">
									<div class="t774__bgimg t-bgimg" bgimgfield="li_img__1499960678558" data-original="https://static.tildacdn.com/tild6466-3437-4431-a337-393131663166/screencapture-kp10-r.png"
									 style="background-image:url('https://static.tildacdn.com/tild6466-3437-4431-a337-393131663166/-/resize/20x/screencapture-kp10-r.png');"></div>
								</div>
							</a>
							<div class="t774__content">
								<a href="https://kp10.pro/JStl7" target="_blank">
									<div class="t774__textwrapper ">
										<div class="t774__title t-name t-name_md" field="li_title__1499960678558" style="">Для Кейтеринговой компании<br /></div>
									</div>
								</a>
								<div class="t774__btn-wrapper ">
									<a href="https://kp10.pro/JStl7" target="_blank" class="t774__btn t774__btn t-btn t-btn_xs" style="color:#ffffff;background-color:#13ce66;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">
										<table style="width:100%; height:100%;">
											<tr>
												<td>Подробнее</td>
											</tr>
										</table>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="t774__col t-col t-col_4 t-align_left t-item t774__col_mobile-grid">
						<div class="t774__wrapper" style="">
							<a href="https://kp10.pro/OdHA2" target="_blank">
								<div class="t774__imgwrapper" style="padding-bottom:305.55555555556%;">
									<div class="t774__bgimg t-bgimg" bgimgfield="li_img__1495010946049" data-original="https://static.tildacdn.com/tild3165-3634-4432-b537-343334313062/screencapture-kp10-r.png"
									 style="background-image:url('https://static.tildacdn.com/tild3165-3634-4432-b537-343334313062/-/resize/20x/screencapture-kp10-r.png');"></div>
								</div>
							</a>
							<div class="t774__content">
								<a href="https://kp10.pro/OdHA2" target="_blank">
									<div class="t774__textwrapper ">
										<div class="t774__title t-name t-name_md" field="li_title__1495010946049" style="">Аренда Специальной техники</div>
									</div>
								</a>
								<div class="t774__btn-wrapper ">
									<a href="https://kp10.pro/OdHA2" target="_blank" class="t774__btn t774__btn t-btn t-btn_xs" style="color:#ffffff;background-color:#13ce66;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">
										<table style="width:100%; height:100%;">
											<tr>
												<td>Подробнее</td>
											</tr>
										</table>
									</a>
								</div>
							</div>
						</div>
					</div>
					<div class="t774__col t-col t-col_4 t-align_left t-item t774__col_mobile-grid">
						<div class="t774__wrapper" style="">
							<a href="https://kp10.pro/pDwNQ" target="_blank">
								<div class="t774__imgwrapper" style="padding-bottom:305.55555555556%;">
									<div class="t774__bgimg t-bgimg" bgimgfield="li_img__1495010928665" data-original="https://static.tildacdn.com/tild3139-3462-4638-b035-366436616536/screencapture-kp10-r.png"
									 style="background-image:url('https://static.tildacdn.com/tild3139-3462-4638-b035-366436616536/-/resize/20x/screencapture-kp10-r.png');"></div>
								</div>
							</a>
							<div class="t774__content">
								<a href="https://kp10.pro/pDwNQ" target="_blank">
									<div class="t774__textwrapper ">
										<div class="t774__title t-name t-name_md" field="li_title__1495010928665" style="">Техническое обеспечение Event</div>
									</div>
								</a>
								<div class="t774__btn-wrapper ">
									<a href="https://kp10.pro/pDwNQ" target="_blank" class="t774__btn t774__btn t-btn t-btn_xs" style="color:#ffffff;background-color:#13ce66;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">
										<table style="width:100%; height:100%;">
											<tr>
												<td>Подробнее</td>
											</tr>
										</table>
									</a>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function() { t774_init('67765498');
	});
			</script>
		</div>
		<div id="rec67768860" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px;background-color:#242424; "
		 data-record-type="490" data-bg-color="#242424">
			<!-- t490 -->
			<div class="t490">
				<div class="t-section__container t-container">
					<div class="t-col t-col_12">
						<div class="t-section__topwrapper t-align_center">
							<div class="t-section__title t-title t-title_xs" field="btitle">
								<div style="color:#ffffff;" data-customstyle="yes">Интеграция с CRM системами</div>
							</div>
							<div class="t-section__descr t-descr t-descr_xl t-margin_auto" field="bdescr">
								<div style="color:#ffffff;" data-customstyle="yes">Используйте КП10 независимо или интегрируйте <br />в вашу CRM <br /></div>
							</div>
						</div>
					</div>
				</div>
				<div class="t-container">
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/tild3565-3138-4263-b730-323436323831/-/empty/image.png" data-original="https://static.tildacdn.com/tild3565-3138-4263-b730-323436323831/image.png"
						 class="t490__img t-img" imgfield="li_img__1476968690512" style="width:200px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="color:#ffffff;" field="li_title__1476968690512">Создавайте КП<br />прямо из сделки Мегаплана</div>
						</div>
					</div>
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/tild3834-3534-4365-a139-313930373737/-/empty/image.png" data-original="https://static.tildacdn.com/tild3834-3534-4365-a139-313930373737/image.png"
						 class="t490__img t-img" imgfield="li_img__1476968700508" style="width:200px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="color:#ffffff;" field="li_title__1476968700508">Создавайте КП<br /> прямо из сделки amoCRM<br /></div>
						</div>
					</div>
					<div class="t490__col t-col t-col_4 t-align_center t-item"> <img src="https://static.tildacdn.com/tild6662-6463-4436-a362-346438353538/-/empty/image.png" data-original="https://static.tildacdn.com/tild6662-6463-4436-a362-346438353538/image.png"
						 class="t490__img t-img" imgfield="li_img__1476968722790" style="width:200px;" />
						<div class="t490__wrappercenter ">
							<div class="t-name t-name_sm" style="color:#ffffff;" field="li_title__1476968722790">Интеграция в работе <br />план выхода 1 ноября 2018</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="rec67752739" class="r t-rec t-rec_pt_30 t-rec_pb_30" style="padding-top:30px;padding-bottom:30px; " data-animationappear="off"
		 data-record-type="599">
			<!-- T599 -->
			<div class="t599">
				<div class="t-section__container t-container">
					<div class="t-col t-col_12">
						<div class="t-section__topwrapper t-align_center">
							<div class="t-section__title t-title t-title_xs" field="btitle">Тарифы</div>
							<div class="t-section__descr t-descr t-descr_xl t-margin_auto" field="bdescr">Перейдите на КП10, чтобы увеличить продажи вдвое <br /></div>
						</div>
					</div>
				</div>
				<div class="t-container t599__withfeatured">
					<div class="t599__col t-col t-col_4 t-align_center ">
						<div class="t599__content" style="border: 1px solid #e0e6ed; border-radius: 9px;">
							<div class="t599__title t-name t-name_lg" field="title" style="">FREE</div>
							<div class="t599__subtitle t-descr t-descr_xxs" field="subtitle" style="">Бессрочно<br /></div>
							<div class="t599__price t-title t-title_xs" field="price" style="">0 ₽</div>
							<div class="t599__descr t-descr t-descr_xs" field="descr" style="">Полный доступ к всему функционалу конструктора.<br />С логотипом сервиса на Вашем КП.<br />Позволит провести <br />полноценный
								тест системы, не ограниченный по времени</div>
							<a href="#registerkp10" target="_blank" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#13ce66;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
								<table style="width:100%; height:100%;">
									<tr>
										<td>Начать</td>
									</tr>
								</table>
							</a>
						</div>
					</div>
					<div class="t599__col t-col t-col_4 t-align_center t599__featured">
						<div class="t599__content" style="border: 1px solid #e0e6ed; border-radius: 9px;">
							<div class="t599__title t-name t-name_lg" field="title2" style="">Месяц</div>
							<div class="t599__subtitle t-descr t-descr_xxs" field="subtitle2" style="">Ежемесячная оплата лицензий<br /></div>
							<div class="t599__price t-title t-title_xs" field="price2" style="">330 ₽</div>
							<div class="t599__descr t-descr t-descr_xs" field="descr2" style="">
								<ul>
									<li>Одна лицензия - один пользователь единовременно.</li>
								</ul>
							</div>
							<a href="#registerkp10" target="" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#13ce66;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
								<table style="width:100%; height:100%;">
									<tr>
										<td>Начать</td>
									</tr>
								</table>
							</a>
						</div>
					</div>
					<div class="t599__col t-col t-col_4 t-align_center ">
						<div class="t599__content" style="border: 1px solid #e0e6ed; border-radius: 9px;">
							<div class="t599__title t-name t-name_lg" field="title3" style="">Год</div>
							<div class="t599__subtitle t-descr t-descr_xxs" field="subtitle3" style="">Скидка 15% за годовую оплату<br /></div>
							<div class="t599__price t-title t-title_xs" field="price3" style="">
								<div style="line-height:36px;" data-customstyle="yes">3366 ₽<br /><del><span data-redactor-tag="span" style="font-size: 26px;"><span data-redactor-style="color: rgb(83, 81, 81);" style="color: rgb(83, 81, 81);">3960 ₽</span></span></del><del></del><br
									/></div>
							</div>
							<div class="t599__descr t-descr t-descr_xs" field="descr3" style="">
								<ul>
									<li>Одна лицензия - один пользователь единовременно.</li>
									<li></li>
								</ul>
							</div>
							<a href="#registerkp10" target="" class="t599__btn t-btn t-btn_sm" style="color:#ffffff;background-color:#13ce66;border-radius:30px; -moz-border-radius:30px; -webkit-border-radius:30px;">
								<table style="width:100%; height:100%;">
									<tr>
										<td>Начать</td>
									</tr>
								</table>
							</a>
						</div>
					</div>
				</div>
			</div>
			<style type="text/css">
				#rec67752739 .t599__featured .t599__content {
					box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.10) !important;
				}
			</style>
			<script type="text/javascript">
				$(document).ready(function() { setTimeout(function(){ t599_init('67752739'); }, 500); $(window).bind('resize', t_throttle(function(){t599_init('67752739')}, 250)); $('.t599').bind('displayChanged',function(){ t599_init('67752739'); }); }); $(window).load(function() { t599_init('67752739'); });
			</script>
		</div>
		<div id="rec67749414" class="r t-rec t-rec_pt_60 t-rec_pb_60" style="padding-top:60px;padding-bottom:60px;background-color:#242424; "
		 data-record-type="551" data-bg-color="#242424">
			<!-- t551-->
			<div class="t551">
				<div class="t-container">
					<div class="t-col t-col_6 t-prefix_3 t-align_center">
						<div class="t551__title t-name t-name_xs" style="color:#ffffff;letter-spacing:1px;" field="title" data-animate-order="1">МЫ ВСЕГДА ОТКРЫТЫ К ОБЩЕНИЮ<br /></div>
						<div class="t551__contacts t-title t-title_xxs" style="color:#ffffff;font-weight:300;" field="text" data-animate-order="2"
						 data-animate-delay="0.3">
							<a href="tel:74996488992" onclick="yaCounter50570539.reachGoal('phone'); return true;" style="">+ 7 (499) 648-89-92<br /></a>
							<a href="mailto:info@kp10.pro" onclick="yaCounter50570539.reachGoal('e-mail'); return true;" style="">info@kp10.pro<br /></a>							г. Москва, ул. Клары Цеткин д.4<br />
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="rec69774452" class="r t-rec" style=" " data-animationappear="off" data-record-type="702">
			<!-- T702 -->
			<div class="t702">
				<div class="t-popup" data-tooltip-hook="#registerkp10">
					<div class="t-popup__close">
						<div class="t-popup__close-wrapper"> <svg class="t-popup__close-icon" width="23px" height="23px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg"
							 xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="#fff" fill-rule="evenodd"> <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect> <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect> </g> </svg>							</div>
					</div>
					<div class="t-popup__container t-width t-width_6">
						<div class="t702__wrapper">
							<div class="t702__text-wrapper t-align_center">
								<div class="t702__title t-title t-title_xxs" style="">
									<div style="color:#3c8dbc;" data-customstyle="yes">Регистрация в КП10</div>
								</div>
								<div class="t702__descr t-descr t-descr_xs" style="">Создайте собственные шаблоны в КП10 и продавайте больше</div>
							</div>
							{{-- Vue --}}
							<div id="registration">
								<form id="form69774452" name='form69774452' role="form" action='' method='POST' data-formactiontype="2" data-inputbox=".t-input-group"
								 data-success-url="https://kp10.pro" class="t-form js-form-proccess t-form_inputs-total_2" data-success-callback="t702_onSuccess">
									<input type="hidden" name="formservices[]" value="356e70ef91aeb28a84fdd3c5e3a0dcb2" class="js-formaction-services">
									<input type="hidden" name="formservices[]" value="dd10530e10860df8e2440a35ab672095" class="js-formaction-services">
									<input type="hidden" name="formservices[]" value="25b1d1ea2d8b3605c6d8ff7ad92350c8" class="js-formaction-services">
									<div class="js-successbox t-form__successbox t-text t-text_md" :style="{display: registered?'block':'none'}">Регистрация прошла успешно!</div>
									<div class="t-form__inputsbox" :style="{display: registered?'none':'block'}">
										<div :class="{'js-error-control-box': errors.name}" class="t-input-group t-input-group_nm" data-input-lid="1495810359387">
											<div class="t-input-block">
												<input v-model="name" type="text" name="Имя" class="t-input js-tilda-rule" value="" placeholder="Имя" data-tilda-req="1"
												 data-tilda-rule="name" style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
												<div class="t-input-error">@{{this.errors.detail.name}}</div>
											</div>
										</div>
										<div :class="{'js-error-control-box': errors.email}" class="t-input-group t-input-group_em" data-input-lid="1495810354468">
											<div class="t-input-block">
												<input v-model="email" type="text" name="Email" class="t-input js-tilda-rule " value="" placeholder="E-mail" data-tilda-req="1"
												 data-tilda-rule="email" style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
												<div class="t-input-error">@{{this.errors.detail.email}}</div>
											</div>
										</div>
										<div :class="{'js-error-control-box': errors.phone}" class="t-input-group t-input-group_em" data-input-lid="1495810354468">
											<div class="t-input-block">
												<input v-model="phone" type="text" name="Phone" class="t-input js-tilda-rule " value="" placeholder="Номер телефона" data-tilda-req="1"
												 data-tilda-rule="phone" style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
												<div class="t-input-error">@{{this.errors.detail.phone}}</div>
											</div>
										</div>
										<div class="t-form__errorbox-middle">
											<div class="js-errorbox-all t-form__errorbox-wrapper" :style="{display: errors.name || errors.email ? 'block': 'none'}">
												<div class="t-form__errorbox-text t-text t-text_md">
													<p class="t-form__errorbox-item js-rule-error js-rule-error-all"></p>
													<p :style="{display: errors.name || errors.email || errors.phone ? 'block': 'none'}" class="t-form__errorbox-item js-rule-error js-rule-error-req">Проверьте правильность заполнения полей</p>
													<p class="t-form__errorbox-item js-rule-error js-rule-error-email"></p>
													<p class="t-form__errorbox-item js-rule-error js-rule-error-name"></p>
													<p class="t-form__errorbox-item js-rule-error js-rule-error-phone"></p>
													<p class="t-form__errorbox-item js-rule-error js-rule-error-string"></p>
												</div>
											</div>
										</div>
										<div class="t-form__submit">
											<button @click.prevent="onSubmit" :class="{'t-btn_sending': request}" type="button" class="t-submit" style="color:#ffffff;background-color:#3c8dbc;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">Пробовать бесплатно</button>
										</div>
									</div>
									<div class="t-form__errorbox-bottom" :style="{display: errors.registration ? 'block': 'none'}">
										<div class="js-errorbox-all t-form__errorbox-wrapper" :style="{display: errors.registration ? 'block': 'none'}">
											<div class="t-form__errorbox-text t-text t-text_md">
												<p class="t-form__errorbox-item js-rule-error js-rule-error-all"></p>
												<p class="t-form__errorbox-item js-rule-error js-rule-error-req"></p>
												<p class="t-form__errorbox-item js-rule-error js-rule-error-email"></p>
												<p class="t-form__errorbox-item js-rule-error js-rule-error-name"></p>
												<p class="t-form__errorbox-item js-rule-error js-rule-error-phone"></p>
												<p :style="{display: errors.registration ? 'block': 'none'}" class="t-form__errorbox-item js-rule-error js-rule-error-string">@{{errors.detail.registration}}</p>
											</div>
										</div>
									</div>
								</form>
							</div>
							<style>
								#rec69774452 input::-webkit-input-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 input::-moz-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 input:-moz-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 input:-ms-input-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 textarea::-webkit-input-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 textarea::-moz-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 textarea:-moz-placeholder {
									color: #000000;
									opacity: 0.5;
								}

								#rec69774452 textarea:-ms-input-placeholder {
									color: #000000;
									opacity: 0.5;
								}
							</style>
							<div class="t702__form-bottom-text t-text t-text_xs t-align_center">Нажимая на кнопку, вы соглашаетесь на обработку персональных данных<br /><a href="http://files.polytell.ru/Politika_obrabotki_D%D0%B0n.pdf">и политикой конфиденциальности.</a></div>
						</div>
					</div>
				</div>
			</div>
			<script type="text/javascript">
				$(document).ready(function(){ setTimeout(function(){ t702_initPopup('69774452'); }, 500);});
			</script>
		</div>
	</div>
	<!--/allrecords-->

	<script>
		window.referer = '{{request()->headers->get('referer')}}';
	</script>
	<script src="{{asset('js/welcome.min.js')}}"></script>

</body>

</html>