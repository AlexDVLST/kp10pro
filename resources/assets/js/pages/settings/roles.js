new window.Vue({
    el: '#app',
    data: {
        roles: [],
        employees: [],
        update: []
    },
    mounted: function () {
        //Get employee card for initialize boject
        window.axios.get('/settings/employee/json')
            .then((response) => {
                this.employees = response.data;

                //Get roles 
                window.axios.get('/settings/role/json')
                    .then((response) => {
                        this.roles = response.data;
                        this.init();
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                    });
            })
            .catch((error) => {
                window.ajaxError(error);
            });

    },
    methods: {
        store: function () {
            window.axios.put('', this.update)
                .then((response) => {
                    window.ajaxSuccess(response.data.message);
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        },

        //Check if user has role
        hasRole: function (employee, roleName) {
            if (employee.roles) {
                let exist = employee.roles.filter((role) => role.name === roleName);
                return exist.length > 0;
            }
            return false;
        },

        init: function () {
            this.$nextTick(() => {
                $('input[type="checkbox"]').iCheck({
                    checkboxClass: 'icheckbox_square-blue',
                    radioClass: 'iradio_square-blue',
                    increaseArea: '20%' // optional
                }).on('ifChanged', (e) => {
                    let $this = $(e.target),
                        employeeId = $this.data('employee-id'),
                        roleName = $this.data('role-name'),
                        status = e.target.checked;

                    let employee = this.update.filter((data) => data.employeeId === employeeId);
                    if (!employee.length) {
                        //add new
                        this.update.push({
                            employeeId: employeeId,
                            roles: [{ name: roleName, status: status }]
                        });
                        return;
                    }
                    let role = employee[0].roles.filter((role) => role.name === roleName);
                    if (!role.length) {
                        //add new
                        employee[0].roles.push({
                            name: roleName, status: status
                        });
                        return;
                    }
                    //set new status of the role
                    role[0].status = status;
                });

                //Tooltop
                $('[data-toggle="tooltip"]').tooltip();
            });
        }
    },
    computed: {
        isSaveEnabled: function () {
            return this.update.length == 0;
        }
    }
});