import currency from '../../components/Currency'
new window.Vue({
	el: '#app',
	components: { currency },
	mounted: function () {
		//Update data
		this.$on('update', this.updateData);
		this.$on('saveCurrency', this.saveCurrency);
		window.axios.get('/settings/currencies/basic/json')
			.then((response) => {
				this.currency.currencies = response.data;

				window.axios.get('json')
					.then((response) => {
						this.currency.id = response.data.id;
						this.currency.name = response.data.name;
						this.currency.code = response.data.code;
						this.currency.sync = response.data.sync;
						this.currency.basic = response.data.basic;
						this.currency.rate = response.data.rate;
						this.currency.charCode = this.currency.currencies[this.currency.code].char_code;
						this.currency.loaded = 1
					})
					.catch((error) => {
						window.ajaxError(error);
					});
			})
			.catch((error) => {
				window.ajaxError(error);
			});
	},
	data: {
		pageName: "Редактирование валюты",
		currency: {
			id: 0,
			name: '',
			code:'',
			sync:'',
			basic:'',
			rate:'',
			currencies: [],
			charCode:'',
			loaded: 0
		},
	},
	methods: {
		updateData: function (data) {
			//console.log(data);
			if (data.field) {
				if (typeof data.index === 'undefined') {
					this.currency[data.field] = data.value;
				} else {
					if (typeof data.value === 'object') {
						$.each(this.currency[data.field], (key, item) => {
							if (key === data.index) {
								$.each(data.value, function (dKey, dValue) {
									//Update only defined key
									item[dKey] = dValue;
								});
							}
						})
					} else {
						this.currency[data.field].splice(data.index, 1, data.value);
					}
				}
			}
		},

		saveCurrency: function(){
			let currencie = {
				'currencieName': this.currency.name,
				'currencieCharCode': this.currency.charCode,
				'currencieRate': this.currency.rate,
				'currencieSync': this.currency.sync,
			};

			//console.log(currencie);

			axios.put('/settings/currencies/' + this.currency.id, {
				currencie: currencie
			}).then(function (response) {
				location.href = '/settings/currencies';
			});
		}
	},
});
