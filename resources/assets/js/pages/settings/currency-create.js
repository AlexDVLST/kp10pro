import currency from '../../components/Currency'
new window.Vue({
	el: '#app',
	components: {currency},
	mounted: function () {
		// BasicCurrencyData
		this.$on('update', this.updateData);
		this.$on('saveCurrency', this.saveCurrency);
		window.axios.get('basic/json')
			.then((response) => {
				this.currency.currencies = response.data;
			})
			.catch((error) => {
				window.ajaxError(error);
			});
	},
	data: {
		pageName: "Создание валюты",
		currency: {
			id: 0,
			name: '',
			code: '',
			sync: '',
			basic: '',
			rate: '',
			currencies: [],
			charCode: '',
			loaded: 1
		}
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

		saveCurrency: function () {
			let currencie = {
				'currencieName': this.currency.name,
				'currencieCharCode': this.currency.charCode, //
				'currencieRate': this.currency.rate,
				'currencieSync': this.currency.sync,
			};

			//console.log(currencie);

			axios.post('/settings/currencies', {currencie: currencie}).then(function (response) {
				location.href = '/settings/currencies';
			});
		}
	},
});
