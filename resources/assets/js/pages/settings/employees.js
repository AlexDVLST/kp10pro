// TODO: переписати на Vue(ПАГІНАЦІЯ НЕ ПОТРІБНА, ПОКИЩО)
// $(document).on('click', 'a.delete', function () {
//     let $this = $(this),
//         id = $this.data('id'),
//         name = $this.data('name');
//     if (id) {
//         window.modal({
//             'modalTitle': 'Подтверждение действия',
//             'modalMessage': 'Вы уверены что хотите удалить сотрудника <strong>' + name + '</strong>?',
//             'onOk': function ($modal) {
                
//                 $modal.modal('hide');
//                 //Show ajax request
//                 window.Pace.restart();

//                 window.axios.delete('/settings/employee/' + id)
//                     .then(() => {
//                         location.reload();
//                     })
//                     .catch((error) => {
//                         window.ajaxError(error);
//                     })
//             },
//         });

//     }
// });

new Vue({
    el: '#app',
    data: {
        employeeFromCrm: [],
        employeeToKP: [],
        isDisabled: false,
        type: '',
        isCheck: [],
        btnName: 'Отобразить добавленных',
        btnStatus: true,
        kp10UserList: [],
        tempEmployeeList: [],
        addedId: [],
        employeeList: [],
        clientsCnt: 0,
        offersCnt: 0,
        employeeName: '',
        noAssigned: true,
        employeeForDelete: 0,
        employeeForReplacement: 0

        // showExist: false
    },
    mounted: function () {
        //css
        let select2Css = document.createElement("link");
        select2Css.setAttribute("rel", "stylesheet");
        select2Css.setAttribute(
        "href",
        "/plugins/select2/dist/css/select2.min.css"
        );
        document.head.insertBefore(select2Css, document.head.firstChild);

        //Select2
        let select2 = document.createElement("script");
        select2.setAttribute("src", "/plugins/select2/dist/js/select2.full.min.js");
        document.head.appendChild(select2);

    },
    methods: {
        deleteEmployee: function (e) {
            var id = $(e.target).data('id'),
                name = $(e.target).data('name');

            this.isDisabled = true;
            this.noAssigned = true;

            if (id) {
                window.axios.get('/settings/employee/getClientsOffersList/' + id )
                .then((response) => {
                    
                    var data         = response.data,
                    clientsCnt   = data.clients.length,
                    offersCnt    = data.offers,
                    employeeList = data.userList;
                    
                    this.employeeList      = employeeList;
                    this.clientsCnt        = clientsCnt;
                    this.offersCnt         = offersCnt;
                    this.employeeName      = name;
                    this.employeeForDelete = id;
                    
                    if (clientsCnt == 0 && offersCnt == 0) {
                        this.noAssigned = false;
                        this.isDisabled = false;
                    }
                    
                    $("#popup-delete-employee").modal('show');
              
                    if (this.offersCnt != 0 && this.clientsCnt != 0) {
                        this.$nextTick(function() {                    
                            $("#employee-to-replace").select2({
                                language: "ru",
                                placeholder: "Выберите сотрудника",
                                width: '100%' 
                            }) 
                            .on("change", e => {
                                if ($(e.target).val() != -1) {
                                    this.isDisabled = false;
                                    this.employeeForReplacement = $(e.target).val();
                                } else {
                                    this.isDisabled = true;
                                    this.employeeForReplacement = 0;
                                }
                            });
                        });
                    }

                    // window.modal({
                    //     'modalTitle': 'Подтверждение действия',
                    //     'modalMessage': 'Вы уверены что хотите удалить сотрудника <strong>' + name + '</strong>?',
                    //     'onOk': function ($modal) {
                            
                    //         $modal.modal('hide');
                    //         //Show ajax request
                    //         window.Pace.restart();

                    //         window.axios.delete('/settings/employee/' + id)
                    //             .then(() => {
                    //                 location.reload();
                    //             })
                    //             .catch((error) => {
                    //                 window.ajaxError(error);
                    //             })
                    //     },
                    // });
                })
                .catch((error) => {
                    window.ajaxError(error);
                });  
            }
        },
        confirmDeletion: function (e) {
            // this.employeeForDelete

            window.axios.delete('/settings/employee/' + this.employeeForDelete + '/' + this.employeeForReplacement)
            .then(() => {
                location.reload();
            })
            .catch((error) => {
                window.ajaxError(error);
            })

        },
        importUser: function (e) {
            var type = $(e.target).data('type');

            //load KP10 user list
            window.axios.get('/settings/employee/json')
            .then((response) => {
                let data = response.data;
                this.kp10UserList = data;
            })
            .catch((error) => {
                window.ajaxError(error);
            });  

            if (type == 1) { // Megaplan
                this.type = 'megaplan';
                window.axios.get('/settings/integration/crm/megaplan/employees')
                .then((response) => {
                    // window.ajaxSuccess(response);
                    var data = response.data;

                    this.employeeFromCrm = [];
                    data.forEach((v) => {
                        let email = '',
                        phone = '';
                        if(v.contactInfo) {
                            v.contactInfo.forEach((item) => {
                                if (item.type == 'email') {email = item.value;}
                                if (item.type == 'phone') {phone = item.value;}
                            });
                        }
                        
                        // this.employeeFromCrm.push({
                            this.tempEmployeeList.push({
                            id: v.id, 
                            firstName: v.firstName, 
                            lastName: v.lastName, 
                            middleName: v.middleName, 
                            email: email,
                            phone: phone,
                            position: v.position
                        });
                    });
                    
                    //********************************************** */
                    // do not show already added users
                    var arrId = [];
                    this.kp10UserList.forEach((index) => {
                        if (index.megaplan) {
                            arrId.push(index.megaplan.megaplan_user_id);
                        }
                    });
                    this.employeeFromCrm = this.tempEmployeeList.filter(function(item) {
                        if ( arrId.indexOf(parseInt(item.id)) === -1 ) {
                            return item;
                        }
                    });

                    //********************************************** */

                    this.$nextTick(function() {
                        $('input[type="checkbox"]').iCheck({
                            checkboxClass: 'icheckbox_flat-blue'
                        }).on('ifChanged', (e) => {
                            //Fix for vue event
                            $(e.target).trigger('click');
                        });
                        
                        $('.checkAll').on('click', function (e) {
                            //Find first checkbox and click on it
                            if ( !$(this).closest('div').hasClass('checked') ) {
                                $("input[type='checkbox']").not(':first').iCheck("check");
                            } else {
                                $("input[type='checkbox']").not(':first').iCheck("uncheck"); 
                            }
                        });
                    });
                    $("#popup-import-employee").modal('show');
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
            }

            if (type == 2) { // AmoCRM
                this.type = 'amocrm'; 
                window.axios.get('/settings/integration/crm/amocrm/employees')
                .then((response) => {
                    let data = response.data;

                    $(data).each((k,v) => {

                        this.employeeFromCrm.push({
                            id: v.id,
                            firstName: v.name,
                            lastName: v.last_name,
                            middleName: '',
                            phone: '',
                            position: '',
                            email: v.login
                        });
                    });
                    
                    this.$nextTick(function () {
                        
                        $('input[type="checkbox"]').iCheck({
                            checkboxClass: 'icheckbox_flat-blue'
                        }).on('ifChanged', (e) => {
                            //Fix for vue event
                            $(e.target).trigger('click');
                        });

                        $('.checkAll').on('click', function (e) {
                            //Find first checkbox and click on it
                            if ( !$(this).closest('div').hasClass('checked') ) {
                                $("input[type='checkbox']").not(':first').iCheck("check");
                            } else {
                                $("input[type='checkbox']").not(':first').iCheck("uncheck"); 
                            }
                        });

                    });
                    $("#popup-import-employee").modal('show');
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
            }

            if (type == 3) { // Bitrix24
                this.type = 'bitrix24';
                window.axios.get('/settings/integration/crm/bitrix24/employees')
                .then((response) => {
                    let data = response.data;
                    this.employeeFromCrm = [];
                    
                    $(data).each((k,v) => {

                        // this.employeeFromCrm.push({
                        this.tempEmployeeList.push({
                            id: v.ID,
                            firstName: v.NAME,
                            lastName: v.LAST_NAME,
                            middleName: v.SECOND_NAME,
                            phone: v.PERSONAL_MOBILE,
                            position: v.WORK_POSITION,
                            email: v.EMAIL
                        });
                    });
                    
                    //********************************************** */
                    // do not show already added users
                    var arrId = [];
                    this.kp10UserList.forEach((index) => {
                        if (index.megaplan) {
                            arrId.push(index.megaplan.megaplan_user_id);
                        }
                    });
                    this.employeeFromCrm = this.tempEmployeeList.filter(function(item) {
                        if ( arrId.indexOf(parseInt(item.id)) === -1 ) {
                            return item;
                        }
                    });

                    //********************************************** */

                    this.$nextTick(function () {
                        
                        $('input[type="checkbox"]').iCheck({
                            checkboxClass: 'icheckbox_flat-blue'
                        }).on('ifChanged', (e) => {
                            //Fix for vue event
                            $(e.target).trigger('click');
                        });

                        $('.checkAll').on('click', function (e) {
                            //Find first checkbox and click on it
                            if ( !$(this).closest('div').hasClass('checked') ) {
                                $("input[type='checkbox']").not(':first').iCheck("check");
                            } else {
                                $("input[type='checkbox']").not(':first').iCheck("uncheck"); 
                            }
                        });

                    });
                    $("#popup-import-employee").modal('show');
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
            }
        },

        addEmployee: function (index) {

            if (this.isCheck[index]) { // remove
                this.employeeToKP.splice(index, 1);
                this.isCheck[index] = false;
            } else { // add 
                let employee = this.employeeFromCrm[index];

                if (this.type == 'amocrm') {
                    this.employeeToKP[index] = {
                        id: employee.id,
                        name: employee.firstName,
                        last_name: employee.lastName,
                        login: employee.email
                    };
                }
                if (this.type == 'megaplan') {
                    this.employeeToKP[index] = employee;
                }
                if (this.type == 'bitrix24') {

                    this.employeeToKP[index] = {
                        NAME: employee.firstName,
                        LAST_NAME: employee.lastName,
                        EMAIL: employee.email,
                        ID: employee.id,
                        PERSONAL_MOBILE: employee.phone,
                    };
                }
                
                this.isCheck[index] = true;
            }

        },

        importBtn: function () {
            this.isDisabled = true;
            var filtered = this.employeeToKP.filter(function (el) {
                return el != null;
            });
            
            window.axios.post('/settings/employee/importFromCRM', {employee: filtered, type: this.type})
            .then((response) => {
                this.isDisabled = false;
                $("#popup-import-employee").modal('hide');
                location.reload();
            })
            .catch((error) => {
                window.ajaxError(error);
            });
        },

        showHideUser: function () {
            var arrId = [];
            this.kp10UserList.forEach((index) => {
                if (index.megaplan) {
                    arrId.push(index.megaplan.megaplan_user_id);
                }
            });

            if (this.btnStatus) { // show all employee
                this.employeeFromCrm = this.tempEmployeeList;

                this.tempEmployeeList.filter((item) => {
                    if ( arrId.indexOf(parseInt(item.id)) !== -1 ) {
                        this.addedId.push(item.id);
                        return item.id;
                    }
                });
                this.btnName = 'Скрыть добавленных';
                this.btnStatus = false;
            } else { // show only new employee

                this.employeeFromCrm = this.tempEmployeeList.filter(function(item) {
                    if ( arrId.indexOf(parseInt(item.id)) === -1 ) {
                        return item;
                    }
                });
                this.addedId = [];
                this.btnName = 'Отобразить добавленных';
                this.btnStatus = true;
            }
            this.$nextTick(function() {
                $('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_flat-blue'
                }).on('ifChanged', (e) => {
                    //Fix for vue event
                    $(e.target).trigger('click');
                });
            });
        },

        alreadyAdded: function (params) {
            if (this.addedId.indexOf(params) === -1) {
                return false;
            } else {
                return true;
            }
        },
        alreadyAddedTitle: function (params) {
            if (this.addedId.indexOf(params) === -1) {
                return 'Сотрудник из Вашей CRM';
            } else {
                return 'Этот сотрудник уже добавлен';
            }
        }
    }
});