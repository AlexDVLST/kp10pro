/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

/**
 * Vue
 */
window.Vue = require('vue');
window.Vue.component('pagination', require('./components/PaginationComponent.vue'));
// window.Vue.component('modal', require('./components/Modal.vue'));

require('./functions');

//TODO: Переписати з використанням socket.io
//Перевірка авторизації
location.href.indexOf('login') == -1 && location.href.indexOf('integration') == -1 && location.href.indexOf('register') == -1 && setInterval(() => {
    axios.get('/auth/check').then((response) => { if(!response.data.auth){ location.reload() } }).catch((error) => { console.log(error)});
}, 5000);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// Vue.component('example', require('./components/Example.vue'));
//
// const app = new Vue({
//     el: '#app'
// });
