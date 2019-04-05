let cropperContainerImg = 635;

$(document).on('click', '#cropper-crop', function () {
    let $cropper = $('#cropper'),
        $container = $cropper.find('.cropper-container-img.active'),
        uploadPath = parseInt($cropper.find('.cropper-upload-path.cropper-active').data('path')),
        $img = $container.find('img.crop'),
        cropper = $container.find('img.crop').data('cropper'),
        editor = $cropper.data('editor'),
        am = editor.AssetManager,
        amConfig = am.getConfig(),
        path = amConfig.params.path;

    if (cropper) {
        cropperStartAnimation();

        let croppedCanvas = cropper.getCroppedCanvas(),
            name = $container.find('.cropper-name input').val(),
            file = $img.data('file'),
            template = $container.data('template');

        //find file data in Assets
        let fileData = am.getAll().find(
            asset => asset.get('file') === file
        );

        if (fileData) {
            // fileData = fileData[0];

            //for round canvas
            if ($container.hasClass('cropper-round')) {
                croppedCanvas = getRoundedCanvas(croppedCanvas);
            }

            // Upload cropped image to server if the browser supports `HTMLCanvasElement.toBlob`
            croppedCanvas.toBlob(function (blob) {
                let formData = new FormData();

                formData.append('croppedImage', blob);
                formData.append('name', name);
                formData.append('file', file);
                formData.append('template', template);
                //Change upload path
                if (uploadPath === 1) {
                    formData.append('path', path);
                }
                // console.log(uploadPath, path, template);

                axios.post('/file-manager/upload/cropped', formData)
                    .then(function (response) {
                        cropperEndAnimation();
                        let fileId = response.data.fileId;

                        //mark image as cropped
                        $container.data('cropped', true);

                        //enable close button only if all images cropped
                        let cropDone = true;
                        $cropper.find('.cropper-container-img').each(function () {
                            if (!$(this).data('cropped')) {
                                cropDone = false;
                            }
                        });

                        if (cropDone) {
                            //Clear file data 
                            $('#gjs-am-uploadFile').val('');

                            if (am) {
                                cropperStartAnimation();
                                //get new assets
                                axios.get('/editor/assets/load').then(function (response) {
                                    cropperEndAnimation();
                                    //update assets
                                    am.load({
                                        assets: response.data
                                    });

                                    //Close editor
                                    $('#cropper').remove();

                                    //Show last edited image
                                    am.render(am.getAll().filter(
                                        asset => asset.get('folder') === fileData.get('folder')
                                    ));

                                    let fdata = am.getAll().find(
                                        asset => asset.get('id') === fileId
                                    );

                                    if (fdata && fdata.view) {
                                        fdata.view.$el.trigger('dblclick');
                                    }

                                }).catch(function (error) {
                                    cropperEndAnimation();
                                    window.message({ text: error.response.data.errors, error: true });
                                });
                            }

                        }

                        //go to next
                        cropperNext();
                    })
                    .catch(function (error) {
                        cropperEndAnimation();

                        window.message({ text: error.response.data.errors, error: true });
                    });
            });
        } else {
            window.message({ text: 'Файл не найден', error: true });
        }
    }
});

$(document).on('click', '#cropper-prev', function () {
    cropperPrev();
});

$(document).on('click', '#cropper-next', function () {
    cropperNext();
});

$(document).on('click', '#cropper button.cropper-template', function () {
    let template = parseInt($(this).data('template')),
        $cropper = $('#cropper'),
        $container = $cropper.find('.cropper-container-img.active '),
        $img = $container.find('img.crop'),
        cropper = $img.data('cropper');

    //clear defaults
    $container.removeClass('cropper-round');
    $cropper.find('.cropper-template.cropper-active').removeClass('cropper-active');

    //select button
    $(this).addClass('cropper-active');

    //if was reset
    if (!cropper.cropped) {
        cropper.crop();
    }

    if (cropper) {
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
                $container.addClass('cropper-round');
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
        $container.data('template', template);
    } else {
        window.message({ text: 'Ошибка инициализации редактора фото. Обновите страницу и повторите попытку', error: true });
    }

});

//Select uploading path
$(document).on('click', '.cropper-upload-path', function () {
    $(this).parent().find('.cropper-upload-path.cropper-active').removeClass('cropper-active');
    $(this).addClass('cropper-active');
});
//Remove
$(document).on('click', '#cropper-cancel', function () {
    let $cropper = $("#cropper"),
        $container = $cropper.find(".cropper-container-img.active"),
        $img = $container.find("img.crop"),
        editor = $cropper.data('editor'),
        am = editor.AssetManager,
        file = $img.data("file");

    //find file data in Assets
    let fileData = am.getAll().find(
        asset => asset.get('file') === file
    );

    if (fileData) {
        cropperStartAnimation();

        axios
            .delete("/file-manager/file", {
                data: {
                    file: fileData.get('file'),
                    folder: fileData.get('folder') //src.replace("/" + file, "")
                }
            })
            .then(() => {
                cropperEndAnimation();
                //mark image as cropped
                $container.data('cropped', true);

                let cropDone = true;
                $cropper.find('.cropper-container-img').each(function () {
                    if (!$(this).data('cropped')) {
                        cropDone = false;
                    }
                });

                if (cropDone) {
                    //Clear file data 
                    $('#gjs-am-uploadFile').val('');

                    if (am) {
                        cropperStartAnimation();
                        //get new assets
                        axios.get('/editor/assets/load').then(function (response) {
                            cropperEndAnimation();
                            //update assets
                            am.load({
                                assets: response.data
                            });

                            //Close editor
                            $('#cropper').remove();

                        }).catch(function (error) {
                            cropperEndAnimation();
                            window.message({ text: error.response.data.errors, error: true });
                        });
                    }
                }

                //go to next
                cropperNext();
            })
            .catch(error => {
                cropperEndAnimation();

                window.message({ text: error.response.data.errors, error: true });
            });
    }
});

function cropperStartAnimation() {
    let $cropper = $('#cropper');
    $cropper.find('.cropper-mdl-header, .cropper-mdl-content').addClass('disabled');
    $cropper.append('<div class="loader"></div>');
}

function cropperEndAnimation() {
    let $cropper = $('#cropper');
    $cropper.find('.cropper-mdl-header, .cropper-mdl-content').removeClass('disabled');
    $cropper.find('.loader').remove();
}

function getRoundedCanvas(sourceCanvas) {
    let canvas = document.createElement('canvas'),
        context = canvas.getContext('2d'),
        width = sourceCanvas.width,
        height = sourceCanvas.height;
    canvas.width = width;
    canvas.height = height;
    context.imageSmoothingEnabled = true;
    context.drawImage(sourceCanvas, 0, 0, width, height);
    context.globalCompositeOperation = 'destination-in';
    context.beginPath();
    context.arc(width / 2, height / 2, Math.min(width, height) / 2, 0, 2 * Math.PI, true);
    context.fill();
    return canvas;
}

function cropperNext() {
    let $cropper = $('#cropper'),
        $cropperImgCounter = $cropper.find('.cropper-img-counter > span'),
        $currentItem = $cropper.find('.cropper-container-img.active'),
        currentIndex = $currentItem.data('index'),
        nextIndex = currentIndex + 1,
        $nextItem = $cropper.find('.cropper-container-img[data-index="' + nextIndex + '"]'),
        template = $nextItem.data('template'),
        currentTemplate = $currentItem.data('template');

    if ($nextItem.length) {
        //update current number of the editable image
        $cropperImgCounter.text(nextIndex);

        //show active image
        let margin = (cropperContainerImg * -1) * currentIndex;
        $cropper.find('.cropper-container-img:first-child').css('margin-top', margin + 'px');

        //change active
        $currentItem.removeClass('active');
        $currentItem.removeClass('cropper-round');
        $nextItem.addClass('active');

        //reset active template
        $cropper.find('.cropper-template.cropper-active').removeClass('cropper-active');
        //select active template
        $cropper.find('button.cropper-template[data-template="' + (template ? template : currentTemplate) + '"]').click();
    }
}

function cropperPrev() {
    let $cropper = $('#cropper'),
        $cropperImgCounter = $cropper.find('.cropper-img-counter > span'),
        $currentItem = $cropper.find('.cropper-container-img.active'),
        $firstItem = $cropper.find('.cropper-container-img:first-child'),
        currentIndex = $currentItem.data('index'),
        nextIndex = currentIndex - 1,
        $nextItem = $cropper.find('.cropper-container-img[data-index="' + nextIndex + '"]'),
        template = $nextItem.data('template'),
        currentTemplate = $currentItem.data('template');

    if ($nextItem.length) {

        //update current number of the editable image
        $cropperImgCounter.text(nextIndex);

        //show active image
        let currentMargin = parseInt($firstItem.css('margin-top').replace('px', '')),
            margin = currentMargin + cropperContainerImg;

        $firstItem.css('margin-top', margin + 'px');

        //change active
        $currentItem.removeClass('active');
        $currentItem.removeClass('cropper-round');
        $nextItem.addClass('active');

        //reset active template
        $cropper.find('.cropper-template.cropper-active').removeClass('cropper-active');
        //select active template
        $cropper.find('button.cropper-template[data-template="' + (template ? template : currentTemplate) + '"]').click();
    }
}