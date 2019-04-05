<template>

    <div class="row">
        <div v-if="loaded==1" class="col-md-4">
            
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">{{pageName}}</h3>
                </div>
                <form  role="form" id="add-currencie-form" @submit.prevent="checkForm">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="currency" >Валюта:</label>
                            <select id="currency" class="form-control" @change="update('charCode',$event.target.value)">
                                <option v-if="charCode==''" selected="selected">Выбрать валюту</option>
								<option v-for="(currency, index) in currencies" selected="{'selected': currency.char_code == charCode}" :value="currency.char_code" :key="index">
									{{currency.description}} ( {{currency.char_code}} )
								</option>
                            </select>
                        </div>

                        <div v-if="basic !=1" class="form-group">
                            <label for="currency">Синхронизировать курс:</label>
                            <br>
                            <input ref="currencieSync"
                                   type="checkbox"
                                   @change="update('sync', $event.target.checked)"
                                   :checked="sync">
                        </div>


                        <div class="form-group" v-if="!sync">
                            <label for="currency">Курс</label>
                            <div :class="[errors.rate ? 'has-error' : '']">
                                <input ref="currencieRate" id="currencieRate" class="form-control" type="text"
                                       :value="rate"
                                       placeholder="Курс"
                                       @input="update('rate', $event.target.value)">
                            </div>
                        </div>
                    </div>
                    <div class="box-body">
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
	export default {
		props: {
			pageName: {default: "Название страницы"},
			name: {default: "Название валюты"},
			sync: {default: 1},
			rate: {default: 1},
			currencies: {default: []},
			charCode: '',
			basic: '',
			loaded: {default: 0},
		},
		data() {
			return {
				errors: {
					rate: false,
					charCode: false
				},
			};
		},
		methods: {
			update(input, value, index) {
				this.$parent.$emit("update", {
					field: input,
					value: value,
					index: index
				});
				this.removeError(input);
			},
			checkForm: function () {

                if(this.charCode == ''){
					this.errors.charCode = true;
					modal({
						'modalTitle': 'Ошибка!',
						'modalMessage': 'Укажите валюту',
						'okTitle': 'OK',
						'cancelTitle': false,
						'type': 'modal-default',
						'onOk': function ($modal) {
							$modal.modal('hide');
						},
					});
                }

				if (!this.sync) {
					if (!this.rate) {
						this.errors.rate = true;
						modal({
							'modalTitle': 'Ошибка!',
							'modalMessage': 'Введите курс валюты!',
							'okTitle': 'OK',
							'cancelTitle': false,
							'type': 'modal-default',
							'onOk': function ($modal) {
								$modal.modal('hide');
							},
						});
					}
					else {
						if (this.rate.toString().length > 8) {
							this.errors.rate = true;
							modal({
								'modalTitle': 'Ошибка!',
								'modalMessage': 'Слишком большое значение',
								'okTitle': 'OK',
								'cancelTitle': false,
								'type': 'modal-default',
								'onOk': function ($modal) {
									$modal.modal('hide');
								},
							});
						}
					}
				}


				if (this.errors.rate !== true && this.errors.charCode !== true) {
					this.$parent.$emit("saveCurrency");
				}
			},
			removeError: function (error) {
				if (this.errors[error]) {
					this.errors[error] = false;
				}
			},
		}
	}
</script>



