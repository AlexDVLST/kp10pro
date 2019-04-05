// Confirm popup window
// types: default, modal-primary, modal-info, modal-warning, modal-success, modal-danger
window.modal = function (params) {
    let settings = $.extend({
        'modalTitle': '',
        'modalMessage': '',
        'okTitle': 'Да',
        'okClass': '',
        'cancelTitle': 'Отмена',
        'type': 'modal-default'
    }, params);

    let $popupWindowConfirm = $('#popup-window-confirm');

    //check if message doesn't contain html
    if (!/^<\w+>/gm.test(settings.modalMessage)) {
        settings.modalMessage = '<p class="modal-text">' + settings.modalMessage + '</p>';
    }

    $popupWindowConfirm.on('show.bs.modal', function (event) {
        let $this = $(this);
        $this.find('.modal-title').html(settings.modalTitle);
        $this.find('.modal-body').html(settings.modalMessage);
        $this.find('.btn-ok').html(settings.okTitle).addClass(settings.okClass);
        $this.find('.btn-cancel').html(settings.cancelTitle);
        $this.addClass(settings.type);
    });

    // Hide cancel button
    if (settings.cancelTitle === false) {
        $popupWindowConfirm.find('.btn-cancel').hide();
    }

    $popupWindowConfirm.modal('show');
    $popupWindowConfirm.find('.btn-ok').off().on('click', function () {
        if (settings.onOk) {
            settings.onOk($popupWindowConfirm);
        }
    });
    $popupWindowConfirm.find('.btn-cancel').off().on('click', function () {
        $popupWindowConfirm.modal('hide');
        if (settings.onCancel) {
            settings.onCancel();
        }
    });

};

/**
 * Using with Validator response format messages
 * @param error
 */
window.ajaxError = function (error) {
    let message = '';
    if (error && error.response.status === 422) {
        if (typeof error.response.data.errors === 'object') {

            for (let i in error.response.data.errors) {
                let er = error.response.data.errors[i];

                message += er[0] + "\n";
            }

        } else {
            message = error.response.data.errors;
        }

    } else {
        message = 'Ошибка сервера. ' + error.toString();
    }

    alert(message);
};

window.ajaxSuccess = function (message) {

    if (typeof message === 'object') {
        //TODO: визначитись з форматом

        alert(message.data.message);

    } else {
        alert(message);
    }
}

/**
 * Format number with spaces for better readable
 * 10000 > 10 000
 * @param {*} str 
 */
window.numberFormat = function (str) {
    str = str + '';//convert to string
    str = str.replace(/(\.(.*))/g, '');
    let arr = str.split(''),
        str_temp = '';
    if (str.length > 3) {
        for (let i = arr.length - 1, j = 1; i >= 0; i-- , j++) {
            str_temp = arr[i] + str_temp;
            if (j % 3 == 0) {
                str_temp = ' ' + str_temp;
            }
        }
        return str_temp;
    } else {
        return str;
    }
};

/**
 * Check user permissions
 * @param  permissin 
 */
window.can = function (params) {
    //If user has role user (this is administrator for account)
    if (window.laravel.user.roles.indexOf('user') !== -1) {
        return true;
    }

    let userId = params.userId,
        permission = params.permission,
        responsibles = params.responsibles,
        permissions = [],
        activePermissions = [],
        can = false; //Marker

    //Create array of the permissions
    if (typeof permission === 'string') {
        permissions.push(permission);
    } else {
        permissions = permission;
    }

    if (responsibles) {
        //Check responsible
        let responsible = responsibles.filter(function (item) {
            return item.user_id === window.laravel.user.id;
        }).length;
        if (responsible) {
            can = true;
        }
    }

    permissions.forEach(permission => {
        if (window.laravel.user.permissions.indexOf(permission) !== -1) {
            //Disabled
            can = true;
            //Fill active permissions
            activePermissions.push(permission);

            if (userId && permission.indexOf('own') !== -1 && window.laravel.user.id !== userId) {
                //If creator another user
                can = false;
            }
            //If userId exist and check create permission
            if (userId && permission.indexOf('create') !== -1){
                //Block
                can = false;
            }
        }
    });

    //If active permissions empty
    if (!activePermissions.length) {
        can = true;
    }

    return can;
};

//Derective for check user permission
Vue.directive('can', {
    bind(el, binding, vnode, old) {
        if (!window.can({ userId: binding.value.userId, permission: binding.value.permission, responsibles: binding.value.responsibles })) {
            //Hide
            el.style.display = 'none';
        }
    },
    componentUpdated(el, binding, vnode, old) {       
        if(binding.value.userId != binding.oldValue.userId){
            if (!window.can({ userId: binding.value.userId, permission: binding.value.permission, responsibles: binding.value.responsibles })) {
                //Hide
                el.style.display = 'none';
            }
        }
    }
});