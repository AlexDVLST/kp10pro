/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

let token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

window.Vue = require('vue');

//
new window.Vue({
    el: '#registration',
    data: {
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
    },
    methods: {
        onSubmit: function () {
            //Reset errors
            this.clearErrors();

            if (!this.name) {
                this.errors.name = true;
                this.errors.detail.name = 'Поле обязательно для заполнения';
            }
            if (!this.email) {
                this.errors.email = true;
                this.errors.detail.email = 'Поле обязательно для заполнения';
            }
            if (!this.validEmail(this.email)) {
                this.errors.email = true;
                this.errors.detail.email = 'Укажите корректный адрес электронной почты';
            }
            if (!this.phone || !this.phone.replace(/[^0-9]/,'')) {
                this.errors.phone = true;
                this.errors.detail.phone = 'Поле обязательно для заполнения';
            }
            //Укажите корректный адрес электронной почты
            if (this.name && this.validEmail(this.email) && this.phone) {
                //Show ajax
                this.request = true;

                axios.post('/register', { name: this.name, email: this.email, phone: this.phone, referer: window.referer })
                    .then(response => {
                        //Send stat
                        try{
                        yaCounter50570539.reachGoal('registr');
                        }catch(e){}
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
            }

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
});