<template>
  <div id="employee" class="box box-primary">
    <div class="box-header with-border">
      <h3 class="box-title">{{pageName}} <small class="badge pull-right bg-orange" v-if="trashed">Заблокирован</small></h3>
    </div>
    
    <form role="form" @submit.prevent="checkForm">
      <div class="box-body">
        <div class="col-md-10">
          <div class="form-group" :class="[errors.surname ? 'has-error' : '']">
            <label for="name">Фамилия</label> 
            <input type="text" class="form-control" id="surname" placeholder="Фамилия" 
                    :value="surname" @input="update('surname', $event.target.value)" autocomplete="off" :disabled="trashed">
          </div>
          
          <div class="form-group" :class="[errors.name ? 'has-error' : '']">
            <label for="name">Имя</label>
            <input type="text" class="form-control" id="name" placeholder="Имя" 
                    :value="name" @input="update('name', $event.target.value)" autocomplete="off" :disabled="trashed">
          </div>
          
          <div class="form-group" :class="[errors.middleName ? 'has-error' : '']">
            <label for="name">Отчество</label>
            <input type="text" class="form-control" id="middleName" placeholder="Отчество" 
                    :value="middleName" @input="update('middleName', $event.target.value)" autocomplete="off" :disabled="trashed">
          </div>

          <div class="form-group" :class="[errors.email ? 'has-error' : '']">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" placeholder="Email сотрудника" 
                    :value="email" @input="update('email', $event.target.value)" autocomplete="off" :disabled="trashed">
          </div>

          <div class="form-group">
            <label for="phone">Телефон</label>
            <div class="input-group"><div class="input-group-addon"><i class="fa fa-phone"></i></div>
              <input type="text" class="form-control" id="phone" placeholder="Телефон сотрудника" 
                      :value="phone" @input="update('phone', $event.target.value)" autocomplete="off" :disabled="trashed">
            </div>
          </div>

          <div class="form-group">
            <label for="position">Должность</label>
            <input type="text" class="form-control" id="position" placeholder="Должность сотрудника" 
                  :value="position" @input="update('position', $event.target.value)" autocomplete="off" :disabled="trashed">
          </div>

          <div class="form-group">
            <label for="signature">Подпись в коммерческом предложении</label>
            <textarea class="form-control" id="signature" placeholder="Подпись..." 
                      :value="signature" rows="6" @input="update('signature', $event.target.value)" :disabled="trashed"></textarea>
          </div>
        </div>
        <div class="col-md-2">
          <div class="form-group">
            <label>Фото сотрудника</label>
            <img id="avatar" :src="employeeFileSrc" :style="{border:'1px solid #d2d6de', width: '100%'}" @click="changeFile" :class="[!trashed?'':'disabled']">
          </div>
        </div>
      </div>
      <!-- /.box-body -->
      <div class="box-footer">
        <button type="submit" class="btn btn-primary" :disabled="trashed">Сохранить</button>
        <div class="pull-right">
          <button type="button" class="btn btn-default" v-if="id" :disabled="trashed" @click="modalEmail">
            <i class="fa fa-envelope"></i> Привязать почту
            <span class="badge bg-blue" v-if="smtpEmails.length">{{smtpEmails.length}}</span>
          </button>
          <button type="button" class="btn btn-default" 
                  v-if="id" @click="$parent.$emit('change-password')" :disabled="trashed">Изменить пароль</button>
          <button type="button" class="btn" :class="[!trashed?'btn-warning':'btn-success']" 
                  v-if="showBlockButton" @click="$parent.$emit(!trashed?'block':'unBlock')">{{!trashed?'Заблокировать':'Разблокировать'}}</button>
        </div>
      </div>
    </form>
    <!-- File manager component -->
    <div class="modal fade modal-default in file-select" :class="{'show': showModalFileManager}"> 
      <div class="modal-dialog" :style="{width: '90%'}">
          <div class="modal-content">
            <div class="modal-header">
                <span class="pull-right close-modal cursor-pointer" @click="closeModal">
                    <i class="fa fa-close"></i>
                </span>
                <h4 class="modal-title">Выбор фотографии сотрудника</h4>
            </div>
            <div class="modal-body file-manager-modal">
              <file-manager ref="fileManager"></file-manager>
            </div>
          </div>
      </div>
    </div>
    <!-- Modal email -->
    <employee-smtp ref="employeeStmp" v-bind:smtp-emails="smtpEmails" v-bind:user-id="id"></employee-smtp>
  </div>
</template>

<script>
import FileManager from "./FileManager.vue";
import EmployeeSmtp from "./EmployeeSmtp.vue";

export default {
  components: { "file-manager": FileManager, 'employee-smtp': EmployeeSmtp },
  props: {
    pageName: { default: "" },
    id: { default: 0 },
    surname: { default: "" },
    name: { default: "" },
    middleName: { default: "" },
    email: { default: "" },
    phone: { default: "" },
    position: { default: "" },
    signature: { default: "" },
    fileId: { default: 0 },
    fileSrc: { default: "/storage/resource/no-avatar.png" },
    roles: {},
    trashed: false,
    smtpEmails: {}
  },
  data() {
    return {
      employeeFileSrc: this.fileSrc, //init
      errors: { name: false, email: false, surname: false },
      showModalFileManager: false
    };
  },
  watch: {
    //Using for initialize from parent
    fileSrc: function() {
      this.employeeFileSrc = this.fileSrc;
    }
  },
  computed: {
    showBlockButton() {
      //If authorized user edit his profile
      if (window.laravel.user.id === this.id) {
        return false;
      }
      //Check employee roles
      if (this.roles && this.roles.indexOf("user") === -1) {
        return true;
      }

      return false;
    }
  },
  created() {
    //Add DaData js lib to component
    let script = document.createElement("script");
    script.setAttribute(
      "src",
      "/plugins/dadata/jquery.suggestions.min.js"
    );
    document.head.appendChild(script);
  },
  mounted() {
    //Event when double click on image
    this.$refs.fileManager.$on("dblclick-image", data => {
      //Update src for this component
      this.employeeFileSrc = data.src;
      //generate event to parent
      this.update("fileSrc", data.src);
      this.update("fileId", data.id);

      //close modal
      this.closeModal();
    });
    //When cropper finished
    this.$refs.fileManager.$on("cropper-finished", data => {
      //Update src for this component
      this.employeeFileSrc = data.fileSrc;
      //generate event to parent
      this.update("fileSrc", data.fileSrc);
      this.update("fileId", data.fileId);
    });

    //initi DaData
    //timeout fix for DaData !
    setTimeout(() => {
      this.dadata();
    }, 500);

  },
  methods: {
    //Check form for errors
    checkForm: function() {
      if (!this.surname) this.errors.surname = true;
      if (!this.name) this.errors.name = true;
      if (!this.email) this.errors.email = true;

      if (this.name && this.surname && this.email) {
        //Notify parent element
        this.$parent.$emit("employee-submit");
      }
    },
    removeError: function(error) {
      if (this.errors[error]) {
        this.errors[error] = false;
      }
    },
    //Update src for image
    changeFile() {
      //Show modal
      this.showModalFileManager = true;

      let path = this.$refs.fileManager.path;

      if (!/Сотрудники/.test(path)) {
        path += "/Сотрудники";
      }

      this.$refs.fileManager.show(path);
    },
    //
    update(input, value) {
      //Notify parent element
      this.$parent.$emit("update", { field: input, value: value });
      //Clear errors
      this.removeError(input);
    },
    //
    dadata() {
      let _this = this,
        self = {},
        $surname = $("#surname"),
        $name = $("#name"),
        $patronymic = $("#middleName");

      self.$surname = $surname;
      self.$name = $name;
      self.$patronymic = $patronymic;

      let fioParts = ["SURNAME", "NAME", "PATRONYMIC"];
      try {
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
      } catch (e) {
        console.error(e);
      }
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
    //Close popup modal wtih file manager
    closeModal() {
      this.showModalFileManager = false;
    },
    // SMTP section
    modalEmail() {
      this.$refs.employeeStmp.showModal();
    },
    
  }
};
</script>

<style src="../../../../public/plugins/dadata/suggestions.min.css"></style>
<style>
.file-manager-modal {
  height: 680px;
  overflow: auto;
}
.cursor-pointer {
  cursor: pointer;
}
</style>
