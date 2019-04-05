new window.Vue({
    el: '#app',
    data: {
        scenario: [],
        eventsList: [],
        additionalEventsList: [],
        additionalSecondEventsList: [],
        additionalActionsList: [],
        additionalSecondActionsList: [],
        actionsList: [],
        storedScenario: [],
        saveScenario: {
            event: [],
            action: [],
            additional_event: [],
            additional_action: []
        },
        crmStates: [],
        additionalEventFlag: false,
        additionalSecondEventFlag: false,
        additionalSecondActionFlag: false,
        additionalSecondEventCondition: false,
        additionalSecondActionCondition: false,
        additionalActionFlag: false,
        additionalActionType: '',
        fullConditions: false,
        disabledSaveBtn: false,
        disIfChanged: false
    },
    mounted: function () {
        //Получаем список уже созданных сценариев
        window.axios.get('/settings/scenario/json')
            .then((response) => {
                let status = response.data.status;

                if (status) {
                    let scenario = response.data.scenario;
                    $.each(scenario, (key, value) => {

                        let actions = value.actions,
                            events = value.events,
                            additionalEventId = 0,
                            additionalEventName = '',
                            additionalActionId = 0,
                            additionalActionName = '';

                        if (events.length) {

                            $.each(events, (item, event) => {
                                switch (Number(value.event_id)) {
                                    case 2: //client don't open message
                                        switch (event.event_type) {
                                            case 'id':
                                                additionalEventId = event.event_value;
                                                break;
                                            case 'name':
                                                additionalEventName = event.event_value;
                                                break;
                                        }
                                        break;
                                    case 8: //crm states
                                        switch (event.event_type) {
                                            case 'id':
                                                additionalEventId = event.event_value;
                                                break;
                                            case 'program_name':
                                                additionalEventName = 'по схеме "' + event.event_value + '"';
                                                break;
                                            case 'crm_status_name':
                                                additionalEventName += ' на статус "' + event.event_value + '"';
                                                break;
                                        }
                                        break;
                                    case 9: //kp states
                                        switch (event.event_type) {
                                            case 'status_id':
                                                additionalEventId = event.event_value;
                                                break;
                                            case 'status_name':
                                                additionalEventName = 'на "' + event.event_value + '"';
                                                break;
                                        }
                                        break;
                                }
                            });
                        }

                        if (actions.length) {
                            $.each(actions, (num, action) => {
                                switch (Number(value.action_id)) {
                                    case 4: //create deal/task
                                        switch (action.action_type) {
                                            case 'text':
                                                additionalActionName = 'с описанием: "' + action.action_value + '"';
                                                break;
                                        }
                                        break;
                                    case 7: //crm states
                                        switch (action.action_type) {
                                            case 'id':
                                                additionalActionId = action.action_value;
                                                break;
                                            case 'program_name':
                                                additionalActionName = 'по схеме "' + action.action_value + '"';
                                                break;
                                            case 'crm_status_name':
                                                additionalActionName += ' на статус "' + action.action_value + '"';
                                                break;
                                        }
                                        break;
                                    case 8: //kp states
                                        switch (action.action_type) {
                                            case 'status_id':
                                                additionalActionId = action.action_value;
                                                break;
                                            case 'status_name':
                                                additionalActionName = 'на "' + action.action_value + '"';
                                                break;
                                        }
                                        break;
                                }
                            });
                        }

                        this.storedScenario.push({
                            event_id: value.event_id,
                            event_name: value.scenario_event.name,
                            action_id: value.action_id,
                            action_name: value.scenario_action.name,
                            additional_event: additionalEventName,
                            additional_action: additionalActionName
                        });
                    });
                }
            })
            .catch((error) => {
                window.ajaxError(error);
            });
    },
    methods: {
        showStoredScenario: function (events) {
        },

        createScenario() {
            this.saveScenario.event = [];
            this.saveScenario.action = [];
            this.saveScenario.additional_event = [];
            this.saveScenario.additional_action = [];

            this.eventsList = [];
            this.actionsList = [];

            this.additionalEventsList = []; //дополнительный список условий
            this.additionalSecondEventsList = []; //второй дополнительный список условий
            this.additionalActionsList = []; //дополнительный список действий
            this.additionalSecondActionsList = []; //второй дополнительный список действий

            this.additionalEventFlag = false;
            this.additionalActionFlag = false;
            this.additionalSecondEventFlag = false;
            this.additionalSecondActionFlag = false;

            this.additionalSecondEventCondition = false;
            this.additionalSecondActionCondition = false;

            window.axios.get('/settings/scenario/get/events')
                .then((response) => {
                    let eventsList = response.data;

                    if (eventsList.length) {
                        $.each(eventsList, (key, value) => {
                            this.eventsList.push({id: value.id, name: value.name});
                        });

                        this.fullConditions = false;
                        $("#modal-scenario").modal("show");
                    }
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        },

        /**
         * Выбор основного условия
         *
         * @param e
         */
        selectEvent: function (e) {
            let eventId = Number(e.target.value),
                eventName = ($(e.target).find('option:selected').text()).trim();

            if (eventId != -1) {

                this.disIfChanged = true;

                window.axios.get('/settings/scenario/get/actions/' + eventId)
                    .then((response) => {
                        let actionsList = response.data.actions,
                            additionalEvents = response.data.additional_events;

                        this.additionalEventsList = []; //дополнительный список условий
                        this.additionalSecondEventsList = []; //второй дополнительный список условий

                        //флаги
                        this.additionalEventFlag = false;
                        this.additionalActionFlag = false;
                        this.additionalSecondEventFlag = false;
                        this.additionalSecondActionFlag = false;
                        this.additionalSecondEventCondition = false;
                        this.additionalSecondActionCondition = false;

                        this.additionalActionsList = []; //дополнительный список действий
                        this.additionalSecondActionsList = []; //второй дополнительный список действий

                        switch (eventId) {
                            case 2: //действия по времени
                                this.additionalEventFlag = true;
                                this.additionalEventsList.push(
                                    {
                                        id: -1,
                                        name: 'Укажите время'
                                    },
                                    {
                                        id: 10800,
                                        name: '3 часов'
                                    },
                                    {
                                        id: 21600,
                                        name: '6 часов'
                                    },
                                    {
                                        id: 86400,
                                        name: '1 дня'
                                    },
                                    {
                                        id: 172800,
                                        name: '2 дней'

                                    },
                                    {
                                        id: 259200,
                                        name: '3 дней'
                                    }
                                );
                                break;
                            case 8: //статусы crm
                                if (additionalEvents.programs.length) {
                                    this.crmStates = additionalEvents;
                                    this.additionalEventFlag = true;
                                    this.additionalEventsList.push({
                                        id: -1,
                                        name: 'Выберите схему сделки или воронку'
                                    });
                                    $.each(additionalEvents.programs, (key, value) => {
                                        this.additionalEventsList.push({
                                            id: value['program_id'],
                                            name: value['program_name']
                                        });
                                    });
                                }
                                break;
                            case 9: //статусы КП
                                if (additionalEvents.length) {
                                    this.additionalEventFlag = true;
                                    this.additionalEventsList.push({
                                        id: -1,
                                        name: 'Выберите статус КП'
                                    });
                                    $.each(additionalEvents, (key, value) => {
                                        this.additionalEventsList.push({
                                            id: value.id,
                                            name: value.name
                                        });
                                    });
                                }
                                break;
                        }

                        this.actionsList = [];
                        $.each(actionsList, (key, value) => {
                            this.actionsList.push({id: value.id, name: value.name});
                        });

                        this.disIfChanged = false;
                        this.saveScenario.event[0] = {id: eventId, name: eventName};

                        this.checkConditions();
                    })
                    .catch((error) => {
                        this.actionsList = [];
                        this.additionalEventsList = [];
                        window.ajaxError(error);
                    });
            } else {
                this.saveScenario.event = [];
                this.saveScenario.action = [];
                this.additionalEventsList = [];
                this.actionsList = [];
                this.additionalActionFlag = false;
                this.additionalSecondEventFlag = false;
                this.fullConditions = false;
            }
        },

        /**
         * Выбор дополнительного условия
         *
         * @param e
         */
        selectAdditionalEvent: function (e) {
            let additionalEventId = e.target.value,
                additionalEventName = ($(e.target).find('option:selected').text()).trim();

            if (additionalEventId != -1) {

                switch (this.saveScenario.event[0]['id']) {
                    case 2: //клиент не открыл письмо в течении
                        this.saveScenario.additional_event[0] = [
                            {
                                event_type: 'id',
                                event_value: additionalEventId
                            },
                            {
                                event_type: 'name',
                                event_value: additionalEventName
                            }
                        ];

                        this.checkConditions();
                        break;
                    case 8: //статусы CRM
                        this.saveScenario.additional_event[0] = [
                            {
                                event_type: 'type',
                                event_value: 2
                            },
                            {
                                event_type: 'crm',
                                event_value: this.crmStates.crm_type
                            },
                            {
                                event_type: 'program_id',
                                event_value: additionalEventId
                            },
                            {
                                event_type: 'program_name',
                                event_value: additionalEventName
                            }
                        ];

                        this.additionalSecondEventsList = [];
                        $.each(this.crmStates.programs, (key, value) => {
                            if (value.program_id == additionalEventId) {
                                this.additionalSecondEventsList = value.states;
                            }
                        });

                        this.additionalSecondEventFlag = true;
                        break;
                    case 9: //статусы КП
                        this.saveScenario.additional_event[0] = [
                            {
                                event_type: 'type',
                                event_value: 3
                            },
                            {
                                event_type: 'status_id',
                                event_value: additionalEventId
                            },
                            {
                                event_type: 'status_name',
                                event_value: additionalEventName
                            }
                        ];
                        this.checkConditions();
                        break;
                }
            } else {
                this.additionalSecondEventFlag = false;
                this.saveScenario.additional_event = [];
                this.fullConditions = false;
            }
        },

        /**
         * Выбор второго дополнительного условия
         * (пока только в случае выбора основного условия "Изменился статус в CRM")
         *
         * @param e
         */
        selectAdditionalSecondEvent: function (e) {
            let additionalSecondEventId = e.target.value,
                additionalSecondEventName = ($(e.target).find('option:selected').text()).trim();

            if (additionalSecondEventId != -1) {

                this.saveScenario.additional_event[0].length = 4;

                this.saveScenario.additional_event[0].push(
                    {
                        event_type: 'crm_status_id',
                        event_value: additionalSecondEventId
                    },
                    {
                        event_type: 'crm_status_name',
                        event_value: additionalSecondEventName
                    }
                );

                this.additionalSecondEventCondition = true;

                this.checkConditions();
            } else {
                this.additionalSecondEventCondition = false;
                this.fullConditions = false;
            }
        },

        /**
         * Выбор дополнительного действия
         *
         * @param e
         */
        selectAdditionalAction: function (e) {
            let additionalActionId = e.target.value,
                additionalActionName = ($(e.target).find('option:selected').text()).trim();

            if (additionalActionId != -1) {

                switch (this.saveScenario.action[0]['id']) {
                    case 7: //статусы CRM
                        this.saveScenario.additional_action[0] = [
                            {
                                action_type: 'type',
                                action_value: 2
                            },
                            {
                                action_type: 'crm',
                                action_value: this.crmStates.crm_type
                            },
                            {
                                action_type: 'program_id',
                                action_value: additionalActionId
                            },
                            {
                                action_type: 'program_name',
                                action_value: additionalActionName
                            }
                        ];

                        this.additionalSecondActionsList = [];
                        $.each(this.crmStates.programs, (key, value) => {
                            if (value.program_id == additionalActionId) {
                                this.additionalSecondActionsList = value.states;
                            }
                        });

                        this.additionalSecondActionFlag = true;
                        break;
                    case 8: //статусы КП
                        this.saveScenario.additional_action[0] = [
                            {
                                action_type: 'type',
                                action_value: 3
                            },
                            {
                                action_type: 'status_id',
                                action_value: additionalActionId
                            },
                            {
                                action_type: 'status_name',
                                action_value: additionalActionName
                            }
                        ];
                        this.checkConditions();
                        break;
                }
            } else {
                this.additionalSecondActionFlag = false;
                this.saveScenario.additional_action = [];
                this.fullConditions = false;
            }
        },

        /**
         * Выбор второго дополнительного действия
         * (пока только при выборе действия "изменить статус сделки в CRM")
         *
         * @param e
         */
        selectAdditionalSecondAction: function (e) {
            let additionalSecondActionId = e.target.value,
                additionalSecondActionName = ($(e.target).find('option:selected').text()).trim();

            if (additionalSecondActionId != -1) {

                this.saveScenario.additional_action[0].length = 4;

                this.saveScenario.additional_action[0].push(
                    {
                        action_type: 'crm_status_id',
                        action_value: additionalSecondActionId
                    },
                    {
                        action_type: 'crm_status_name',
                        action_value: additionalSecondActionName
                    }
                );

                this.additionalSecondActionCondition = true;

                this.checkConditions();
            } else {
                this.additionalSecondActionCondition = false;
                this.fullConditions = false;
            }
        },

        /**
         * Ввод текста в поле дополнительного действия
         * (пока только для действия "Создать дело/задачу в CRM)
         *
         * @param e
         */
        inputAdditionalAction: function (e) {
            let additionalActionName = ($(e.target).val()).trim();

            if (additionalActionName != '') {
                this.saveScenario.additional_action[0] = [
                    {
                        action_type: 'type',
                        action_value: 'task'
                    },
                    {
                        action_type: 'text',
                        action_value: additionalActionName
                    }
                ];

                this.checkConditions();
            } else {
                this.saveScenario.additional_action = [];
                this.fullConditions = false;
            }
        },

        /**
         * Выбор основного действия
         *
         * @param e
         */
        selectAction: function (e) {
            let actionId = Number(e.target.value),
                actionName = ($(e.target).find('option:selected').text()).trim();

            if (actionId != -1) {

                this.saveScenario.action[0] = {id: actionId, name: actionName};

                this.additionalActionsList = []; //дополнительный список действий
                this.additionalSecondActionsList = []; //второй дополнительный список действий

                this.additionalSecondActionFlag = false;
                this.additionalSecondActionCondition = false;

                switch (actionId) {
                    case 4: //создать дело/задачу
                        this.additionalActionFlag = true;
                        this.additionalActionType = 'input';
                        this.fullConditions = false;
                        break;
                    case 7: //статусы CRM
                        this.disIfChanged = true;
                        window.axios.get('/settings/scenario/get/additional-actions/' + actionId)
                            .then((response) => {

                                let additionalActions = response.data;

                                if (additionalActions.programs.length) {

                                    this.additionalActionType = 'select';
                                    this.crmStates = additionalActions;

                                    this.additionalActionFlag = true;

                                    this.additionalActionsList.push({
                                        id: -1,
                                        name: 'Выберите схему сделки или воронку'
                                    });
                                    $.each(additionalActions.programs, (key, value) => {
                                        this.additionalActionsList.push({
                                            id: value['program_id'],
                                            name: value['program_name']
                                        });
                                    });
                                }

                                this.fullConditions = false;
                                this.disIfChanged = false; //todo AIM наверное нужно убрать проверить точно
                            })
                            .catch((error) => {
                                window.ajaxError(error);
                            });

                        break;
                    case 8:
                        this.disIfChanged = true;

                        window.axios.get('/settings/scenario/get/additional-actions/' + actionId)
                            .then((response) => {
                                let additionalActions = response.data;


                                this.additionalActionFlag = true;
                                this.fullConditions = false;

                                if (additionalActions.length) {
                                    this.additionalActionType = 'select';
                                    this.additionalActionFlag = true;
                                    this.additionalActionsList.push({
                                        id: -1,
                                        name: 'Выберите статус КП'
                                    });
                                    $.each(additionalActions, (key, value) => {
                                        this.additionalActionsList.push({
                                            id: value.id,
                                            name: value.name
                                        });
                                    });
                                }

                                this.disIfChanged = false;
                            })
                            .catch((error) => {
                                window.ajaxError(error);
                            });
                        break;
                    default:
                        this.additionalActionFlag = false;
                        this.checkConditions();
                        break;
                }
            } else {
                this.saveScenario.action = [];
                this.fullConditions = false;
                this.additionalActionFlag = false;
            }
        },

        /**
         * Проверка целостности сценария
         */
        checkConditions() {

            //Отображен блок дополнительных условий, но условие не выбрано
            if (this.additionalEventFlag && !this.saveScenario.additional_event.length) {
                this.fullConditions = false;
                return;
            }

            //Отображен блок дополнительных действий, но действие не выбрано
            if (this.additionalActionFlag && !this.saveScenario.additional_action.length) {
                this.fullConditions = false;
                return;
            }

            if (this.additionalSecondEventFlag && !this.additionalSecondEventCondition) {
                this.fullConditions = false;
                return;
            }

            if (this.additionalSecondActionFlag && !this.additionalSecondActionCondition) {
                this.fullConditions = false;
                return;
            }

            if (this.saveScenario.event.length && this.saveScenario.action.length) {
                this.fullConditions = true;
            }
        },

        /**
         * Сохранить указанный сценарий
         */
        saveNewScenario() {
            if (this.saveScenario.action.length && this.saveScenario.event.length) {

                this.disIfChanged = true;
                this.disabledSaveBtn = true;

                window.axios.post('/settings/scenario/add', this.saveScenario)
                    .then((response) => {

                        let status = response.data.status;

                        if (status) {

                            let eventId = this.saveScenario.event[0]['id'],
                                eventName = this.saveScenario.event[0]['name'],
                                actionId = this.saveScenario.action[0]['id'],
                                actionName = this.saveScenario.action[0]['name'],
                                additionalEvents = this.saveScenario.additional_event,
                                additionalActions = this.saveScenario.additional_action,
                                additionalEventId = 0,
                                additionalEventName = '',
                                additionalActionId = 0,
                                additionalActionName = '';

                            if (additionalEvents.length) {
                                $.each(additionalEvents[0], (item, event) => {
                                    switch (Number(eventId)) {
                                        case 2: //client don't open message
                                            switch (event.event_type) {
                                                case 'id':
                                                    additionalEventId = event.event_value;
                                                    break;
                                                case 'name':
                                                    additionalEventName = event.event_value;
                                                    break;
                                            }
                                            break;
                                        case 8: //crm states
                                            switch (event.event_type) {
                                                case 'id':
                                                    additionalEventId = event.event_value;
                                                    break;
                                                case 'program_name':
                                                    additionalEventName = 'по схеме "' + event.event_value + '"';
                                                    break;
                                                case 'crm_status_name':
                                                    additionalEventName += ' на статус "' + event.event_value + '"';
                                                    break;
                                            }
                                            break;
                                        case 9: //kp states
                                            switch (event.event_type) {
                                                case 'status_id':
                                                    additionalEventId = event.event_value;
                                                    break;
                                                case 'status_name':
                                                    additionalEventName = 'на "' + event.event_value + '"';
                                                    break;
                                            }
                                            break;
                                    }
                                });
                            }

                            if (additionalActions.length) {
                                $.each(additionalActions[0], (num, action) => {
                                    switch (Number(actionId)) {
                                        case 4: //create deal/task
                                            switch (action.action_type) {
                                                case 'text':
                                                    additionalActionName = 'с описанием: "' + action.action_value + '"';
                                                    break;
                                            }
                                            break;
                                        case 7: //crm states
                                            switch (action.action_type) {
                                                case 'id':
                                                    additionalActionId = action.action_value;
                                                    break;
                                                case 'program_name':
                                                    additionalActionName = 'по схеме "' + action.action_value + '"';
                                                    break;
                                                case 'crm_status_name':
                                                    additionalActionName += ' на статус "' + action.action_value + '"';
                                                    break;
                                            }
                                            break;
                                        case 8: //kp states
                                            switch (action.action_type) {
                                                case 'status_id':
                                                    additionalActionId = action.action_value;
                                                    break;
                                                case 'status_name':
                                                    additionalActionName = 'на "' + action.action_value + '"';
                                                    break;
                                            }
                                            break;
                                    }
                                });
                            }

                            this.storedScenario.push({
                                event_id: eventId,
                                event_name: eventName,
                                action_id: actionId,
                                action_name: actionName,
                                additional_event: additionalEventName,
                                additional_action: additionalActionName
                            });

                            this.disIfChanged = false;
                            this.disabledSaveBtn = false;
                            $("#modal-scenario").modal("hide");
                        }
                    })
                    .catch((error) => {
                        window.ajaxError(error);
                        this.disabledSaveBtn = false;
                    });
            }
        },

        deleteScenario: function (index, scenario) {
            window.axios.post('/settings/scenario/delete', scenario)
                .then((response) => {
                    this.storedScenario.splice(index, 1);
                })
                .catch((error) => {
                    window.ajaxError(error);
                });
        }
    }
});