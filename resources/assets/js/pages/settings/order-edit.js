new window.Vue({
    el: '#app',
    data: {
        loaded: false,
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
                this.order = data;
                this.order.edit = 1; //marker for edit order

                let invoice = data.invoices.filter((invoice) => {
                    return invoice.id === data.invoice_id;
                });

                if (invoice.length) {
                    this.dayPrice = Math.round(this.order.tariff.price / data.daysInPeriod);
                    this.order.tariff.price = this.dayPrice * data.daysToEnd;
                    this.order.invoice = invoice[0];

                    if (!this.order.invoice.payment_info.months) {
                        this.order.invoice.payment_info.months = 1;
                    }

                    this.order.months = this.order.invoice.payment_info.months;
                    this.order.licenses = this.order.invoice.payment_info.licenses;
                }
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        updateOrder: function () {
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
        }
    }
});