new window.Vue({
    el: '#app',
    data: {
        loaded: false,
        dayPrice: 11,
        accountBalance: 0,
        order: {
            months: 1,
            licenses: 1,
            invoice: {
                reference: '',
                total: ''
            }
        }
    },
    mounted: function () {
        //Get employee card for initialize boject
        window.axios.get('/settings/order/json')
            .then((response) => {
                //View marker
                this.loaded = true;

                let data = response.data;
                this.order.tariff = data.tariff;

                let invoice = data.invoices.filter((invoice) => {
                    return invoice.id === data.invoice_id;
                });

                if (invoice.length) {
                    this.dayPrice = Math.round(invoice[0].total / data.daysInPeriod);
                    this.order.daysInPeriod = data.daysInPeriod;
                    this.accountBalance = this.dayPrice * data.daysToEnd * data.licenses;
                    this.order.invoice = invoice[0];
                }

            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        createOrder: function () {
            //TODO-N:need disable btn
            window.axios.put('/settings/order', this.order)
                .then((response) => {
                    let data = response.data;

                    this.order.invoice.total = data.total + '.00';
                    this.order.invoice.reference = data.reference;

                    setTimeout(function () {
                        $('#moneta-pay').submit();
                    }, 100);

                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        },
        calculatePrice: function () {
            return (this.order.tariff.price * this.order.months * this.order.licenses) - this.accountBalance;
        }
    },
});