new window.Vue({
    el: '#app',
    data: {
        roleId: 0,
        role: '',
        permissions: [],
        update: [],
        roleName: '',
    },
    mounted: function () {

        //Get employee card for initialize boject
        window.axios.get('json')
            .then((response) => {
                let data = response.data;
                this.roleId = data.id;
                this.role = data.name;
                this.roleName = data.translation_relation.translation;

                //get stored permissions
                let permissions = response.data.permissions.map(function (p) {
                    return { name: p.name, value: 1 };
                });
                //set permissions
                this.update = permissions;

                //Get permission
                window.axios.get('/settings/permission/json')
                    .then((response) => {
                        let permissions = response.data,
                            data = [],
                            ex = ['edit-own', 'delete-own', 'view-own'];

                        if (permissions) {
                            $.each(permissions, (index, permission) => {
                                let nameArr = permission.name.split(' '),
                                    action = nameArr[0],
                                    page = nameArr[1];

                                //find same page
                                let perm = data.filter(function (val) {
                                    return val.page === page;
                                });
                                //for custom actions
                                if (ex.indexOf(action) !== -1) {
                                    //get real name
                                    let actionReal = action.split('-')[0];
                                    //find relative permission
                                    let permData = perm[0].permissions.filter(function (val) {
                                        return val.action === actionReal
                                    });
                                    if (permData.length) {
                                        if (!permData[0].custom) {
                                            permData[0].custom = [];
                                        }
                                        // console.log(permData[0]);
                                        permData[0].custom.push({
                                            action: action,
                                            roles: permission.roles
                                        });
                                    }
                                    return;
                                }

                                if (!perm.length) {
                                    //create first
                                    data.push({
                                        page: page,
                                        name: this.getPageTranslation(page),
                                        permissions: [{
                                            action: action,
                                            name: permission.name,
                                            translation: this.getTranslation(action),
                                            roles: permission.roles
                                            // buttons: this.getButtons(permission)
                                        }]
                                    })
                                    return;
                                }

                                perm[0].permissions.push({
                                    action: action,
                                    name: permission.name,
                                    translation: this.getTranslation(action),
                                    roles: permission.roles
                                    // buttons: this.getButtons(permission)
                                });
                            });

                            //add buttons
                            $.each(data, (index, value) => {
                                $.each(value.permissions, (i, perm) => {
                                    perm.buttons = this.getButtons(value.page, perm);
                                });
                            });

                            this.permissions = data;
                        }
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
            window.axios.put('/settings/permission/' + this.roleId, this.update)
                .then((response) => {
                    window.ajaxSuccess(response.data.message);
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        },
        //Get translation by permission
        getTranslation: function (action) {
            switch (action) {
                case 'create':
                    return 'Создание';
                case 'view':
                    return 'Просмотр';
                case 'edit':
                    return 'Редактирование';
                case 'delete':
                    return 'Удаление';
                case 'import':
                    return 'Загрузка';
            }
        },
        //Get button by permission name
        getButtons: function (page, permission) {

            let name = permission.name,
                action = permission.action,
                role = this.role,
                roles = permission.roles,
                custom = permission.custom ? permission.custom : [],
                nameOwn = action + '-own ' + page;

            let active = roles.filter(function (r) {
                return r.name === role
            }).length > 0;

            let activeOwn = custom.filter(function (c) {
                return c.action === action + '-own' && c.roles.filter(function (cr) { return cr.name === role }).length > 0
            }).length > 0;
            
            if(page === 'product' || page === 'file-manager' || page === 'settings'){
                switch (action) {
                    case 'create':
                        return [
                            { name: name, value: true, label: 'Да', active: active },
                            { name: name, value: false, label: 'Нет', active: !active }
                        ];
                    case 'view':
                        return [
                            { name: name, value: true, label: 'Да', active: active },
                            { name: name, value: false, label: 'Нет', active: !active }
                        ];
                    case 'edit':
                        return [
                            { name: name, value: true, label: 'Да', active: active },
                            { name: name, value: false, label: 'Нет', active: !active }
                        ];
                    case 'delete':
                        return [
                            { name: name, value: true, label: 'Да', active: active },
                            { name: name, value: false, label: 'Нет', active: !active }
                        ];
                    case 'import':
                        return [
                            { name: name, value: true, label: 'Да', active: active },
                            { name: name, value: false, label: 'Нет', active: !active }
                        ];
    
                }
            }

            switch (action) {
                case 'create':
                    return [
                        { name: name, value: true, label: 'Да', active: active },
                        { name: name, value: false, label: 'Нет', active: !active }
                    ];
                case 'view':
                    return [
                        { name: name, value: true, label: 'Все', active: active },
                        { name: nameOwn, value: true, label: 'Свои', active: activeOwn },
                        { name: name, value: false, label: 'Нет', active: !active && !activeOwn }
                    ];
                case 'edit':
                    return [
                        { name: name, value: true, label: 'Все', active: active },
                        { name: nameOwn, value: true, label: 'Свои', active: activeOwn },
                        { name: name, value: false, label: 'Нет', active: !active && !activeOwn }
                    ];
                case 'delete':
                    return [
                        { name: name, value: true, label: 'Все', active: active },
                        { name: nameOwn, value: true, label: 'Свои', active: activeOwn },
                        { name: name, value: false, label: 'Нет', active: !active && !activeOwn }
                    ];
            }

        },
        //Get page translation
        getPageTranslation: function (page) {
            switch (page) {
                case 'offer':
                    return 'КП';
                case 'product':
                    return 'Товары';
                case 'file-manager':
                    return 'Фотографии';
                case 'client':
                    return 'Клиенты';
                case 'settings':
                    return 'Настройки';
            }
        },
        //Check enable
        isPermissionEnabled: function (role, roles) {
            return roles.filter(function (role) {
                return role.name === role
            }).length > 0
        },
        //Update result array
        changeUpdate: function (e, button) {
            let $this = $(e.target),
                name = $this.data('name'),
                nameReal = name.replace(/(-\w+)?/g, '');

            $this.closest('div').find('button.active').removeClass('active');
            $this.addClass('active');

            //remove exists
            this.update.forEach((element, index) => {
                //find same permission
                if (element.name.replace(/(-\w+)?/g, '') === nameReal) {
                    //remove
                    this.update.splice(index, 1);
                }
            });
            //add only enabled permission
            if (button.value) {
                //add permission
                this.update.push({ name: name, value: button.value });
            }
        }
    },
    computed: {
        isSaveEnabled: function () {
            return this.update.length == 0;
        }
    }
});