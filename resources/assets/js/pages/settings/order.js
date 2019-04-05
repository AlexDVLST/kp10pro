new window.Vue({
    el: '#app',
    data: {
        loaded: false,
        order: [],
        unpaidInvoices: []
    },
    mounted: function () {
        //Get employee card for initialize boject
        window.axios.get('/settings/order/json')
            .then((response) => {
                //View marker
                this.loaded = true;

                this.order = response.data;
                if (this.order.invoices) {
                    this.order.invoices.forEach(invoice => {
                        if (invoice.isReady || invoice.isWaiting) {
                            this.unpaidInvoices.push(invoice);
                        }
                    });
                }
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        getColorStatus: function (invoice) {
            if (invoice.isReady) {
                return 'bg-aqua';
            }
            if (invoice.isWaiting) {
                return 'bg-orange';
            }
            if (invoice.isPaid) {
                return 'bg-green';
            }
            if (invoice.isCancelled) {
                return 'bg-red';
            }
        },
        cancelInvoice: function (invoice, index) {

            window.modal({
                'modalTitle': 'Подтверждение действия',
                'modalMessage': 'Вы уверены что хотите отменить счет?',
                'onOk': ($modal) => {

                    $modal.modal('hide');
                    //Show ajax request
                    window.Pace.restart();

                    window.axios.post('/settings/invoice/' + invoice.id + '/cancel')
                        .then(() => {
                            this.unpaidInvoices.splice(index, 1);
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        })
                },
            });
        }
    }
});