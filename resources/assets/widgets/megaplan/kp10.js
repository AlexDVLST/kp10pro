window.Vue = require('vue');
window.axios = require('axios'); 
require('../../js/bootstrap');
window._ = require('lodash');
require('../../../../public/plugins/select2/dist/js/i18n/ru.js');
import widget from './Widget'
window.kp10Widget = new Vue({
    el: '#kp10-widget-megaplan',
    components: { widget },
    data: {
        megaplan: 'Прювееет!!!',
        widget: {}
    },
    methods: {

    }
});