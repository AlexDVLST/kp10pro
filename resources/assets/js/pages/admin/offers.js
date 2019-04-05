new Vue({
    el: '#app',
    data: {
        //Uploading status
        offers: {},
        uploadingId: 0
    },
    mounted() {
        window.axios.get('offers/json')
            .then(response => {
                this.offers = response.data;
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        upload: function (id) {

            this.uploadingId = id;
            
            //Start upload
            window.axios.put('offers/' + id + '/upload')
                .then(response => {
                    //Upload done
                    this.uploadingId = 0;
                    window.ajaxSuccess(response);
                })
                .catch((error) => {
                    //Upload done
                    this.uploadingId = 0;

                    window.ajaxError(error);
                });
        }
    }
})