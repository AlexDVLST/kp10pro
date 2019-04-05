window.Vue = require('vue');

require('../../../functions');

import EmployeeSmtp from "../../../components/EmployeeSmtp";
import Client from "../../../components/Client";

window.utilities = new Vue({
    components: { 'employee-smtp': EmployeeSmtp, 'client': Client },
    el: '#app',
    data: {
        employeeSmtp: {
            userId: 0
        },
        client: {
            loaded: false
        },
        sendMail: {
            employeeEmails: [],
            clientEmails: [],
            clientName: '',
            clientId: 0,
            disabled: false,
            data: {
                offerId: 0,
                smtpId: 0,
                clientEmail: '',
                subject: '',
                message: ''
            }
        },
        help: {
            show: false,
            showHelp: false, //Show help on start
            videos: [],
            current: {}
        }
    },
    mounted: function () {
        this.$on('employeeSmtp:save:success', (data) => {
            //Hide modal
            this.$refs.employeeStmp.hideModal();
            //Add email
            window.config.offer.employee.user.smtp_emails = data;
            //Open modal
            this.showModalSendEmail();
        });

        this.$on('client:create:success', (data) => {
            //Hide modal
            this.$refs.client.hideModal();

            //Update data on the sidebar
            let $toolbar = $('#gjs-sm-decorations.toolbar');

            F.updateCpSettings({ client: { id: data.id, name: data.displayName } });
            //Client
            $toolbar.find('select#client').empty()
                .append(`<option value="${data.id}">${data.displayName}</option>`);

            if (data.contact_person_relation && data.contact_person_relation.length) {
                let options = data.contact_person_relation.map(function (contactPerson) {
                    return `<option value="${contactPerson.client_relation.id}">${contactPerson.client_relation.displayName}</option>`
                });
                //Contact persons
                $toolbar.find('select#contact-person').empty()
                    .append(options);

                //Get first
                if (data.contact_person_relation[0].client_relation) {
                    F.updateCpSettings({ contactPerson: { id: data.contact_person_relation[0].client_relation.id, name: data.contact_person_relation[0].client_relation.displayName } });
                    //Set first selected
                    $toolbar.find('select#contact-person').val(data.contact_person_relation[0].client_relation.id);
                }
            }
        });

        //EmployeeStmp component //if offer has employee
        if (config.offer.employee && config.offer.employee.user) {
            this.employeeSmtp.userId = config.offer.employee.user.id;
        }
        //Fix for summernote
        $("#modal-send-email").on("hidden.bs.modal", function () {
            $('#compose-textarea').summernote('destroy');
        });
        //Fix for client
        $('#modal-add-client').on('shown.bs.modal', () => {
            this.client.loaded = true;
        });

        //Load help videos
        window.axios.get('/help/video/json')
            .then((response) => {
                this.help.videos = response.data;
                this.help.current = this.help.videos[0];
            })
            .catch(error => {
                window.message({ text: error.response.data.errors, error: true });
            });
        //Get usermeta help view settings
        window.axios.get('/user/meta/show-editor-help')
            .then((response) => {
                //If meta data exist
                if (response.data.id) {
                    this.help.showHelp = 0;
                }
            })
            .catch(error => {
                window.message({ text: error.response.data.errors, error: true });
            });


    },
    methods: {
        showModalSendEmail: function () {
            let config = window.config;

            this.sendMail.data.message = window.$wrapper.find('.message__tooltip').html();
            //Offer url
            this.sendMail.data.message += `<br><br>Ознакомиться с КП вы можете по ссылке <a href="${config.webUrlOffer}" target="_blank">Перейти</a>`;
            //Name
            this.sendMail.data.subject = config.offer.offer_name;
            //EmployeeStmp component //if offer has employee
            if (config.offer.employee && config.offer.employee.user) {
                this.employeeSmtp.userId = config.offer.employee.user.id;
                //Set offer id
                this.sendMail.data.offerId = config.offer.id;

                if (config.offer.employee.user.smtp_emails && config.offer.employee.user.smtp_emails.length) {
                    this.sendMail.employeeEmails = config.offer.employee.user.smtp_emails;
                    //Add first smtp settings to data
                    this.sendMail.data.smtpId = this.sendMail.employeeEmails[0].id;
                }
                //Check if need add email
                if (!this.sendMail.employeeEmails.length) {
                    //Show modal add email
                    this.$refs.employeeStmp.showModal();
                    return;
                }

                if (config.offer.employee.user.signature_relation) {
                    this.sendMail.data.message += '<br>-----<br>' +
                        F.nlToBr(config.offer.employee.user.signature_relation.signature);
                }
            }
            //if offer has client
            if (config.offer.client_relation && config.offer.client_relation.client) {
                if (config.offer.client_relation.client.email_relation) {
                    this.sendMail.clientEmails = config.offer.client_relation.client.email_relation;
                    this.sendMail.clientId = config.offer.client_relation.client.id;
                    //Add first email to data
                    this.sendMail.data.clientEmail = this.sendMail.clientEmails[0] ? this.sendMail.clientEmails[0].email : '';
                }
                //Get name
                this.sendMail.clientName = config.offer.client_relation.client.displayName;
            } else {
                //Client not selected
                window.message(`
                    Необходимо привязать клиента к КП или создать его из панели настроек. После выбора клиента необходимо сохранить КП
                    <br><br><button type="button" class="btn btn-default" onclick="F.openToolbar();hideMessage();">Перейти</button>
                    `);
                return;
            }

            let $el = $(this.$el).find('#modal-send-email'),
                $textarea = $el.find('#compose-textarea');

            this.$nextTick(() => {
                //Fix for summernote modal
                $el.find('.note-editor .modal button.close').removeAttr('data-dismiss');
                $el.find('.note-editor .modal button.close').click(function (e) {
                    // e.preventDefault(); 
                    $(this).closest('.modal').modal('hide');
                });
                //
                $textarea.summernote({
                    toolbar: [
                        // [groupName, [list of button]]
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['height', ['height']]
                    ],
                    dialogsInBody: true,
                    lang: 'ru-RU',
                    callbacks: {
                        onChange: (contents, $editable) => {
                            this.sendMail.data.message = contents;
                        }
                    }
                });
                //Init employee select
                $el.find('select#employee-email').select2({
                    language: "ru",
                    placeholder: 'Выберите почту для отправки'
                }).on("select2:select", e => {
                    this.sendMail.data.smtpId = $(e.target).val();
                });
                //Init client select
                $el.find('select#client-email').select2({
                    language: "ru",
                    placeholder: 'Выберите почту для отправки',
                    tags: true,
                    createTag: function (params) {
                        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                        //Validate email
                        if (!re.test(params.term)) {
                            // Return null to disable tag creation
                            return null;
                        }

                        return {
                            id: params.term,
                            text: params.term
                        }
                    }
                }).on("select2:select", e => {
                    this.sendMail.data.clientEmail = $(e.target).val();
                    //Check if email doesnt added yet
                    if (this.sendMail.clientId && !this.sendMail.clientEmails.find(el => el.email === this.sendMail.data.clientEmail)) {
                        axios.post(`/client/${this.sendMail.clientId}/email`, { email: this.sendMail.data.clientEmail })
                            .then((response) => { })
                            .catch(error => {
                                window.message({ text: error.response.data.errors, error: true });
                            });
                    }
                });
                //Init checkbox
                $el.find('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_flat-blue'
                })

                $('#modal-send-email').modal('show');
            });
        },
        sendEmailSend: function () {
            //Disable
            this.sendMail.disabled = true;

            window.axios.post('/settings/integration/email/send', this.sendMail.data)
                .then((response) => {
                    //Enable
                    this.sendMail.disabled = false;
                    $('#modal-send-email').modal('hide');

                    setTimeout(() => {
                        window.message(`КП для клиента <b>${this.sendMail.clientName}</b> отправлено!`);
                        setTimeout(function () {
                            window.hideMessage();
                        }, 2000);
                    }, 800);
                })
                .catch(error => {
                    //Enable
                    this.sendMail.disabled = false;

                    window.message({ text: error.response.data.errors, error: true });
                });
        },
        showHelp: function (metaCheck) {
            if (metaCheck && this.help.showHelp !== false) {
                return;
            }
            this.help.show = true;
        },
        closeHelp: function () {
            this.help.show = false;
            if (this.help.showHelp === false) {
                window.axios.put('/user/meta/show-editor-help', { value: false })
                    .then((response) => {
                    })
                    .catch(error => {
                        window.message({ text: error.response.data.errors, error: true });
                    });
            }
        }
    }
})