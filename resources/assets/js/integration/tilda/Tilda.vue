<template>
   <div id="rec71742476" class="r t-rec" style="opacity: 1;" data-animationappear="off" data-record-type="702">
	<div class="t702">
		<div class="t-popup" data-tooltip-hook="#popup:KP10-1" style="display: none;">
			<div class="t-popup__close">
				<div class="t-popup__close-wrapper"> <svg class="t-popup__close-icon" width="23px" height="23px" viewBox="0 0 23 23" version="1.1" xmlns="http://www.w3.org/2000/svg"
					 xmlns:xlink="http://www.w3.org/1999/xlink"> <g stroke="none" stroke-width="1" fill="#fff" fill-rule="evenodd"> <rect transform="translate(11.313708, 11.313708) rotate(-45.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect> <rect transform="translate(11.313708, 11.313708) rotate(-315.000000) translate(-11.313708, -11.313708) " x="10.3137085" y="-3.6862915" width="2" height="30"></rect> </g> </svg>					</div>
			</div>
			<div class="t-popup__container t-width t-width_6 t-popup__container-animated">
				<div class="t702__wrapper">
					<div class="t702__text-wrapper t-align_center">
						<div class="t702__title t-title t-title_xxs" style="">
							<div style="color:#3c8dbc;" data-customstyle="yes"><strong>Регистрация в КП10</strong></div>
						</div>
						<div class="t702__descr t-descr t-descr_xs" style="">Создайте собственные шаблоны в КП10 и продавайте больше</div>
					</div>
					<form id="form71742476" name="form71742476" role="form" action="" method="POST" data-formactiontype="0" data-inputbox=".t-input-group"
					 class="t-form js-form-proccess t-form_inputs-total_1 " data-success-callback="t702_onSuccess">
						<div class="js-successbox t-form__successbox t-text t-text_md" style="display:none;"></div>
						<div class="t-form__inputsbox">
							<div :class="{'js-error-control-box': errors.email}" class="t-input-group t-input-group_em" data-input-lid="1495810354468">
								<div class="t-input-block"> 
                                    <input @keyup.enter.prevent="onSubmit" v-model="email" type="text" name="Email" class="t-input js-tilda-rule " value="" placeholder="E-mail" data-tilda-req="1" data-tilda-rule="email"
									 style="color:#000000; border:1px solid #c9c9c9; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;">
									<div class="t-input-error">{{this.errors.detail.email}}</div>
								</div>
							</div>
							<div class="t-form__errorbox-middle">
								<div class="js-errorbox-all t-form__errorbox-wrapper" :style="{display: errors.name || errors.email ? 'block': 'none'}">
									<div class="t-form__errorbox-text t-text t-text_md">
										<p class="t-form__errorbox-item js-rule-error js-rule-error-all"></p>
										<p class="t-form__errorbox-item js-rule-error js-rule-error-req"></p>
										<p :style="{display: errors.email ? 'block': 'none'}" class="t-form__errorbox-item js-rule-error js-rule-error-email">Проверьте правильность заполнения полей</p>
										<p class="t-form__errorbox-item js-rule-error js-rule-error-name"></p>
										<p class="t-form__errorbox-item js-rule-error js-rule-error-phone"></p>
										<p class="t-form__errorbox-item js-rule-error js-rule-error-string"></p>
									</div>
								</div>
							</div>
							<div class="t-form__submit"> 
                                <button type="button" @click.prevent="onSubmit" :class="{'t-btn_sending': request}" class="t-submit" style="color:#ffffff;background-color:#3c8dbc;border-radius:5px; -moz-border-radius:5px; -webkit-border-radius:5px;">
                                    Пробовать бесплатно
                                </button>								
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
                                    <p :style="{display: errors.registration ? 'block': 'none'}" class="t-form__errorbox-item js-rule-error js-rule-error-string">{{errors.detail.registration}}</p>
								</div>
							</div>
						</div>
						<div style="position: absolute; left: -5000px; bottom:0;"><input type="text" name="form-spec-comments" value="Its good" class="js-form-spec-comments" tabindex="-1"></div>
					</form>
					
					<div class="t702__form-bottom-text t-text t-text_xs t-align_center">Нажимая на кнопку, вы соглашаетесь на обработку персональных данных<br><a href="http://files.polytell.ru/Politika_obrabotki_D%D0%B0n.pdf">и политикой конфиденциальности.</a></div>
				</div>
			</div>
		</div>
	</div>
</div>
</template>
<style>
    #rec71742476 input::-webkit-input-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 input::-moz-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 input:-moz-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 input:-ms-input-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 textarea::-webkit-input-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 textarea::-moz-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 textarea:-moz-placeholder {
        color: #000000;
        opacity: 0.5;
    }

    #rec71742476 textarea:-ms-input-placeholder {
        color: #000000;
        opacity: 0.5;
    }
</style>
<style>
    #rec71742476 .t-btn[data-btneffects-first],
    #rec71742476 .t-btn[data-btneffects-second],
    #rec71742476 .t-submit[data-btneffects-first],
    #rec71742476 .t-submit[data-btneffects-second] {
        position: relative;
        overflow: hidden;
        -webkit-transform: translate3d(0, 0, 0);
        transform: translate3d(0, 0, 0);
    }
</style>
<script>
export default {
  data(){
      return {
           errors: {
            name: false,
            email: false,
            registration: false,
            detail: {
                name: '',
                email: '',
                phone: '',
                registration: ''
            }
        },
        name: '',
        email: '',
        phone: '',
        registered: false,
        request: false //Ajax
      }
  },
  mounted(){
    setTimeout(function(){ t702_initPopup('71742476'); }, 500);
  },
  methods:{
      onSubmit: function () {
          
            //Reset errors
            this.clearErrors();

            if (!this.validEmail(this.email)) {
                this.errors.email = true;
                this.errors.detail.email = 'Укажите корректный адрес электронной почты';
                return;
            }
            //Show ajax
            this.request = true;

            axios.post('https://admin.kp10.pro/integration/tilda/register', { email: this.email })
                .then(response => {
                    //Send stat
                    //TODO: додати скрипт ститистики на сторінку
                    try{
                    yaCounter50570539.reachGoal('registr');
                    }catch(e){
                        console.log(e);
                    }
                    //Hide ajax
                    this.request = false;
                    //Show success message
                    this.registered = true;
                    //Open page
                    location.href = response.data;
                })
                .catch((error) => {
                    //Hide ajax
                    this.request = false;
                    let message = '';
                    if (error && error.response.status === 422) {
                        if (typeof error.response.data.errors === 'object') {
                            for (let i in error.response.data.errors) {
                                let er = error.response.data.errors[i];
                                message += er[0] + "\n";
                            }
                        } else {
                            message = error.response.data.errors;
                        }
                    } else {
                        message = 'Ошибка сервера. ' + error.toString();
                    }
                    //Set error
                    this.errors.registration = true;
                    this.errors.detail.registration = message;
                });
            // 
        

        },
        validEmail: function (email) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(email);
        },
        clearErrors: function () {
            this.errors.name = false;
            this.errors.email = false;
            this.errors.registration = false;
            this.registered = false;
        }
  }
};
</script>