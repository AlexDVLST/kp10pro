// Registration from Tilda
window.axios = require('axios');
window.Vue = require('vue');

import Tilda from "./Tilda.vue";

new Vue({
    el: '#registration',
    components: {'tilda': Tilda}
});