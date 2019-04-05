import employee from '../../components/Employee'

new window.Vue({
    el: '#app',
    components: { employee },
    mounted: function () {
        //Update employee
        this.$on('employee-submit', this.updateEmployee);
        //Update employee data
        this.$on('update', this.updateData);
        //Block employee
        this.$on('block', this.block);
        //Un block employee
        this.$on('unBlock', this.unBlock);
        //Change password
        this.$on('change-password', this.changePassword);

        //Get employee card for initialize boject
        window.axios.get('json')
            .then((response) => {
                this.employee = response.data;
                this.employee.middleName = response.data.middle_name;
                this.employee.fileId = response.data.avatarFileId;
                this.employee.fileSrc = response.data.avatarUrl;
                this.employee.smtpEmails = response.data.smtp_emails
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    data: {
        pageName: 'Редактирование сотрудника',
        employee: {
            id: '',
            surname: '',
            name: '',
            middleName: '',
            email: '',
            phone: '',
            position: '',
            signature: '',
            fileId: '',
            fileSrc: '',
            roles: [],
            trashed: false,
            smtpEmails: []
        }
    },
    methods: {
        updateEmployee: function () {

            if (this.employee && this.employee.id) {

                window.axios
                    .put("/settings/employee/" + this.employee.id, this.employee)
                    .then((response) => {
                        window.ajaxSuccess(response.data.message);
                    })
                    .catch(function (error) {
                        window.ajaxError(error);
                    });
            }
        },
        //Update this model after child component updated
        updateData: function (data) {
            if (data.field) {
                this.employee[data.field] = data.value;
            }
        },
        //Block employee
        block: function () {

            let _this = this;

            window.modal({
                'modalTitle': 'Подтверждение действия',
                'modalMessage': 'Вы уверены что хотите заблокировать сотрудника <strong>' + this.employee.surname + ' ' + this.employee.name + '</strong>?'
                    + '<div class="alert alert-warning"><p><i class="icon fa fa-warning"></i> Сотрудник не сможет авторизоваться в системе</p></div>',
                'onOk': function ($modal) {

                    $modal.modal('hide');
                    //Show ajax request
                    window.Pace.restart();

                    window.axios.post('block')
                        .then((response) => {
                            //Change employee param
                            _this.employee.trashed = true;
                        })
                        .catch((error) => {
                            console.log('error', error);
                            window.ajaxError(error);
                        })
                },
            });
        },
        //Unblock employee
        unBlock: function () {
            //Show ajax request
            window.Pace.restart();

            window.axios.post('unBlock')
                .then((response) => {
                    //Change employee param
                    this.employee.trashed = false;
                })
                .catch((error) => {
                    window.ajaxError(error);
                })
        },
        //Change password
        changePassword: function () {
            if (this.employee.id) {
                window.modal({
                    'modalTitle': 'Изменение пароля для сотрудника <strong>' + this.employee.surname + ' ' + this.employee.name + '</strong>',
                    'modalMessage': `
                    <form autocomplete="off">
                    <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Пароль" name="password" autocomplete="off">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                    <input type="password" class="form-control" placeholder="Повторите пароль" name="password_confirmation" autocomplete="off">
                    <span class="glyphicon glyphicon-log-in form-control-feedback"></span>
                    </div>
                    </form>
                    `,
                    'onOk': function ($modal) {

                        let password = $modal.find('input[name="password"]').val(),
                            passwordConfirm = $modal.find('input[name="password_confirmation"]').val();

                        if (!password) {
                            alert('Необходимо ввести пароль')
                            return;
                        }

                        if (password !== passwordConfirm) {
                            alert('Введенные пароли не совпадают');
                            return;
                        }

                        //Show ajax request
                        window.Pace.restart();

                        window.axios.post('change-password', { password: password, passwordConfirm: passwordConfirm })
                            .then(() => {

                                $modal.modal('hide');
                                // alert('Функция в разработке!');
                                // $this.closest('tr').remove();
                            })
                            .catch((error) => {
                                window.ajaxError(error);
                            })
                    },
                });
            }
        }
    }
});
