<template>
<div>
  <div class="box" @click="clearSelectedItemBody" :class="{disabled: disabledApp}">
		<div class="box-header with-border">
			<div class="btn-group-actions">
				<button type="button" class="btn btn-default" title="На уровень выше"
				        @click.prevent="levelUp">
					<i class="fa fa-level-up"></i>
				</button>
        <template v-if="can('edit file-manager')">
            <button type="button" class="btn btn-default" title="Создать папку"
                    @click.prevent="createFolder">
                <i class="fa fa-plus"></i>
                <i class="fa fa-folder"></i>
            </button>
            <button type="button" class="btn btn-default" title="Добавить изоборажение(я)"
                    @click.prevent="openFileUploader">
                <i class="fa fa-plus"></i>
                <i class="fa fa-image"></i>
            </button>
            <button type="button" class="btn btn-default" title="Файлы которые необходимо обработь через редактор"
              v-if="filesCropper.length"
              @click="showCropEditor">
              <i class="fa fa-image"></i>
              Запустить редактор: <b>{{filesCropper.length}}</b>
            </button>
            <form>
                <input type="file" id="files" name="file" accept="image/*" multiple="" @change="uploadFiles">
            </form>
        </template>
			</div>
			<div class="pull-right">
				<ol class="breadcrumb">
					<template>
						<li v-for="(crumb, index) in breadcrumb" :key="index"
						    :class="isLastBreadcrumb(index)?'active':''">
							<i class="fa fa-home" v-if="index === 0"></i>
							<a href="#" v-if="!isLastBreadcrumb(index)" @click.prevent="show(crumb.path)">
								{{crumb.name}}
							</a>
							<span v-else>{{crumb.name}}</span>
						</li>
					</template>
				</ol>
			</div>
		</div>
		<div class="box-body" @click.prevent="closeMenu" @contextmenu.prevent="openMenu">
			<template>
				<div class="row">
					<!-- Folder -->
					<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"
					     v-for="(file, index) in files"
					     v-if="file.type === 'folder'"
					     :key="index">
						<div class="info-box"
						     @contextmenu="setSelectedItem(index, $event, file)"
						     @dblclick.prevent="show(file.path)"
						     @click.prevent="setSelectedItem(index, $event, file, true)"
						     :class="[isSelectedItem(index)?selectedItemClass:'']"
						     data-type="folder">
                    <span class="info-box-icon" :class="folderClassObject(file.name)">
                        <i class="fa fa-folder"></i>
                    </span>

							<div class="info-box-content">
								<span class="info-box-text">{{file.name}}</span>
								<span class="info-box-number">{{file.countFiles}}</span>
							</div>
							<!-- /.info-box-content -->
						</div>
					</div>
				</div>
				<div class="row">
					<!-- Image -->
					<div class="col-lg-2 col-md-4 col-sm-6 col-xs-12"
					     v-for="(file, index) in files"
					     v-if="file.type === 'image'"
					     :key="index">
						<div class="box box-success"
						     @contextmenu="setSelectedItem(index, $event, file)"
						     @click.prevent="setSelectedItem(index, $event, file, true)"
                 @dblclick.prevent="dblClickImage(file)"
						     @click.ctrl.prevent="setSelectedItem(index, $event, file)"
						     :class="[imageClassObject(file.folder), isSelectedItem(index)?selectedItemClass:'']"
						     data-type="image">
							<div class="box-header">
								<h3 class="box-title">{{file.name}}</h3>
								<!-- /.box-tools -->
							</div>
							<!-- /.box-header -->
							<div class="box-body">
								<img :src="file.src">
							</div>
							<!-- /.box-body -->
						</div>
					</div>
				</div>
			</template>
			<template v-if="can('edit file-manager')">
				<ul id="right-click-menu" tabindex="-1"
				    ref="right" v-show="viewMenu" @blur="closeMenu"
				    :style="{top:top, left:left}">
					<li v-if="showMenuItem()" @click.prevent="cutItemMenu" :class="itemEnableMenuClass">
						<i class="fa fa-cut"></i> Вырезать
					</li>
					<li v-if="showMenuItem()" @click.prevent="copyItemMenu" :class="itemEnableMenuClass">
						<i class="fa fa-copy"></i> Скопировать
					</li>
					<li v-if="showMenuItem()" @click.prevent="pasteItemMenu" :class="pasteMenuClass">
						<i class="fa fa-paste"></i>
						<span>Вставить</span>
						<span v-if="countItemsInBufferMenu()" class="label label-primary">{{ countItemsInBufferMenu() }}</span>
					</li>
					<li v-if="selectedItems.length === 1" @click.prevent="renameItemMenu"
					    :class="itemEnableMenuClass">
						<i class="fa fa-pencil"></i> Переименовать
					</li>
					<li @click.prevent="deleteItemMenu" :class="itemEnableMenuClass">
						<i class="fa fa-trash"></i> Удалить
					</li>
				</ul>
			</template>
		</div>
	</div>
  <template v-if="showCropper">
			<div class="modal fade modal-default in" id="cropper">
				<div class="modal-dialog" :class="{disabled: disabledCropper}">
					<div class="modal-content">
						<div class="modal-header">
              <div class="row">
                <div class="col-xs-12">
                  <h4 class="modal-title cropper-img-counter">Редактор
                    <span>{{cropperActiveIndex}}</span>/{{filesCropper.length}}
                  </h4>
                </div>
                <div class="col-xs-12">
                  <div class="row">
                    <!-- Small screen -->
                    <div class="col-xs-4 col-lg-7">
                       <div class="btn-group hidden-lg">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">Обрезка <i class="fa fa-caret-down"></i></button>
                        <ul class="dropdown-menu" role="menu">
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===1}" data-template="1" @click="changeCropperTemplate(1)">Логотип</a></li>
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===2}" data-template="2" @click="changeCropperTemplate(2)">Обложка</a></li>
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===3}" data-template="3" @click="changeCropperTemplate(3)">Фото Товара</a></li>
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===4}" data-template="4" @click="changeCropperTemplate(4)">Фото Сотрудника</a></li>
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===5}" data-template="5" @click="changeCropperTemplate(5)">Фото Галереи</a></li>
                          <li><a href="#" class="cropper-template" :class="{active: cropperTemplate===6}" data-template="6" @click="changeCropperTemplate(6)">Без изменений</a></li>
                        </ul>
                      </div>
                      <div class="btn-group hidden-xs hidden-sm hidden-md">
                        <button class="btn btn-default btn-sm cropper-template"
                                :class="{active: cropperTemplate===1}"
                                data-template="1"
                                @click="changeCropperTemplate(1)">Логотип
                        </button>
                        <button class="btn btn-default btn-sm cropper-template"
                                data-template="2"
                                :class="{active: cropperTemplate===2}"
                                @click="changeCropperTemplate(2)">Обложка
                        </button>
                        <button class="btn btn-default btn-sm cropper-template"
                                data-template="3"
                                :class="{active: cropperTemplate===3}"
                                @click="changeCropperTemplate(3)">Фото Товара
                        </button>
                        <button class="btn btn-default btn-sm cropper-template"
                                data-template="4"
                                :class="{active: cropperTemplate===4}"
                                @click="changeCropperTemplate(4)">Фото Сотрудника
                        </button>
                        <button class="btn btn-default btn-sm cropper-template"
                                data-template="5"
                                :class="{active: cropperTemplate===5}"
                                @click="changeCropperTemplate(5)">Фото Галереи
                        </button>
                        <button class="btn btn-default btn-sm cropper-template"
                                data-template="6"
                                :class="{active: cropperTemplate===6}"
                                @click="changeCropperTemplate(6)">Без изменений
                        </button>
                      </div>
                    </div>
                    <div class="col-xs-4 col-lg-3">
                      <!-- Small screen -->
                      <div class="btn-group-vertical hidden-sm hidden-md hidden-lg">
                        <button class="btn btn-default btn-sm" title="Загрузка файла в папку согласно формату обрезки" 
                            :class="{active: !uploadingPath}"
                            @click="changeUploadPath(0)">По умолчанию</button>
                        <button class="btn btn-default btn-sm" title="Загрузка файла в текущую папку"
                            :class="{active: uploadingPath}"
                            @click="changeUploadPath(1)">В папку ({{breadcrumb[breadcrumb.length-1].name}})</button>
                      </div>
                      <div class="btn-group hidden-xs">
                        <button class="btn btn-default btn-sm" title="Загрузка файла в папку согласно формату обрезки" 
                            :class="{active: !uploadingPath}"
                            @click="changeUploadPath(0)">По умолчанию</button>
                        <button class="btn btn-default btn-sm" title="Загрузка файла в текущую папку"
                            :class="{active: uploadingPath}"
                            @click="changeUploadPath(1)">В папку ({{breadcrumb[breadcrumb.length-1].name}})</button>
                      </div>
                    </div>
                    <div class="col-xs-4 col-lg-2">
                      <div class="pull-right">
                        <button type="button" class="btn btn-default btn-sm col-xs-12 col-lg-6" @click="cancelCrop" data-toggle="tooltip" title="При отмене файл будет удален">Отменить</button>
                        <button type="button" class="btn btn-primary btn-sm col-xs-12 col-lg-6" @click="cropperCrop">Сохранить</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
						</div>
						<div class="modal-body">
							<div v-for="(file, index) in filesCropper" class="cropper-container-img"
                                :key="index"
                                :class="{active: cropperActiveIndex===index+1, 'cropper-round': cropperTemplate===4}"
                                :data-index="index+1"
                                data-template="0">
								<div class="cropper-name">
									<label :for="'cropper-name-'+(index+1)">Введите название изображения</label>
									<input class="form-control" :id="'cropper-name-'+(index+1)" type="text"
									       :value="file.name">
								</div>
								<div class="cropper-img">
									<img class="crop"
									     :src="file.src"
									     :data-file="file.file">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</template>
  </div>
</template>
<script>
  import 'cropperjs/dist/cropper.css';
  import Cropper from 'cropperjs';
export default {
  data: function() {
    return {
      files: {},
      data: {},
      path: "",
      uploadingPath: "",
      breadcrumb: {},
      selectedItemClass: "",
      selectedIndex: [], //index of the file or folder
      selectedItems: [], //files and folders
      disabledApp: false, //control #app enabled

      //Cropper
      showCropper: false,
      filesCropper: [],
      cropperContainerImg: 0,
      cropperTemplate: 1,
      cropperActiveIndex: 1,
      disabledCropper: false, //control #cropper enabled

      viewMenu: false,
      top: "0px",
      left: "0px",
      pasteCutItemsMenu: [], //cut files
      pasteCopiedItemsMenu: [], //copied files
      menuType: ""
    };
  },
  created() {
    //Add croper js lib to component
    // let recaptchaScript = document.createElement("script");
    // recaptchaScript.setAttribute("src", "/plugins/cropperjs/cropper.js");
    // document.head.appendChild(recaptchaScript);
  },
  mounted() {
    this.fetchStorageData();
  },
  methods: {
    //Get data from server
    fetchStorageData: function(path) {
      axios
        .get("/file-manager/json")
        .then(response => {
          let data = response.data;

          this.data = data.storageData;
          this.path = data.path;
          this.filesCropper = data.needCropp;

          //breadcrumb
          this.updateBreadcrumb();

          this.show(!path ? this.path : path);
        })
        .catch(function(error) {
          ajaxError(error);
        });
    },
    //Filter elements by folder
    show: function(path) {
      this.path = path;
      this.updateBreadcrumb();

      let fixedFolder = [
        "Галерея",
        "Товары",
        "Логотипы",
        "Обложки",
        "Сотрудники"
      ];

      this.files = this.data.filter(function(file) {
        return file.folder === path;
      });

      //Sort files with rules
      this.files.sort(function(a, b) {
        //Exclude system folders
        if (
          fixedFolder.indexOf(a.name) === -1 ||
          fixedFolder.indexOf(b.name) === -1
        ) {
          if (a.name > b.name) return 1;
          if (a.name < b.name) return -1;
          return 0;
        }
      });

      //clear selected
      this.clearSelectedItem();

      //Clear selected text on the page
      if (window.getSelection) {
        if (window.getSelection().empty) {
          // Chrome
          window.getSelection().empty();
        } else if (window.getSelection().removeAllRanges) {
          // Firefox
          window.getSelection().removeAllRanges();
        }
      } else if (document.selection) {
        // IE?
        document.selection.empty();
      }
    },
    //Go to parent folder
    levelUp: function() {
      if (this.path) {
        let path = this.getParentFolder(this.path);
        if (path && path !== this.path) {
          //update path for upload
          this.path = path;
          //show folder
          this.show(path);
        }
      }
    },
    //Get path folder from path
    getParentFolder: function(path) {
      let pathArr = path.split("/");
      //remove last
      pathArr.pop();

      if (pathArr.length >= 2) {
        return pathArr.join("/");
      }
      return "";
    },
    updateBreadcrumb: function() {
      //breadcrumb
      let pathArr = this.path.split("/"),
        itemPath = pathArr[0] + "/" + pathArr[1];
      //remove first 2
      pathArr = pathArr.slice(2);
      //add home label
      pathArr.unshift("Дом");

      let data = [];
      for (let i in pathArr) {
        if (parseInt(i) !== 0) {
          //update item path
          itemPath += "/" + pathArr[i];
        }

        data.push({
          path: itemPath,
          name: pathArr[i]
        });
      }

      this.breadcrumb = data;
    },
    isLastBreadcrumb: function(index) {
      return index === this.breadcrumb.length - 1;
    },
    //Create folder
    createFolder: function() {
      modal({
        okTitle: "Создать",
        modalTitle: "Создание папки",
        modalMessage:
          '<div class="form-group"> ' +
          '<label for="folder-name" class="control-label">Введите название папки:</label> ' +
          '<input class="form-control" id="folder-name"> </div>',
        onOk: $modal => {
          let $input = $modal.find("#folder-name"),
            folderName = $input.val();

          if (folderName) {
            axios
              .post("/file-manager/folder", {
                path: this.path,
                folder: folderName
              })
              .then(response => {
                this.data.push({
                  type: "folder",
                  folder: this.path,
                  name: folderName,
                  path: this.path + "/" + folderName,
                  countFiles: 0
                });
                //refresh view
                this.show(this.path);

                //hide modal
                $modal.modal("hide");
              })
              .catch(error => {
                ajaxError(error);
              });
          } else {
            $input.parent().addClass("has-error");
          }
        }
      });
    },
    //Delete file
    deleteFile: function(file) {
      modal({
        okTitle: "Удалить",
        okClass: "btn-danger",
        modalTitle: "Удаление файла",
        modalMessage: "<h4>Вы уверены что хотите удалить файл?</h4>",
        onOk: $modal => {
          axios
            .delete("/file-manager/file", {
              data: { file: file, folder: this.path }
            })
            .then(response => {
              //Remove images from this.data
              for (let i = this.data.length - 1; i >= 0; i -= 1) {
                let item = this.data[i];
                if (item.type === "image") {
                  //find item by file
                  if (file.indexOf(item.file) !== -1) {
                    //remove item
                    this.data.splice(i, 1);
                  }
                }
                //If last file
                if (i === 0) {
                  //Refresh view
                  this.show(this.path);
                }
              }
              //Update count files for current folder
              //Find parent folder
              let folder = this.data.filter(item => {
                return item.path === this.path;
              });
              //Update count file in parent folder
              if (folder.length) {
                folder[0].countFiles -= file.length;
              }
            })
            .catch(error => {
              ajaxError(error);
            });
          //Hide modal
          $modal.modal("hide");
        }
      });
    },
    //Delete folder
    deleteFolder: function(folder) {
      modal({
        okTitle: "Удалить",
        okClass: "btn-danger",
        modalTitle: "Удаление папки",
        modalMessage:
          "<h4>Вы уверены что хотите удалить папку со всем содержимым?</h4>",
        onOk: $modal => {
          axios
            .delete("/file-manager/folder", { data: { path: folder } })
            .then(response => {
              this.fetchStorageData(this.path);
            })
            .catch(error => {
              ajaxError(error);
            });
          //hide modal
          $modal.modal("hide");
        }
      });
    },
    //Class for folder
    folderClassObject: function(folder) {
      let cls = "bg-teal";
      switch (folder) {
        case "Логотипы":
          cls = "bg-purple";
          break;
        case "Обложки":
          cls = "bg-red";
          break;
        case "Товары":
          cls = "bg-green";
          break;
        case "Сотрудники":
          cls = "bg-yellow";
          break;
        case "Галерея":
          cls = "bg-aqua";
          break;
      }
      return cls;
    },
    //Class for folder
    imageClassObject: function(folder) {
      let cls = "box-teal";
      if (folder) {
        switch (folder.split("/").pop()) {
          case "Логотипы":
            cls = "box-purple";
            break;
          case "Обложки":
            cls = "box-red";
            break;
          case "Товары":
            cls = "box-green";
            break;
          case "Сотрудников":
            cls = "box-yellow";
            break;
          case "Галерея":
            cls = "box-aqua";
            break;
        }
      }
      return cls;
    },
    //Add class to selected item
    setSelectedItem: function(index, e, item, closeMenu) {
      let classes = "",
        type = item.type;

      if (closeMenu) {
        this.closeMenu();
      }
      //control duplicates
      if (this.isSelectedItem(index)) {
        return;
      }
      //if try select multiple folders
      if (e.ctrlKey && item.type === "folder") {
        return;
      }

      //clear items if ctrl doesn't click
      if (!e.ctrlKey) {
        this.selectedItems = [];
      }
      this.selectedItems.push(item);

      if (type === "folder") {
        $(e.target)
          .closest(".info-box")
          .children(".info-box-icon")
          .attr("class")
          .split(" ")
          .forEach(cls => {
            if (/^bg-\S+$/.test(cls)) {
              classes = cls;
            }
          });
      }
      if (type === "image") {
        $(e.target)
          .closest(".box")
          .attr("class")
          .split(" ")
          .forEach(cls => {
            if (/^box-\S+$/.test(cls)) {
              classes = cls.replace("box-", "bg-");
            }
          });
      }
      //set current bg class
      this.selectedItemClass = classes;
      //set menu type
      this.menuType = type;

      //clear items if ctrl doesn't click
      if (!e.ctrlKey) {
        this.selectedIndex = [];
      }
      this.selectedIndex.push(index);
    },
    //Check by index selected item
    isSelectedItem: function(index) {
      return this.selectedIndex.indexOf(index) !== -1;
    },
    //Clear selected item. Using after change current directory
    clearSelectedItem: function() {
      this.selectedIndex = [];
      this.selectedItemClass = "";
      this.selectedItems = [];
    },
    //Clear selected item from clicked body
    clearSelectedItemBody: function(e) {
      //clear selection if clicked not on the image
      if (
        !e.ctrlKey &&
        !$(e.target).closest('[data-type="image"]').length &&
        !$(e.target).closest('[data-type="folder"]').length
      ) {
        this.clearSelectedItem();
      }
    },
    //Update files count in folders
    updateFoldersCounter: function() {
      if (this.data.length) {
        this.data.forEach(item => {
          if (item.type === "folder") {
            let files = this.data.filter(function(it) {
              return it.type === "image" && it.folder.match(item.path);
            });

            item.countFiles = files.length;
          }
        });
      }
    },
    //Show file upload dialogue
    openFileUploader: function() {
      $("#files").click();
    },
    //Upload files to server
    uploadFiles: function(e) {
      let files = e.dataTransfer ? e.dataTransfer.files : e.target.files;

      if (files.length) {
        //Disable app
        this.disabledApp = true;

        const body = new FormData();

        for (let i = 0; i < files.length; i++) {
          body.append("files[]", files[i]);
        }

        body.append("path", this.path);

        //clear file input
        e.target.value = "";

        axios
          .post("/file-manager/upload", body)
          .then(response => {
            if (response.data.files) {
              //Set files for cropper
              response.data.files.forEach(file => {
                this.filesCropper.push(file);
              });

              //Show editor
              this.showCropEditor();
            }

            //Enable app
            this.disabledApp = false;
          })
          .catch(error => {
            //Enable app
            this.disabledApp = false;

            ajaxError(error);
          });
      }
    },
    //Crop images
    showCropEditor: function() {
      this.showCropper = true;

      setTimeout(() => {
        //Tooltop
        $('[data-toggle="tooltip"]').tooltip();

        let $cropper = $("#cropper");

        $cropper.find("img.crop").each(function() {
          $(this).data({
            cropper: new Cropper($(this).get(0), {
              movable: false,
              rotatable: false,
              scalable: false
              // zoomable: false,
              // zoomOnTouch: false,
              // zoomOnWheel: false,
              // aspectRatio: 17 / 4
            })
          });
        });
        this.changeCropperTemplate(this.cropperTemplate);
        //set cropper height
        this.cropperContainerImg = $cropper
          .find(".cropper-container-img:first-child")
          .height();
      }, 100);
    },

    //Go to prev image
    cropperPrev: function() {
      let $cropper = $("#cropper"),
        $currentItem = $cropper.find(".cropper-container-img.active"),
        $firstItem = $cropper.find(".cropper-container-img:first-child"),
        currentIndex = $currentItem.data("index"),
        nextIndex = currentIndex - 1,
        $nextItem = $cropper.find(
          '.cropper-container-img[data-index="' + nextIndex + '"]'
        ),
        template = $nextItem.data("template");

      if ($nextItem.length) {
        //Update current active index
        //update current number of the editable image
        this.cropperActiveIndex = nextIndex;

        //show active image
        let currentMargin = parseInt(
            $firstItem.css("margin-top").replace("px", "")
          ),
          margin = currentMargin + this.cropperContainerImg;

        $firstItem.css("margin-top", margin + "px");

        //Update current template
        setTimeout(() => {
          this.changeCropperTemplate(
            template ? template : this.cropperTemplate
          );
        }, 100);
      }
    },
    //
    cropperNext: function() {
      let $cropper = $("#cropper"),
        $currentItem = $cropper.find(".cropper-container-img.active"),
        currentIndex = $currentItem.data("index"),
        nextIndex = currentIndex + 1,
        $nextItem = $cropper.find(
          '.cropper-container-img[data-index="' + nextIndex + '"]'
        ),
        template = $nextItem.data("template");

      if ($nextItem.length) {
        //Update current active index
        //update current number of the editable image
        this.cropperActiveIndex = nextIndex;

        //show active image
        let margin = this.cropperContainerImg * -1 * currentIndex;
        $cropper
          .find(".cropper-container-img:first-child")
          .css("margin-top", margin + "px");

        //Update current template
        setTimeout(() => {
          this.changeCropperTemplate(
            template ? template : this.cropperTemplate
          );
        }, 100);
      }
    },
    //Update cropper template
    changeCropperTemplate: function(template) {
      //update current template
      this.cropperTemplate = template;

      let $cropper = $("#cropper"),
        $container = $cropper.find(".cropper-container-img.active "),
        $img = $container.find("img.crop"),
        cropper = $img.data("cropper");

      if (cropper) {
        //if was reset
        if (!cropper.cropped) {
          cropper.crop();
        }

        //Change aspect ratio
        switch (template) {
          case 1: //Логотип
            cropper.setAspectRatio(17 / 4);
            break;
          case 2: //Обложка
            cropper.setAspectRatio(13 / 3);
            break;
          case 3: //Фото товара
            cropper.setAspectRatio(1);
            break;
          case 4: //Фото сотрудника
            cropper.setAspectRatio(1);
            break;
          case 5: //Фото галереи
            cropper.setAspectRatio(12 / 8);
            break;
          case 6: //Без изменений
            cropper.clear();
            break;
        }

        //save selected template
        $container.data("template", template);
      }
    },
    //Crop current image
    cropperCrop: function() {
      let $cropper = $("#cropper"),
        $container = $cropper.find(".cropper-container-img.active"),
        $img = $container.find("img.crop"),
        cropper = $container.find("img.crop").data("cropper");

      if (cropper) {
        // cropperStartAnimation();
        //Disable app
        this.disabledCropper = true;

        let croppedCanvas = cropper.getCroppedCanvas(),
          name = $container.find(".cropper-name input").val(),
          file = $img.data("file"),
          template = $container.data("template");

        //find file data in Assets
        let fileData = this.filesCropper.find(item => {
          return item.file === file;
        });

        if (fileData) {
          //for round canvas
          if ($container.hasClass("cropper-round")) {
            croppedCanvas = this.getRoundedCanvas(croppedCanvas);
          }

          // Upload cropped image to server if the browser supports `HTMLCanvasElement.toBlob`
          croppedCanvas.toBlob(blob => {
            let formData = new FormData();

            formData.append("croppedImage", blob);
            formData.append("name", name);
            formData.append("file", file);
            formData.append("template", template);
            formData.append("path", this.uploadingPath); //change default path

            axios
              .post("/file-manager/upload/cropped", formData)
              .then(response => {
                //Enable app
                this.disabledCropper = false;

                //mark image as cropped
                fileData.cropped = 1;

                //enable close button only if all images cropped
                let cropDone = true;
                this.filesCropper.forEach(item => {
                  if (item.cropped === 0) {
                    cropDone = false;
                  }
                });

                if (cropDone) {
                  //Generate event when all image cropped
                  this.$emit("cropper-finished", response.data);
                  //Save current path
                  let path = this.path;
                  //get new assets
                  this.fetchStorageData(path);
                  //Close editor
                  this.showCropper = false;
                  //Empty
                  this.filesCropper = [];
                  //Reset active index
                  this.cropperActiveIndex = 1;
                }

                //go to next
                this.cropperNext();
              })
              .catch(error => {
                //Enable app
                this.disabledCropper = false;
                ajaxError(error);
              });
          });
        } else {
          alert("Файл не найден");
        }
      }
    },
    getRoundedCanvas: function(sourceCanvas) {
      let canvas = document.createElement("canvas"),
        context = canvas.getContext("2d"),
        width = sourceCanvas.width,
        height = sourceCanvas.height;
      canvas.width = width;
      canvas.height = height;
      context.imageSmoothingEnabled = true;
      context.drawImage(sourceCanvas, 0, 0, width, height);
      context.globalCompositeOperation = "destination-in";
      context.beginPath();
      context.arc(
        width / 2,
        height / 2,
        Math.min(width, height) / 2,
        0,
        2 * Math.PI,
        true
      );
      context.fill();
      return canvas;
    },
    // --- Custom context menu --- //
    setMenu: function(top, left) {
      let $app = $("#app"),
        largestHeight = $app.innerHeight() - this.$refs.right.offsetHeight - 25,
        largestWidth = $app.innerWidth() - this.$refs.right.offsetWidth - 25;
      //Add scroll from top
      top += $(document).scrollTop();
      left -= $("aside.main-sidebar").innerWidth(); //remove left with side
      top -= 100; //remove top margin

      if (top > largestHeight) top = largestHeight;

      if (left > largestWidth) left = largestWidth;

      this.top = top + "px";
      this.left = left + "px";
    },

    closeMenu: function() {
      this.viewMenu = false;
    },

    openMenu: function(e) {
      this.viewMenu = true;

      Vue.nextTick(
        function() {
          // this.$refs.right.focus();
          // let pos = e.target.getBoundingClientRect();
          this.setMenu(e.y, e.x);
        }.bind(this)
      );
    },
    //Count cut or copied items
    countItemsInBufferMenu: function() {
      return this.pasteCopiedItemsMenu.length + this.pasteCutItemsMenu.length;
    },
    //After click in context menu
    deleteItemMenu: function() {
      if (this.selectedItems.length) {
        if (this.menuType === "folder") {
          this.deleteFolder(this.selectedItems[0].path);
        }
        if (this.menuType === "image") {
          //Get only file name
          let files = this.selectedItems.map(function(item) {
            return item.file;
          });

          this.deleteFile(files);
        }
      }
      this.closeMenu();
    },
    //After click in context menu
    cutItemMenu: function() {
      if (this.selectedItems.length) {
        //Clear previous copied files
        this.pasteCopiedItemsMenu = [];
        //Store item for paste
        this.pasteCutItemsMenu = this.selectedItems.map(function(item) {
          return item.file;
        });
      }

      this.closeMenu();
    },
    //After click in context menu
    copyItemMenu: function() {
      if (this.selectedItems.length) {
        //Clear previous cut files
        this.pasteCutItemsMenu = [];
        //Store item for paste
        this.pasteCopiedItemsMenu = this.selectedItems.map(function(item) {
          return item.file;
        });
      }

      this.closeMenu();
    },
    //After click in context menu
    pasteItemMenu: function() {
      //Paste cut items
      if (this.pasteCutItemsMenu.length) {
        axios
          .post("/file-manager/file/move", {
            file: this.pasteCutItemsMenu,
            folder: this.path
          })
          .then(response => {
            this.data.forEach(item => {
              //Find by file
              if (this.pasteCutItemsMenu.indexOf(item.file) !== -1) {
                //update path
                item.folder = this.path;
                //update src
                item.src =
                  "/" +
                  this.path.replace("public", "storage") +
                  "/" +
                  item.file;
              }
              //update files counter
              this.updateFoldersCounter();
            });

            //clear paste items
            this.pasteCutItemsMenu = [];
            //refresh view
            this.show(this.path);
          })
          .catch(error => {
            //clear paste items
            this.pasteCutItemsMenu = [];

            ajaxError(error);
          });
      }

      if (this.pasteCopiedItemsMenu.length) {
        axios
          .post("/file-manager/file/copy", {
            file: this.pasteCopiedItemsMenu,
            folder: this.path
          })
          .then(response => {
            let data = response.data;
            data.forEach(file => {
              file.type = "image";
              file.folder = this.path;
              file.src = file.path;

              this.data.push(file);
            });

            //update files counter
            this.updateFoldersCounter();

            //clear paste items
            this.pasteCopiedItemsMenu = [];
            //refresh view
            this.show(this.path);
          })
          .catch(error => {
            //clear paste items
            this.pasteCopiedItemsMenu = [];

            ajaxError(error);
          });
      }

      this.closeMenu();
    },

    renameItemMenu: function() {
      if (this.selectedItems.length) {
        let item = this.selectedItems[0],
          itemTitle = item.type === "image" ? "изображение" : "папку",
          itemLabel = item.type === "image" ? "изображения" : "папки",
          itemPath = item.path,
          itemOldName = item.name;

        modal({
          okTitle: "Подтвердить",
          modalTitle: "Переименовать " + itemTitle,
          modalMessage:
            '<div class="form-group"> ' +
            '<label for="folder-name" class="control-label">Введите название ' +
            itemLabel +
            ":</label> " +
            '<input class="form-control" id="item-name" value="' +
            itemOldName +
            '"> </div>',
          onOk: $modal => {
            let $input = $modal.find("#item-name"),
              itemName = $input.val().trim();

            if (itemName) {
              if (item.type === "folder") {
                axios
                  .post("/file-manager/folder/rename", {
                    path: itemPath,
                    folder: item.folder,
                    name: itemName
                  })
                  .then(response => {
                    this.fetchStorageData(this.path);
                    //hide modal
                    $modal.modal("hide");
                  })
                  .catch(error => {
                    ajaxError(error);
                  });
              }
              if (item.type === "image") {
                axios
                  .post("/file-manager/file/rename", {
                    file: item.file,
                    name: itemName
                  })
                  .then(response => {
                    this.data.forEach(file => {
                      if (file.file === item.file) {
                        file.name = itemName;
                      }
                    });

                    //refresh view
                    this.show(this.path);
                    //
                    // //hide modal
                    $modal.modal("hide");
                  })
                  .catch(error => {
                    ajaxError(error);
                  });
              }
            } else {
              $input.parent().addClass("has-error");
            }
          }
        });
      }
      this.closeMenu();
    },

    //When double clikc on image
    dblClickImage(file) {
      //Generate event
      this.$emit("dblclick-image", file);
    },

    //Show or hide menu item
    showMenuItem: function() {
      if (this.pasteCutItemsMenu.length) {
        return true;
      }
      if (this.pasteCopiedItemsMenu.length) {
        return true;
      }
      return this.menuType === "image";
    },

    //Change path for uploading file
    changeUploadPath: function(pathType) {
      let changedPath = "";

      if (pathType === 1) {
        changedPath = this.path;
      }

      this.uploadingPath = changedPath;
    },

    //Check permission
    can: function(permission) {
      return window.can({ permission: permission });
    },
    //Remove current file from editing
    cancelCrop: function() {
      let $cropper = $("#cropper"),
        $container = $cropper.find(".cropper-container-img.active"),
        $img = $container.find("img.crop"),
        file = $img.data("file");

      //find file data in Assets
      let fileData = this.filesCropper.find(item => {
        return item.file === file;
      });

      if (fileData) {
        //Disable app
        this.disabledCropper = true;

        axios
          .delete("/file-manager/file", {
            data: {
              file: fileData.file,
              folder: fileData.folder //src.replace("/" + file, "")
            }
          })
          .then(response => {
            //Enable app
            this.disabledCropper = false;
            //mark image as cropped
            fileData.cropped = 1;
            //enable close button only if all images cropped
            let cropDone = true;
            this.filesCropper.forEach((item, index) => {
              if (item.cropped === 0) {
                cropDone = false;
              }
            });

            if (cropDone) {
              //Generate event when all image cropped
              this.$emit("cropper-finished", response.data);
              //Save current path
              let path = this.path;
              //get new assets
              this.fetchStorageData(path);
              //Close editor
              this.showCropper = false;
              //Remove cropped files
              this.filesCropper = [];
              //Reset index
              this.cropperActiveIndex = 1;
            }
            //go to next
            this.cropperNext();
          })
          .catch(error => {
            //Enable app
            this.disabledCropper = false;
            ajaxError(error);
          });
      }
    }
  },
  computed: {
    //For context menu
    pasteMenuClass: function() {
      return {
        disabled:
          this.pasteCutItemsMenu.length === 0 &&
          this.pasteCopiedItemsMenu.length === 0
      };
    },
    itemEnableMenuClass: function() {
      return {
        disabled: !this.selectedItems.length
      };
    }
  }
};
</script>

<style lang="scss">
@import "../../sass/pages/file-manager.scss";
</style>