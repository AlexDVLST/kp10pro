import employee from '../../components/Employee'

new window.Vue({
    el: '#app',
    components: { employee },
    mounted: function () {
        this.$on('employee-submit', this.createEmployee);
        //Update employee data
        this.$on('update', this.updateData);
    },
    data: {
        pageName: 'Добавление сотрудника',
        employee: {
            surname: '',
            name: '',
            middleName: '',
            email: '',
            phone: '',
            position: '',
            signature: '',
            fileId: '',
        }
    },
    methods: {
        createEmployee: function () {
            //Create employee
            window.axios.post("/settings/employee", this.employee)
                .then(() => {
                    //Redirect to employees page
                    location.href = "/settings/employee/";
                })
                .catch(error => {
                    window.ajaxError(error);
                });
        },
        //Update this model after child component updated
        updateData: function (data) {
            if (data.field) {
                this.employee[data.field] = data.value;
            }
        },
    },
});
