<template>
<!-- Modal email -->
    <div class="modal fade" id="popup-email">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Подключите вашу почту к КП10</h4>
          </div>
          <div class="modal-body">
            <div v-if="smtpEmails.length">
              <table class="table table-hover table-striped">
                <thead>
                  <tr>
                    <th>Email</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(data, index) in smtpEmails" :key="index">
                    <td>
                      <div class="row">
                        <div class="col-xs-8 col-sm-9">{{data.smtp_login}}</div>
                        <div class="col-xs-3 col-sm-3">
                          <div class="pull-right">
                            <button type="button" class="btn btn-default btn-sm"
                              @click="editSmtp(data)"><i class="fa fa-pencil"></i></button>
                            <button type="button" class="btn btn-default btn-sm"
                              @click="removeSmtp(data.id)"><i class="fa fa-trash"></i></button>
                          </div>
                        </div>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <p>
              <button class="btn btn-default btn-sm" type="button"
                  @click="toggleSmtp()">
                <i class="fa fa-envelope"></i> {{!smtp.showForm?'Добавить почту':'Скрыть'}}
              </button>
            </p>
            <div class="collapse" id="collapse">
              <div class="well" :class="{'disabled': smtp.ajax}">
                <!-- <a class="btn btn-app">
                    <i class="fa fa-envelope"></i> Яндекс
                </a>
                <a class="btn btn-app">
                    <i class="fa fa-google"></i> Gmail
                </a>
                <a class="btn btn-app active">
                    <i class="fa fa-envelope"></i> Другой
                </a> -->
                <div class="form-group" :class="{'has-error': smtp.errors.login}">
                    <input type="text" placeholder="Ваш email адрес" class="form-control" v-model="smtp.data.login" @keyup="smtp.errors.login=false">
                    <span class="help-block" v-if="smtp.errors.login">Поле обязательно для заполнения и должно быть действительным email</span>
                </div>
                <div class="form-group" :class="{'has-error': smtp.errors.password}">
                    <input type="password" placeholder="Пароль от почты" class="form-control" v-model="smtp.data.password" @keyup="smtp.errors.password=false">
                    <span class="help-block" v-if="smtp.errors.password">Поле обязательно для заполнения</span>
                </div>
                <div class="form-group" :class="{'has-error': smtp.errors.server}">
                    <input type="text" placeholder="Адрес smtp сервера" class="form-control" v-model="smtp.data.server" @keyup="smtp.errors.server=false">
                    <span class="help-block" v-if="smtp.errors.server">Поле обязательно для заполнения</span>
                </div>
                <div class="row">
                    <div class="col-md-10 col-xs-9">
                        <div class="form-group" :class="{'has-error': smtp.errors.port}">
                            <input type="text" placeholder="Порт smtp сервера" class="form-control" v-model="smtp.data.port" @keyup="smtp.errors.port=false">
                            <span class="help-block" v-if="smtp.errors.port">Поле обязательно для заполнения</span>
                        </div>
                    </div>
                    <div class="col-md-2 col-xs-3">
                        <div>
                            <label>
                                <input type="checkbox" v-model="smtp.data.secure">
                                TLS/SSL
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" @click="addSmtp">Сохранить</button>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Отмена</button>
          </div>
        </div>
      </div>
    </div>
</template>
<script>
export default {
  props: {
    smtpEmails: {},
    userId: 0
  },
  data() {
    return {
      smtp: {
        id: 0,
        data: {
          login: "",
          password: "",
          server: "",
          port: "",
          secure: false,
          userId: 0
        },
        errors: {
          login: false,
          password: false,
          server: false,
          port: false
        },
        showForm: false,
        ajax: false //Ajax request
      }
    };
  },
  mounted() {
    //Smtp checkbox
    $('input[type="checkbox"]')
      .iCheck({
        checkboxClass: "icheckbox_flat-blue"
      })
      .on("ifChanged", e => {
        //Fix for vue event
        this.smtp.data.secure = !this.smtp.data.secure;
      });
  },
  methods: {
    addSmtp() {
      this.smtp.errors.login = !this.validEmail(this.smtp.data.login);
      this.smtp.errors.password = !this.smtp.data.password;
      this.smtp.errors.server = !this.smtp.data.server;
      this.smtp.errors.port = !this.smtp.data.port;

      if (
        this.smtp.data.login &&
        this.smtp.data.password &&
        this.smtp.data.server &&
        this.smtp.data.port
      ) {
        //Update user id
        this.smtp.data.userId = this.userId;
        //Ajax status
        this.smtp.ajax = true;
        //Show ajax request
        window.Pace && window.Pace.restart();
        if (this.smtp.id) {
          window.axios
            .put("/settings/integration/email/" + this.smtp.id, this.smtp.data)
            .then(response => {
              this.smtpEmails.forEach((data, index) => {
                if (data.id == response.data.id) {
                  this.smtpEmails.splice(index, 1, response.data);
                }
              });
              this.toggleSmtp();
              //Ajax status
              this.smtp.ajax = false;
            })
            .catch(error => {
              //Ajax status
              this.smtp.ajax = false;
              window.ajaxError(error);
            });
        } else {
          //Create
          window.axios
            .post("/settings/integration/email", this.smtp.data)
            .then(response => {
              this.smtpEmails.push(response.data);
              $("#collapse").collapse("hide");
              this.clearSmtp();
              this.smtp.showForm = false;
              //Ajax status
              this.smtp.ajax = false;
              //
              this.$parent.$emit("employeeSmtp:save:success", this.smtpEmails);
            })
            .catch(error => {
              window.ajaxError(error);
              //Ajax status
              this.smtp.ajax = false;
            });
        }
      }
    },
    clearSmtp() {
      this.smtp.data.login = "";
      this.smtp.data.password = "";
      this.smtp.data.server = "";
      this.smtp.data.port = "";
      $('#popup-email input[type="checkbox"]').iCheck("uncheck");
    },
    editSmtp(data) {
      this.smtp.data.login = data.smtp_login;
      this.smtp.data.password = data.smtp_password;
      this.smtp.data.server = data.smtp_server;
      this.smtp.data.port = data.smtp_port;
      this.smtp.id = data.id;

      if (data.smtp_secure) {
        $('#popup-email input[type="checkbox"]').iCheck("check");
      }

      $("#collapse").collapse("show");
      this.smtp.showForm = true;
    },
    removeSmtp(id) {
      if (id) {
        //Show ajax request
        window.Pace && window.Pace.restart();
        window.axios
          .delete("/settings/integration/email/" + id, {
            data: { userId: this.userId }
          })
          .then(response => {
            this.smtpEmails.forEach((data, index) => {
              if (data.id == id) {
                this.smtpEmails.splice(index, 1);
              }
            });
          })
          .catch(error => {
            window.ajaxError(error);
          });
      }
    },
    toggleSmtp() {
      if (this.smtp.showForm) {
        $("#collapse").collapse("hide");
      } else {
        $("#collapse").collapse("show");
      }
      this.smtp.showForm = !this.smtp.showForm;
      this.clearSmtp();
    },
    showModal() {
      $("#popup-email").modal("show");
    },
    hideModal() {
      $("#popup-email").modal("hide");
    },
    validEmail: function (email) {
      var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
      return re.test(email);
    }
  }
};
</script>