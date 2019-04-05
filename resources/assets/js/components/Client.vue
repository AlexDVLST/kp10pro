<template>
  <div ref="client" class="modal fade" id="modal-client" role="dialog">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <!-- modal-body -->
          <div> 
            <div class="nav-tabs-custom">
              <ul class="nav nav-tabs">
                <li :class="{active: isCompany}"><a href="#company" data-toggle="tooltip" title="Изменить тип клиента на Компанию" @click.prevent="data.type=1">Компания</a></li>
                <li :class="{active: isHuman}" ><a href="#human" data-toggle="tooltip" title="Изменить тип клиента на Человек" @click.prevent="data.type=2">Человек</a></li>
                <li :class="{active: isContactPerson}" ><a href="#contact-person" data-toggle="tooltip" title="Изменить тип клиента на Контактное лицо" @click.prevent="data.type=3">Контактное лицо</a></li>
              </ul>
              <div>
                <div class="box-body">
                  <div class="col-md-7">
                    <div class="form-group">
                      <div class="row">
                        <div class="col-xs-4" v-show="!isCompany" :class="[errors.surname ? 'has-error' : '']">
                          <input type="text" class="form-control" id="surname" placeholder="Фамилия" 
                                  :value="data.surname" @input="update('surname', $event.target.value)" autocomplete="off">
                        </div>
                        
                        <div class="" :class="{'has-error': errors.name, 'col-xs-12': isCompany, 'col-xs-4': !isCompany}">
                          <input type="text" class="form-control" id="name" :placeholder="isCompany?'Название':'Имя'" 
                                  :value="data.name" @input="update('name', $event.target.value)" autocomplete="off">
                        </div>
                        
                        <div class="col-xs-4" v-show="!isCompany" :class="[errors.middleName ? 'has-error' : '']">
                          <input type="text" class="form-control" id="middle_name" placeholder="Отчество" 
                                  :value="data.middle_name" @input="update('middle_name', $event.target.value)" autocomplete="off">
                        </div>
                      </div>
                    </div>
                    <div class="form-group" v-show="isContactPerson">
                    <input type="text" class="form-control input" placeholder="Должность" :value="data.position" @input="update('position', $event.target.value)">
                    </div>
                    <div class="form-group" v-show="isContactPerson">
                      <select class="form-control" id="company" style="width: 100%"
                        @change="update('company', $event.target.value)">
                        <option v-if="data.companyId" :value="data.companyId" selected="true">{{data.companyName}}</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <textarea class="form-control" id="description" placeholder="Описание..." 
                                :value="data.description" rows="6" @input="update('description', $event.target.value)"></textarea>
                    </div>
                  </div>
                  <div class="col-md-5">
                    <!-- Phones -->
                    <div class="form-group" @mouseover="showAddPhone = true" @mouseleave="showAddPhone = false">
                      <label>Телефон</label>
                      <button type="button" class="btn btn-default btn-xs pull-right" 
                          @click="add('phones', {id: 0, phone: '', default: false})"
                          v-show="showAddPhone"><i class="fa fa-plus"></i> Добавить</button>
                      <div class="form-group row" v-for="(phone, index) in data.phones" :key="index">
                        <div class="col-sm-11 col-xs-10">
                          <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-phone"></i></div>
                            <input type="text" class="form-control input-sm" placeholder="Телефон" 
                              :value="phone.phone" 
                              @input="update('phones', {phone: $event.target.value}, index)">
                            <div class="input-group-addon" title="Выбор по умолчанию">
                              <input type="radio" name="client-phone" 
                                :checked="phone.default" 
                                @change="update('phones', {default: $event.target.checked}, index)">
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-1 col-xs-2">
                          <button type="button" class="btn btn-default btn-sm pull-right" 
                            @click="remove('phones', index)"><i class="fa fa-trash"></i></button>
                        </div>
                      </div>
                    </div>
                    <!-- Emails -->
                    <div class="form-group" @mouseover="showAddEmail = true" @mouseleave="showAddEmail = false">
                      <label>Email</label>
                      <button type="button" class="btn btn-default btn-xs pull-right" 
                          @click="add('emails', {id: 0, email: '', default: false})"
                          v-show="showAddEmail"><i class="fa fa-plus"></i> Добавить</button>
                      <div class="form-group row" v-for="(email, index) in data.emails" :key="index">
                        <div class="col-sm-11 col-xs-10">
                          <div class="input-group">
                            <div class="input-group-addon"><i class="fa fa-envelope"></i></div>
                            <input type="text" class="form-control input-sm email" placeholder="Email" 
                              :data-index="index"  
                              :value="email.email"
                              @input="update('emails', {email: $event.target.value}, index)">
                            <div class="input-group-addon" title="Выбор по умолчанию">
                              <input type="radio" name="client-email"
                                :checked="email.default"
                                @change="update('emails', {default: $event.target.checked}, index)">
                            </div>
                          </div>
                        </div>
                        <div class="col-sm-1 col-xs-2">
                          <button type="button" class="btn btn-default btn-sm pull-right" 
                            @click="remove('emails', index)"><i class="fa fa-trash"></i></button>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Ответственный</label>
                      <div>
                        <select class="form-control" id="responsibles" multiple style="width:100%">
                          <option :value="user.id" v-for="user in data.users" :key="user.id" :selected="isResponsibleSelected(user.id)">{{user.displayName}}</option>
                        </select>
                      </div>
                    </div>
                    <div class="form-group" v-show="isCompany">
                      <label>Контактное лицо</label>
                      <div>
                        <select class="form-control" id="contact-person" multiple style="width:100%">
                          <option v-for="person in data.contactPersonList" :value="person.id" :key="person.id" :selected="isContactPersonSelected(person.id)">{{person.displayName}}</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /.box-body -->
                <!-- <div class="box-footer" v-can="{permission: ['create client', 'edit client', 'edit-own client'], userId: data.userId, responsibles: data.responsibles}">
                  <button type="submit" class="btn btn-primary">Сохранить</button>
                </div> -->
              </div>
              <!-- /.tab-content -->
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default pull-left" @click="hideModal()">Отмена</button>
            <div v-can="{permission: ['create client', 'edit client', 'edit-own client'], userId: data.userId, responsibles: data.responsibles}">
              <button type="button" class="btn btn-primary" @click="checkForm()">Сохранить</button>
            </div>
          </div>
        </div>
      </div>
  </div>
</template>

<script>
export default {
  props: {
    clientId: { default: 0 },
    loaded: { default: false }
  },
  data() {
    return {
      errors: { name: false },
      showAddEmail: false,
      showAddPhone: false,
      showForm: false,
      data: {
        id: 0,
        type: 1,
        userId: 0,
        surname: "",
        name: "",
        middle_name: "",
        description: "",
        position: "",
        phones: [{ id: 0, phone: "", default: true }],
        emails: [{ id: 0, email: "", default: true }],
        responsibles: [],
        users: [],
        contactPersons: [],
        companyId: 0,
        companyName: ""
      },
      contactPersonList: []
    };
  },
  created() {
    //Css
    let suggestionCss = document.createElement("link");
    suggestionCss.setAttribute("rel", "stylesheet");
    suggestionCss.setAttribute("href", "/plugins/dadata/suggestions.min.css");
    document.head.appendChild(suggestionCss);
    let select2Css = document.createElement("link");
    select2Css.setAttribute("rel", "stylesheet");
    select2Css.setAttribute(
      "href",
      "/plugins/select2/dist/css/select2.min.css"
    );
    document.head.insertBefore(select2Css, document.head.firstChild);
    //Add DaData js lib to component
    let suggestion = document.createElement("script");
    suggestion.setAttribute("src", "/plugins/dadata/jquery.suggestions.min.js");
    document.head.appendChild(suggestion);
    //Select2
    let select2 = document.createElement("script");
    select2.setAttribute("src", "/plugins/select2/dist/js/select2.full.min.js");
    document.head.appendChild(select2);
  },
  mounted() {
    //Load employees
    window.axios
      .get("/settings/employee/json")
      .then(response => {
        this.data.users = response.data;
      })
      .catch(error => {
        window.ajaxError(error);
      });
  },
  computed: {
    isCompany: function() {
      return this.data.type === 1;
    },
    isHuman: function() {
      return this.data.type === 2;
    },
    isContactPerson: function() {
      return this.data.type === 3;
    }
  },
  watch: {
    showForm: function() {
      this.$nextTick(function() {
        this.dadataCompany();
        //init DaData email
        this.dadataEmail();
        //init select2
        this.contactPersonSelect2();
        //init select2
        this.companySelect2();
        this.responsiblesSelect2();
      });
    },
    data: {
      handler: function() {
        this.$nextTick(() => {
          //Wait until form show
          //TODO: ???
          if (!this.showForm) {
            return;
          }
          //Initialize dadata hints
          if (this.data.type === 1) {
            //Company
            this.dadataCompany();
          } else {
            this.dadata();
          }
        });
      },
      deep: true
    }
  },
  methods: {
    //Check form for errors
    checkForm: function() {
      if (!this.data.name) this.errors.name = true;

      if (this.data.name) {
        if (this.data.id) {
          //Update client
          window.axios
            .put("/client/" + this.data.id, this.data)
            .then(response => {
              this.$parent.$emit("client:update:success", response.data);
              // window.ajaxSuccess(response.data.message);
            })
            .catch(error => {
              window.ajaxError(error);
            });
        } else {
          //Create employee
          window.axios
            .post("/client", this.data)
            .then(response => {
              this.$parent.$emit("client:create:success", response.data);
            })
            .catch(error => {
              window.ajaxError(error);
            });
        }
      }
    },
    //Clear error
    removeError: function(error) {
      if (this.errors[error]) {
        this.errors[error] = false;
      }
    },
    //Change type param
    setType: function(type) {
      this.type = type;
    },
    add(input, value) {
      this.data[input].push(value);
      if (input === "emails") {
        //Run after DOM updated
        this.$nextTick(() => {
          this.dadataEmail();
        });
      }
    },
    remove(input, value) {
      this.data[input].splice(value, 1);
    },
    //Update src for image
    changeFile() {
      if (window.fileManager) {
        let path = window.fileManager.path;

        if (!/Сотрудники/.test(path)) {
          path += "/Сотрудники";
        }

        window.fileManager.show(path);
        window.fileManager.disabledApp = true;
      }
    },
    update(input, value, index) {
      if (input) {
        if (typeof index === "undefined") {
          this.data[input] = value;
        } else {
          if (typeof value === "object") {
            $.each(this.data[input], (key, item) => {
              if (key === index) {
                $.each(value, function(dKey, dValue) {
                  //Update only defined key
                  item[dKey] = dValue;
                });
              }
            });
          } else {
            this.data[input].splice(index, 1, value);
          }
        }
      }
      //Clear errors
      this.removeError(input);
    },
    //For company
    dadataCompany() {
      let _this = this;

      $(this.$el)
        .find("#name")
        .suggestions({
          token: window.laravel.dadata.apiKey,
          type: "PARTY",
          count: 5,
          /* Вызывается, когда пользователь выбирает одну из подсказок */
          onSelect: function(suggestion) {
            //Update parent components
            _this.update($(this).attr("id"), suggestion.value);
            //Update description
            let description = "";

            if (_this.description) description += _this.description + "\n";
            if (suggestion.data.inn)
              description += "ИНН: " + suggestion.data.inn + "\n";
            if (suggestion.data.kpp)
              description += "КПП: " + suggestion.data.kpp + "\n";
            if (suggestion.data.ogrn)
              description += "ОГРН: " + suggestion.data.ogrn + "\n";
            if (suggestion.data.address.value)
              description += "Адрес: " + suggestion.data.address.value + "\n";
            if (suggestion.data.management && suggestion.data.management.post) {
              description +=
                suggestion.data.management.post +
                ": " +
                suggestion.data.management.name +
                "\n";
            }
            _this.update("description", description);
          }
        });
    },
    dadataEmail() {
      let _this = this;

      $(this.$el)
        .find(".email")
        .suggestions({
          token: window.laravel.dadata.apiKey,
          type: "EMAIL",
          count: 5,
          /* Вызывается, когда пользователь выбирает одну из подсказок */
          onSelect: function(suggestion) {
            //Update parent components
            _this.update(
              "emails",
              { email: suggestion.value },
              $(this).data("index")
            );
          }
        });
    },
    //For human
    dadata() {
      let _this = this,
        self = {},
        $surname = $(this.$el).find("#surname"),
        $name = $(this.$el).find("#name"),
        $patronymic = $(this.$el).find("#middle_name");

      self.$surname = $surname;
      self.$name = $name;
      self.$patronymic = $patronymic;

      let fioParts = ["SURNAME", "NAME", "PATRONYMIC"];

      $.each([$surname, $name, $patronymic], function(index, $el) {
        let sgt = $el.suggestions({
          token: window.laravel.dadata.apiKey,
          type: "NAME",
          triggerSelectOnSpace: false,
          hint: "",
          noCache: true,
          params: {
            // каждому полю --- соответствующая подсказка
            parts: [fioParts[index]]
          },
          onSearchStart: function(params) {
            // если пол известен на основании других полей,
            // используем его
            let $el = $(this);
            params.gender = _this.isGenderKnown(self, $el)
              ? self.gender
              : "UNKNOWN";
          },
          onSelect: function(suggestion) {
            // определяем пол по выбранной подсказке
            self.gender = suggestion.data.gender;
            //Update parent components
            _this.update($(this).attr("id"), suggestion.value);
          }
        });
      });
    },
    isGenderKnown(ths, $el) {
      var self = ths;
      var surname = self.$surname.val(),
        name = self.$name.val(),
        patronymic = self.$patronymic.val();
      if (
        ($el.attr("id") == self.$surname.attr("id") && !name && !patronymic) ||
        ($el.attr("id") == self.$name.attr("id") && !surname && !patronymic) ||
        ($el.attr("id") == self.$patronymic.attr("id") && !surname && !name)
      ) {
        return false;
      } else {
        return true;
      }
    },
    //Init select2
    contactPersonSelect2(destroy) {
      $(this.$el)
        .find("#contact-person")
        .select2({
          language: "ru",
          placeholder: "Выберите контактное лицо",
          // templateSelection: this.formatStateLink,
          ajax: {
            url: "/client/json",
            data: function(params) {
              var query = {
                search: params.term,
                page: params.page || 1
              };

              // Query parameters will be ?search=[term]&page=[page]
              return query;
            },
            processResults: function(data, params) {
              params.page = params.page || 1;
              // Tranforms the top-level key of the response object from 'items' to 'results'
              return {
                results: $.map(data.data, function(item) {
                  if (item.typeId === 3) {
                    return {
                      id: item.id,
                      text: item.displayName
                    };
                  }
                }),
                pagination: {
                  more: params.page * 10 < data.total
                }
              };
            }
          }
        })
        .on("select2:select", e => {
          this.update("contactPersons", $(e.target).val());
        })
        .on("select2:unselect", e => {
          this.update("contactPersons", $(e.target).val());
        });
    },
    companySelect2() {
      $(this.$el)
        .find("#company")
        .select2({
          language: "ru",
          placeholder: "Выберите компанию",
          // templateSelection: this.formatStateLink,
          ajax: {
            url: "/client/json",
            data: function(params) {
              var query = {
                search: params.term,
                page: params.page || 1
              };

              // Query parameters will be ?search=[term]&page=[page]
              return query;
            },
            processResults: function(data, params) {
              params.page = params.page || 1;
              // Tranforms the top-level key of the response object from 'items' to 'results'
              return {
                results: $.map(data.data, function(item) {
                  if (item.typeId === 1) {
                    return {
                      id: item.id,
                      text: item.displayName
                    };
                  }
                }),
                pagination: {
                  more: params.page * 10 < data.total
                }
              };
            }
          }
        })
        .on("select2:change", e => {
          //update data in parent component
          this.update("companyId", $(e.target).val());
          this.update("companyName", $(e.target).text());
        })
        .on("select2:unselect", e => {
          //update data in parent component
          this.update("companyId", $(e.target).val());
          this.update("companyName", $(e.target).text());
        });
    },
    responsiblesSelect2() {
      $(this.$el)
        .find("#responsibles")
        .select2({ language: "ru", placeholder: "Выберите ответственного" })
        .on("select2:select", e => {
          //update data in parent component
          this.update("responsibles", $(e.target).val());
        })
        .on("select2:unselect", e => {
          //update data in parent component
          this.update("responsibles", $(e.target).val());
        });
    },
    //Check if responsible selected
    isResponsibleSelected(id) {      
      let responsible = this.data.responsibles.filter(function(userId) {
        return id == userId;
      });
      return responsible.length;
    },
    //Check if contact person selected
    isContactPersonSelected(id) {
      let contactPerson = this.data.contactPersons.filter(function(userId) {
        return id == userId;
      });
      // console.log(id, contactPerson);
      return id && contactPerson.length;
    },
    //Check if company selected
    isCompanySelected(id) {
      return this.companyId === id;
    },
    //Select2 format state as link
    formatStateLink(state) {
      if (!state.id) {
        return state.text;
      }
      var $state = $(
        "<span><a href='/client/" +
          state.id +
          "/edit' target='_blank'>" +
          state.text +
          "</a></span>"
      );
      return $state;
    },
    getCompanyName(id) {
      let company = this.companyList.data.filter(function(company) {
        return company.id == id;
      });
      return company[0].displayName;
    },
    loadClient(id) {
      if (!id) {
        this.data.name = "";
        this.data.middle_name = "";
        this.data.surname = "";
        this.data.type = 1;
        this.data.description = "";
        this.data.phones = [{ id: 0, phone: "", default: true }];
        this.data.emails = [{ id: 0, email: "", default: true }];
        this.data.position = "";
        this.data.companyName = "";
        this.data.companyId = 0;
        this.data.contactPersons = [];
        this.contactPersonList = [];
        // this.data.users = [];
        this.data.responsibles = [];
        //set current user as first responsible
        if (window.laravel && window.laravel.user) {
          this.data.responsibles.push(window.laravel.user.id);
        }        
        this.$nextTick(function() {
          //Tooltop
          $('[data-toggle="tooltip"]').tooltip();
          //Fix for company
          this.companySelect2();
          this.responsiblesSelect2();
        });
      }else {
        window.axios
          .get(`/client/${id}/json`)
          .then(response => {
            //Show Client component
            this.showForm = true;
  
            if (this.data.users.length) {
              response.data.users = this.data.users;
            }
  
            this.data.name = response.data.name;
            this.data.middle_name = response.data.middle_name;
            this.data.surname = response.data.surname;
            this.data.type = response.data.type_value_relation.client_type_id;
            this.data.description =
              response.data.description_relation.description;
            this.data.phones = response.data.phone_relation;
            this.data.emails = response.data.email_relation;
  
            if (response.data.responsible_relation) {
              this.data.responsibles = response.data.responsible_relation.map( el => {
                  return el.user_id;
                }
              );
            }
            this.data.position = response.data.position_relation.position;
  
            if (
              response.data.company_relation &&
              response.data.company_relation.client_relation
            ) {
              this.data.companyId =
                response.data.company_relation.client_relation.id;
              this.data.companyName =
                response.data.company_relation.client_relation.name;
            }
  
            this.data.contactPersons = response.data.contact_person_relation.map(
              el => {
                if (el.client_relation) {
                  return el.client_relation.id;
                }
                return 0;
              }
            );
  
            this.data.contactPersonList = response.data.contact_person_relation.map(
              el => {
                if (el.client_relation) {
                  return {
                    id: el.client_relation.id,
                    displayName: el.client_relation.displayName
                  };
                }
                return {
                  id: 0,
                  displayName: ""
                };
              }
            );
  
            this.$nextTick(function() {
              //Tooltop
              $('[data-toggle="tooltip"]').tooltip();
              //Fix for company
              this.companySelect2();
              this.responsiblesSelect2();
            });
          })
          .catch(error => {
            window.ajaxError(error);
          });
      }
    },
    showModal(id) {
      $("#modal-client").modal("show");
      this.showForm = true;
      this.data.id = id;
      this.loadClient(id);
    },
    hideModal() {
      $("#modal-client").modal("hide");
    }
  }
};
</script>

<style>
#modal-client .nav-tabs-custom {
  margin-bottom: 0;
  background: transparent;
  box-shadow: none;
  border-radius: 0;
}
#modal-client .select2 .select2-selection--multiple a {
  color: #ffffff;
  text-decoration: underline;
}
</style>