new Vue({
    el: '#app',
    data: {
        id: 0,
        smtp: {
            data: {
                login: '',
                password: '',
                server: '',
                port: '',
                secure: false,
            },
            errors: {
                login: false,
                password: false,
                server: false,
                port: false,
            }
        }
    },
    mounted: function () {
        //Get integration
        window.axios.get('/settings/integration/email/json')
            .then((response) => {
                let data = response.data;

                if (data) {
                    console.log(data);
                } else {
                    this.smtp.data.login = window.laravel.user.email;
                }

                $('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_flat-blue'
                }).on('ifChanged', (e) => {
                    //Fix for vue event
                    this.smtp.data.secure = !this.smtp.data.secure;
                });
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        save: function () {

            this.smtp.errors.login = !this.smtp.data.login;
            this.smtp.errors.password = !this.smtp.data.password;
            this.smtp.errors.server = !this.smtp.data.server;
            this.smtp.errors.port = !this.smtp.data.port;

            if (this.smtp.data.login && this.smtp.data.password && this.smtp.data.server && this.smtp.data.port) {
                if (this.id) {
                    window.axios.put('/settings/integration/email', this.smtp.data)
                        .then((response) => {
                            
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                } else {
                    //Create
                    window.axios.post('/settings/integration/email', this.smtp.data)
                        .then((response) => {
                            this.id = response.data.id;
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                }
            }
        }
    }
});