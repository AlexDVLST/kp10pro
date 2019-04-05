import client from '../components/Client'

new window.Vue({
    el: '#app',
    components: { client },
    mounted: function () {
        this.$on('client:save:success', (data) => {
            //Redirect to client page
            location.href = "/client/";
        });
        this.loaded = true;
    },
    data: {
        loaded: false
    }
});
