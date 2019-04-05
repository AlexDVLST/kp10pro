new Vue({
    el: '#app',
    data: {
        helps: [],
        sections: [],
        showByIndex: null,
        help: {
            id: 0,
            name: '',
            sectionId: -1,
            video: '',
            externalLink: ''
        },
        error: {
            name: false,
            section: false,
            video: false,
        },
        modalAjax: false
    },
    mounted() {
        window.axios.get('help/json')
            .then(response => {
                this.helps = response.data;
            })
            .catch((error) => {
                window.ajaxError(error);
            });

        window.axios.get('help/section/json')
            .then(response => {
                this.sections = response.data;
            })
            .catch((error) => {
                window.ajaxError(error);
            });

        $(this.$refs.modalHelp)
            .on("hidden.bs.modal", () => {
                this.clearHelp();
            });
    },
    watch: {
        help: {
            handler: function (data) {
                //Reset error
                this.error.name = false;
                this.error.section = false;
                this.error.video = false;
            },
            deep: true
        }
    },
    methods: {
        showCreateModal: function () {
            $(this.$refs.modalHelp).modal('show');
        },
        hideCreateModal: function () {
            $(this.$refs.modalHelp).modal('hide');
        },
        saveHelp: function () {
            if (!this.help.name) {
                this.error.name = true;
            }
            if (!this.help.sectionId || this.help.sectionId < 1) {
                this.error.section = true;
            }
            if (!this.help.video || this.help.video.indexOf('https://www.youtube.com/embed/') === -1) {
                this.error.video = true;
            }

            if (!this.error.name && !this.error.sectionId && !this.error.video) {

                this.modalAjax = true;

                //Update
                if (this.help.id) {
                    //Create new help
                    window.axios.put('help/' + this.help.id, this.help)
                        .then(response => {
                            this.modalAjax = false;
                            let index = -1;
                            this.helps.forEach((help, i) => {
                                if (help.id == this.help.id) {
                                    index = i;
                                }
                            });
                            if (index != -1) {
                                //Replace with new
                                this.helps.splice(index, 1, response.data);
                            }
                            //Hide modal
                            this.hideCreateModal();
                        })
                        .catch((error) => {
                            this.modalAjax = false;
                            window.ajaxError(error);
                        });
                } else { //Create
                    //Create new help
                    window.axios.post('help', this.help)
                        .then(response => {
                            this.modalAjax = false;
                            //Add new line
                            this.helps.push(response.data);
                            //Hide modal
                            this.hideCreateModal();
                        })
                        .catch((error) => {
                            this.modalAjax = false;
                            window.ajaxError(error);
                        });
                }
            }
        },
        editHelp: function (help) {
            this.help = Object.assign({}, help);
            if (help.section && help.section.id) {
                this.help.sectionId = help.section.id;
            }
            this.showCreateModal();
        },
        removeHelp: function () {
            if (this.help.id) {
                this.modalAjax = true;
                //Create new help
                window.axios.delete('help/' + this.help.id)
                    .then(response => {
                        this.modalAjax = false;
                        //Remove item
                        this.helps.splice(this.help.index, 1);
                        //Hide modal
                        this.hideConfirmDeleteModal();
                    })
                    .catch((error) => {
                        this.modalAjax = false;
                        window.ajaxError(error);
                    });
            }
        },
        showConfirmDeleteModal: function (help, index) {
            $(this.$refs.modalConfirmDelete).modal('show');
            this.help = Object.assign({}, help);
            this.help.index = index;
        },
        hideConfirmDeleteModal: function () {
            $(this.$refs.modalConfirmDelete).modal('hide');
            this.clearHelp();
        },
        clearHelp: function(){
            this.help.id = 0;
            this.help.name = '';
            this.help.sectionId = -1;
            this.help.video = '';
            this.help.externalLink = '';
        }
    }
})