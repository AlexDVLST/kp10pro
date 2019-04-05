<template>
    <div class="modal" id="notification-register-bonus-modal">
        <div class="modal-dialog modal-sm" :class="{'modal-disabled': ajax}">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <!-- <img src="/images/notifications/register-bonus.png"> -->
                    <h1 class="text-center">Бонус для новых клиентов!</h1>
                </div>
                <div class="modal-body">
                    <div v-if="!bonusActivated">
                      <h3 class="text-center">Получите 1 месяц бесплатно<sup>*</sup></h3>
                      <p class="text-center">За Вами будет закреплен менеджер, который ответит на вопросы и поможет создать первое КП</p>
                      <span class="help-block" v-if="error.id">Произошла непредвиденная ошибка, пожалуйста, обновите страницу</span>
                      <div class="form-group" :class="{'has-error': error.name}">
                          <label for="name" class="control-label">Имя Фамилия</label>
                          <input type="text" class="form-control" id="name" placeholder="Имя Фамилия" v-model="name">
                          <span class="help-block" v-if="error.name">Укажите имя и фамилию через пробел</span>
                      </div>
                      <div class="form-group" :class="{'has-error': error.company}">
                          <label for="company" class="control-label">Компания</label>
                          <input type="text" class="form-control" id="company" placeholder="Компания" v-model="data.company">
                          <span class="help-block" v-if="error.company">Укажите компанию или вид деятельности</span>
                      </div>
                      <div class="form-group" :class="{'has-error': error.phone}">
                          <label for="phone" class="control-label">Телефон</label>
                          <input type="text" class="form-control" id="phone" placeholder="Телефон" v-model="data.phone" maxlength="12">
                          <span class="help-block" v-if="error.phone">Укажите действительный номер телефона</span>
                      </div>
                      <div class="text-center">
                        <small><sup>*</sup>Подразумевается продление текущего 14-ти дневного бесплатного периода до 1 календарного месяца</small>
                      </div>
                    </div>
                    <div v-if="bonusActivated">
                      <h2 class="text-center">Ваш бонус успешно активирован</h2>
                      <p class="text-center">Текущий тариф и подробную информацию вы можете найти на странице <a href="/settings/order">Оплата сервиса</a></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-ok col-xs-12" v-if="!bonusActivated" @click="bonus">Получить бонус</button>
                    <button type="button" class="btn btn-success btn-ok col-xs-12" v-if="bonusActivated" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
  mounted() {
    $(this.$el)
      .modal("show")
      .on("hidden.bs.modal", () => {
        window.axios
          .put("/notification/" + this.notificationId + "/viewed")
          .then(() => {
            $(this.$el).modal("hide");
          })
          .catch(error => {
            window.ajaxError(error);
          });
      });
  },
  props: {
    notificationId: { default: 0 }
  },
  data() {
    return {
      ajax: false,
      bonusActivated: false,
      error: {
        id: false,
        name: false,
        company: false,
        phone: false
      },
      name: "",
      data: {
        firstName: "",
        lastName: "",
        company: "",
        phone: ""
      }
    };
  },
  methods: {
    bonus() {
      if (!this.data.firstName || !this.data.lastName) {
        this.error.name = true;
      }
      if (!this.data.company) {
        this.error.company = true;
      }
      if (!this.data.phone) {
        this.error.phone = true;
      }
      //Fatal error !
      if (!this.notificationId) {
        this.error.id = true;
      }
      if (this.bonusEnabled) {
        //Disable modal
        this.ajax = true;
        window.axios
          .put("/notification/" + this.notificationId + "/viewed", this.data)
          .then(() => {
            //Enable modal
            this.ajax = false;
            //Activate bonus and change message in modal
            this.bonusActivated = true;
          })
          .catch(error => {
            window.ajaxError(error);
          });
      }
    }
  },
  watch: {
    name: function(val) {
      let data = val.split(" ");
      //First name
      if (data[0]) {
        this.data.firstName = data[0];
      }
      //Last name
      if (data[1]) {
        this.data.lastName = data[1];
      }
    },
    data: {
      handler: function(data) {
        //Reset error
        this.error.name = false;
        this.error.company = false;
        this.error.phone = false;
        //Clean up phone
        this.data.phone = data.phone.replace(/[^0-9]/,'');
      },
      deep: true
    }
  },
  computed: {
    bonusEnabled() {
      return (
        this.data.firstName &&
        this.data.lastName &&
        this.data.company &&
        this.data.phone
      );
    }
  }
};
</script>
<style>
</style>
