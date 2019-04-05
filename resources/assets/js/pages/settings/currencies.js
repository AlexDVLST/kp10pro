import currencies from '../../components/Currencies'
new window.Vue({
    el: '#app',
    data: {
	    currencyData: {
		    currencies:{}
	    },

    },
	components: { currencies },
	created: function () {
		this.start();
	},

	mounted: function () {
		this.$on('changeSync', this.changeSync);
		this.$on('setBasicCurrencie', this.setBasicCurrencie);
		this.$on('deleteCurrencie', this.deleteCurrencie);
	},

    methods: {

	    start(){
	        this.fetchProducts();
        },

	    fetchProducts(){
		    axios.get('/settings/currencies/list/json')
			    .then(response => {
				    this.currencyData.currencies = response.data;
			    })
			    .catch(error => {
				    // console.log(error.response.data);
			    });
        },

        deleteCurrencie(value) {
	        let _this = this;
            modal({
                'modalTitle': 'Удаление валюты',
                'modalMessage': 'Вы действительно хотите удалить валюту?',
                'okTitle': 'Да',
                'cancelTitle': 'Отмена',
                'type': 'modal-default',
                'onOk': function () {
                    axios.delete(location.href + '/' + value.id).then(function (response) {
                        //location.reload();
	                    $('#popup-window-confirm').modal('hide');
	                    _this.fetchProducts();
                    });
                },
                'onCancel': function () {
                }
            })
        },

        setBasicCurrencie(element) {
	        let _this = this;
            modal({
                'modalTitle': 'Выбор базовой валюты',
                'modalMessage': 'Вы действительно хотите установить эту валюту <br> валютой по умолчанию?',
                'okTitle': 'Да',
                'cancelTitle': 'Отмена',
                'type': 'modal-default',
                'onOk': function () {
                    axios.put('/settings/currencies/' + element.id, {
                        action: "change_basic",
                    }).then(function (response) {
                        //location.reload();
	                    $('#popup-window-confirm').modal('hide');
	                    _this.fetchProducts();
                    });
                },
                'onCancel': function () {
                }
            })
        },

        changeSync(element) {
	    	let _this = this;
	    	axios.put('/settings/currencies/' + element.id, {
                action: "change_sync",
            }).then(function (response) {
                //location.reload();
			    $('#popup-window-confirm').modal('hide');
			    _this.fetchProducts();
            });
        }
    },
});