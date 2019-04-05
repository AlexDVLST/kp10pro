import client from '../components/Client'

new window.Vue({
    el: '#app',
    components: { client },
    mounted: function () {
         //Grab the path from your URL. In your case /writers/1/books/
        let path = window.location.pathname;
        //Break the path into segments
        let segments = path.split("/");
        //Return the segment that has the ID
        this.clientId = segments[2];
    },
    data: {
        clientId: 0
    }
});
