new Vue({
    el: '#app',
    data: {
        text: {
            addBtn: 'Добавить',
            hideBtn: 'Скрыть'
        },
        megaplan: false,
        megaplanApplication: 5972796, // код додатку в мегаплан
        amocrm: false,
        bitrix24: false,
        errors: {
            host: false,
            login: false,
            token: false
        },
        integrationData: {
            id: 0,
            host: '',
            uuid: '',
            token: '',
            crm: '',
            login: '',
            accessToken: ''
        },
        showSaveBtn: true,
        disabledSelect: false,
        disabledProgram: false,
        disabledField: false,
        disabledFieldType: false,
        disabledAddBtn: true,
        disabledHost: false,
        selectProgramValue: '',
        program: [],
        fields: [],
        fieldType: [],
        showFieldDisable: false,
        storedFields: [],
        storedParams: {
            program: '',
            field: '',
            type: '',
            contentType: ''
        },
        storedParamsValue: {
            program: '',
            field: '',
            type: ''
        },
        addFieldBtnName: '',
        responseData: '',
        selectedIndex: 0,
        showPlus: true,
        showMinus: false,
        megaplanEmployee: {
            name: ''
        }
    },
    mounted: function () {
        this.loadSettings();
    },
    methods: {
        loadSettings: function () {
            //Get integration
            window.axios.get('/settings/integration/crm/json')
                .then((response) => {
                    let data = response.data;

                    if (data.crm_type) {
                        this.showRelativePanel(data.crm_type);
                    }

                    this.integrationData.id = data.id || 0;
                    this.integrationData.uuid = data.uuid || false;
                    this.integrationData.token = data.api_token || '';
                    this.integrationData.crm = data.crm_type || '';
                    this.integrationData.login = data.login || '';
                    this.integrationData.host = data.host || '';
                    this.integrationData.accessToken = data.access_token || '';

                    // малюємо збережені поля
                    this.storedFields = data.fields;

                    //Disable select
                    this.disabledHost = this.disabledSelect = data.id != 0;

                    //Init text
                    this.addFieldBtnName = this.text.addBtn;
                })
                .catch((error) => {
                    // window.ajaxError(error);
                });
        },
        showRelativePanel: function (crm_type) {
            this.megaplan = crm_type === 'megaplan';
            this.amocrm = crm_type === 'amocrm';
            this.bitrix24 = crm_type === 'bitrix24';
        },
        selectIntegration: function (e) {
            this.host = "";
            this.integrationData.crm = e.target.value;
            this.showRelativePanel(e.target.value);

            if (this.megaplan) {
                this.showSaveBtn = false;
            }
            if (this.amocrm) {
                this.showSaveBtn = true;
            }
            if (this.bitrix24) {
                this.showSaveBtn = false;
            }
        },
        updateHost: function (e) {
            this.integrationData.host = this.prepareHost(e.target.value);
            if (!this.integrationData.host) {
                this.errors.host = true;
            } else {
                this.errors.host = false;
            }
        },
        updateLogin: function (e) {
            this.integrationData.login = e.target.value;
            if (!this.integrationData.login) {
                this.errors.login = true;
            } else {
                this.errors.login = false;
            }
        },
        updateToken: function (e) {
            this.integrationData.token = e.target.value;
            if (!this.integrationData.token) {
                this.errors.token = true;
            } else {
                this.errors.token = false;
            }
        },

        targetLink: function (e) {
            let link = '',
                params = '';

            if (!this.integrationData.host) {
                this.errors.host = true;
                return;
            } else {
                this.errors.host = false;
            }

            if (this.errors.host) { return; }

            this.integrationData.host = this.prepareHost(this.integrationData.host);

            if (this.integrationData.crm === 'megaplan') {
                link = 'https://' + this.integrationData.host + '/settings/application/install/' + this.megaplanApplication;
                this.showSaveBtn = true;
                params = {
                    crm: this.integrationData.crm
                };
            }

            if (this.integrationData.crm === 'bitrix24') {
                link = 'https://' + this.integrationData.host + '/marketplace/detail/telefum24.kp10/';

                params = {
                    crm: this.integrationData.crm,
                    host: this.integrationData.host
                };

                this.disabledHost = true;
            }

            window.axios.post('/settings/integration/set/cookies', params)
                .then((response) => {
                    //open after few seconds
                    // setTimeout(() => window.open(link), 700);
                    window.open(link, '_self');
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        },

        //Remove protocol from url
        prepareHost: function (str) {
            var host = str && str.replace(/http(s)?\:\/\//i, '');
            return host && host.split('/')[0];
        },

        saveIntegration: function (e) {

            if (this.integrationData.id) {
                //Update
                window.axios.put('/settings/integration/crm/' + this.integrationData.crm, this.integrationData)
                    .then((response) => {
                        window.ajaxSuccess(response);
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            } else {
                //Create
                window.axios.post('/settings/integration/crm', this.integrationData)
                    .then((response) => {

                        this.loadSettings();
                        // window.ajaxSuccess(response);

                        // //Update id
                        // this.integrationData.id = response.data.integration.id;
                        // //Disable select
                        // this.disabledSelect = true;
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            }
        },

        selectProgram: function (e) {
            // вимикаємо всі кнопки і селекти
            this.disabledProgram = true;
            this.disabledField = true;
            this.disabledFieldType = true;
            this.disabledAddBtn = true;
            this.storedParams.program = e.target.options[e.target.selectedIndex].innerHTML;
            this.storedParamsValue.program = e.target.value;
            //Allowed content types
            let types = ['DateField', 'StringField', 'MoneyField', 'EnumField', 'DateTimeField', 'FloatField', 'BoolField'];

            window.axios.get('/megaplan/programs/' + e.target.value + '/field')
                .then((response) => {
                    let data = response.data,
                        program = data.program,
                        //field_type = data.type,
                        fieldArr = [];
                    // fieldTypeArr = [];

                    this.responseData = data;

                    program.forEach(function (el, k) {
                        //
                        if (types.indexOf(el.contentType) !== -1) {
                            fieldArr.push({ text: el.hrName, val: el.name, contentType: el.contentType });
                        }
                    });

                    // var output = Object.entries(field_type).map(([key, value]) => ({ key, value }));

                    // output.forEach(function (key, val) {
                    //     fieldTypeArr.push({ text: key.value, val: key.key });
                    // });

                    this.fields = fieldArr;
                    // this.fieldType = fieldTypeArr;

                    // вмикаємо всі кнопки і селекти
                    this.disabledProgram = false;
                    this.disabledField = false;
                    this.disabledFieldType = false;
                    // this.disabledAddBtn = false;

                    // window.ajaxSuccess(response);
                })
                .catch((error) => {
                    // вмикаємо всі кнопки і селекти
                    this.disabledProgram = false;
                    this.disabledField = false;
                    this.disabledFieldType = false;
                    this.disabledAddBtn = false;

                    window.ajaxError(error);
                });
        },

        showField: function (e) {

            this.showFieldDisable = true;

            if (this.megaplan) {
                if (this.program.length == 0) {
                    window.axios.get('/megaplan/programs')
                        .then((response) => {
                            let data = response.data,
                                programArr = [];

                            data.forEach(function (el, k) {
                                programArr.push({ text: el.name, val: el.id });
                            });

                            if (this.storedParamsValue.program != '' && this.storedParamsValue.field != '') { // && this.storedParamsValue.type != ''
                                this.disabledAddBtn = false;
                            }

                            this.program = programArr;
                            // this.fields = programArr;
                            this.addFieldBtnName = this.text.hideBtn;
                            this.showFieldDisable = false;
                            this.showPlus = false;
                            this.showMinus = true;
                            // window.ajaxSuccess(response);
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                } else {
                    this.program = [];
                    this.fields = [];
                    this.showPlus = true;
                    this.showMinus = false;
                    this.showFieldDisable = false;
                    this.addFieldBtnName = this.text.addBtn;
                }
            }
            //AmoCrm integration
            if (this.amocrm) {
                if (!this.fields.length) {
                    window.axios.get('/amocrm/leads/fields')
                        .then((response) => {
                            if (response.data.length) {
                                this.fields = response.data.filter((field) => {
                                    //FIXME: не отображать юр лицо, мультисписок, адрес
                                    if (field.field_type != 15 && field.field_type != 5 && field.field_type != 13) {
                                        return true;
                                    }
                                });

                                this.addFieldBtnName = this.text.hideBtn;
                                this.showFieldDisable = false;
                                this.showPlus = false;
                                this.showMinus = true;
                            } else {
                                this.fields = [{ 'id': '-1', 'name': 'Список полей пуст' }];
                            }
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                } else { //When click cancel
                    this.fields = [];
                    this.showPlus = true;
                    this.showMinus = false;
                    this.showFieldDisable = false;
                    this.addFieldBtnName = this.text.addBtn;
                }
            }
            //Bitrix24 integration
            if (this.bitrix24) {
                if (!this.fields.length) {

                    window.axios.get('/bitrix24/deal/fields')
                        .then((response) => {

                            let data = response.data;

                            if (data.length) {

                                $.each(data, (key, value) => {
                                    this.fields.push({ id: value.field_id, name: value.field_name, field_type: value.field_type, enums: value.items });
                                });

                                this.addFieldBtnName = this.text.hideBtn;
                                this.showFieldDisable = false;
                                this.showPlus = false;
                                this.showMinus = true;
                            } else {
                                this.fields = [{ 'id': '-1', 'name': 'Список полей пуст' }];
                            }
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                } else { //When click cancel
                    this.fields = [];
                    this.showPlus = true;
                    this.showMinus = false;
                    this.showFieldDisable = false;
                    this.addFieldBtnName = this.text.addBtn;
                }
            }
        },

        selectField: function (e) {

            // Megaplan
            if (this.megaplan) {
                this.storedParams.field = e.target.options[e.target.selectedIndex].innerHTML;
                this.storedParamsValue.field = e.target.value;
                this.storedParams.contentType = e.target.options[e.target.selectedIndex].dataset.contenttype;
                this.selectedIndex = e.target.selectedIndex;
                if (this.storedParamsValue.program != '' && this.storedParamsValue.field != '') { // && this.storedParamsValue.type != ''
                    this.disabledAddBtn = false;
                }
            }

            //AmoCrm
            if (this.amocrm) {
                //Add field id
                this.storedParamsValue.field = e.target.value;
                //Enable add field button
                this.disabledAddBtn = false;
            }

            //Bitrix24
            if (this.bitrix24) {
                //Add field id
                this.storedParamsValue.field = e.target.value;
                //Enable add field button
                this.disabledAddBtn = false;
            }
        },

        // selectType: function (e) {
        //     this.storedParams.type = e.target.options[e.target.selectedIndex].innerHTML;
        //     this.storedParamsValue.type = e.target.value;


        //     if (this.storedParamsValue.program != '' && this.storedParamsValue.field != '' && this.storedParamsValue.type != '') {
        //         this.disabledAddBtn = false;
        //     }
        // },

        addRow: function (e) {
            // Megaplan
            if (this.megaplan) {

                var type = this.responseData.program[this.selectedIndex - 1].type,
                    refContentType = this.responseData.program[this.selectedIndex - 1].refContentType,
                    enumValues = this.responseData.program[this.selectedIndex - 1].enumValues;

                // this.rawHtml = this.templateRaw;
                if (this.storedParamsValue.program != ('' || -1) && this.storedParamsValue.field != ('' || -1) && this.storedParamsValue.type != ('' || -1)) {

                    window.axios.post('/settings/integration/crm/megaplan/program',
                        {
                            program_name: this.storedParams.program,
                            program_id: this.storedParamsValue.program,
                            field_name: this.storedParams.field,
                            field_id: this.storedParamsValue.field,
                            type: type,
                            refContentType: refContentType,
                            enumValues: enumValues,
                            // type_name: this.storedParams.type,
                            // type_id: this.storedParamsValue.type,
                            content_type: this.storedParams.contentType
                        })
                        .then((response) => {
                            // window.ajaxSuccess(response);

                            this.storedFields.push({
                                program_name: this.storedParams.program,
                                field_name: this.storedParams.field,
                                type_name: this.storedParams.type
                            });
                        })
                        .catch((error) => {
                            window.ajaxError(error);
                        });
                }
            }
            //AmoCrm
            if (this.amocrm) {
                if (this.storedParamsValue.field) {
                    //Check if field added
                    let storedField = this.storedFields.filter((field) => {
                        return field.field_id == this.storedParamsValue.field
                    });
                    if (storedField.length) {
                        //Block add same field
                        return;
                    }

                    //Get field data from main object
                    let field = this.fields.filter((field) => {
                        return field.id == this.storedParamsValue.field
                    });

                    if (field.length) {
                        field = field[0];

                        window.axios.post('/settings/integration/crm/amocrm/lead/field', field)
                            .then((response) => {
                                this.storedFields.push({
                                    amocrm_field_id: field.id,
                                    amocrm_field_name: field.name
                                });
                            })
                            .catch((error) => {
                                window.ajaxError(error);
                            });

                    }
                }
            }
            //Bitrix24
            if (this.bitrix24) {
                if (this.storedParamsValue.field) {

                    if(this.storedFields){
                        //Check if field added
                        let storedField = this.storedFields.filter((field) => {
                            return field.bitrix24_field_id == this.storedParamsValue.field
                        });

                        if (storedField.length) {
                            //Block add same field
                            return;
                        }
                    }

                    //Get field data from main object
                    let field = this.fields.filter((field) => {
                        return field.id == this.storedParamsValue.field
                    });

                    if (field.length) {
                        field = field[0];

                        window.axios.post('/settings/integration/crm/bitrix24/deal/field', field)
                            .then((response) => {
                                this.storedFields.push({
                                    bitrix24_field_id: field.id,
                                    bitrix24_field_name: field.name
                                });
                            })
                            .catch((error) => {
                                window.ajaxError(error);
                            });

                    }
                }
            }
        },

        // loadUser: function (e) {
        
        //     window.axios.get('/settings/integration/megaplan/employees')
        //     .then((response) => {
        //         // window.ajaxSuccess(response);
        //         let data = response.data;
        //         console.log('TEST response', response.data);
        //         data.forEach((k,v) => {
        //             this.megaplanEmployee.name = k.name;
        //             console.log('TEST v',v); 
        //             console.log('TEST k',k); 
        //         });

        //     })
        //     .catch((error) => {
        //         window.ajaxError(error);
        //     });

        // },

        deleteRow: function (index, field) {

            if (this.megaplan) {
                //TODO: ТУТ ТИ ВИДАЛЯЄШ ПОЛЕ А РОУТ ВЕДЕ НА СХЕМУ
                window.axios.delete('/settings/integration/crm/megaplan/' + field.field_id + '/program')
                    .then((response) => {
                        this.storedFields.splice(index, 1);
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            }
            if (this.amocrm) {
                window.axios.delete('/settings/integration/crm/amocrm/lead/field/' + field.amocrm_field_id)
                    .then((response) => {
                        this.storedFields.splice(index, 1);
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            }
            if (this.bitrix24) {
                window.axios.delete('/settings/integration/crm/bitrix24/deal/field/' + field.bitrix24_field_id)
                    .then((response) => {
                        this.storedFields.splice(index, 1);
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            }
        },

        deleteIntegration: function (e) {
            //TODO: ДОДАТИ ПІДТВЕРДЖЕННЯ ПРИ ВИДАЛЕННІ
            window.axios.delete('/settings/integration/crm/' + this.integrationData.crm)
                .then((response) => {
                    window.ajaxSuccess(response);
                    //Reset values
                    this.integrationData.id = 0;
                    this.integrationData.uuid = '';
                    this.integrationData.token = '';
                    this.integrationData.crm = '';
                    this.integrationData.login = '';
                    this.integrationData.host = '';
                    this.integrationData.accessToken = '';
                    this.disabledSelect = false;
                    this.disabledHost = false;
                    this.showRelativePanel('');
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        }
    }

});