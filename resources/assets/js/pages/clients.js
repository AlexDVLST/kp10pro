import VueRouter from 'vue-router';
import Client from '../components/Client'
window.Vue.use(VueRouter);

let router = new VueRouter({
    mode: 'history'
});

new window.Vue({
    components: { 'client': Client },
    router,
    el: '#app',
    mounted: function () {

        this.$on('client:create:success', (data) => {
            //Hide modal
            this.$refs.client.hideModal();
            this.fetch();
        });

        this.$on('client:update:success', (data) => {
            //Hide modal
            this.$refs.client.hideModal();
            this.fetch();
        });

        //Restore page after reload
        this.pagination.current_page = this.$route.query.page ? parseInt(this.$route.query.page) : 1;

        //load employees
        this.fetch();
    },
    data: {
        loaded: true,
        clients: [],
        employee: [],
        pagination: { current_page: 1, per_page: 30 },
        errorEmpty: false,
        showByIndex: 0, //For functional panel
        clientId: 0
    },
    methods: {
        fetch: function () {
            //Update route
            this.$router.replace({ path: "/client/", query: { page: this.pagination.current_page } });

            window.axios.get('/settings/employee/json')
                .then((response) => {
                    let data = response.data;

                    this.employee = data;
                })
                .catch(error => {
                    window.ajaxError(error);
                });

            window.axios.get('/client/json', { params: { page: this.pagination.current_page, paginate: this.pagination.per_page } })
                .then((response) => {
                    let data = response.data;

                    this.loaded = true;
                    this.clients = data.data;
                    this.pagination.current_page = data.current_page;
                    this.pagination.last_page = data.last_page;
                    this.pagination.per_page = data.per_page;

                    this.errorEmpty = this.clients.length > 0 ? false : true;

                    this.$nextTick(function () {
                        //Tooltop
                        $('[data-toggle="tooltip"]').tooltip();
                    });
                })
                .catch(error => {
                    window.ajaxError(error);
                });

        },
        remove: function (id) {
            if (id) {
                let name = '',
                    index = -1; //using for remove

                this.clients.forEach(function (item, i) {
                    if (item.id === id) {
                        name = item.name;
                        index = i;
                    }
                });
                if (index != -1) {
                    window.modal({
                        'modalTitle': 'Подтверждение действия',
                        'modalMessage': 'Вы уверены что хотите удалить <strong>' + name + '</strong>?',
                        'onOk': ($modal) => {

                            $modal.modal('hide');
                            //Show ajax request
                            window.Pace.restart();

                            window.axios.delete('/client/' + id)
                                .then(() => {
                                    // location.reload();
                                    //Remove client
                                    this.clients.splice(index, 1);
                                })
                                .catch((error) => {
                                    window.ajaxError(error);
                                })
                        },
                    });
                } else {

                }
            }
        },
        responsibleName: function (id) {
            let name = '';

            let employee = this.employee.find((e) => {
                return e.id == id;
            });
            if (employee) {
                name += employee.surname ? ' ' + employee.surname : '';
                name += employee.name ? ' ' + employee.name : '';
                name += employee.middle_name ? ' ' + employee.middle_name : '';
            }
            return name;
        }
    }

});