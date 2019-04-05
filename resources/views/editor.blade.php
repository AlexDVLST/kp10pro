<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>
    <!-- Theme style -->
    <link rel="stylesheet" href="{{url('/js/grapesjs/css/grapes.min.css')}}"> 
    <link rel="stylesheet" href="{{url('/css/editor.css?ver='.$templateVersion)}}">
    <link rel="stylesheet" href="{{asset('/plugins/admin-lte/dist/css/AdminLTE.min.css')}}">
    <!-- DaData -->
    <link rel="stylesheet" href="{{asset('/plugins/dadata/suggestions.min.css')}}"> 

    <link rel="apple-touch-icon" sizes="57x57" href="/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
    @auth
    <script>
    // Global variables
    window.laravel = {!! json_encode([
        'user' => [ 'id' => Auth::user()->id, 'email' => Auth::user()->email, 'permissions' => Auth::user()->getAllPermissions()->pluck('name'), 'roles' => Auth::user()->roles->pluck('name') ],
        'dadata' => [
            'apiKey' => env('DADATA_API_KEY')
        ]
    ]) !!}
    </script>
    @endauth
    <div id="gjs">
        {{-- @include('templates.base.index') --}}
    </div>
    <div id="app" class="bootstrap">
        {{-- Help block --}}
        <template>
            <transition name="fade">
                <div class="box-help" v-if="help.show">
                    <div class="box">
                        <div class="box-header">
                            {{-- <button class="btn btn-default btn-xs btn-round">Видео</button>
                            <button class="btn btn-default btn-xs btn-round">Инструменты</button> --}}

                            <div class="pull-right box-tools">
                            <button type="button" class="btn btn-sm" @click="closeHelp">
                                <i class="fa fa-times"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                                <h3>@{{help.current.name}}</h3>
                                <iframe width="100%" height="315" :src="help.current.video" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                
                                <button v-for="(video, index) in help.videos" :key="index" class="btn btn-xs btn-round" 
                                    :class="help.current.id!=video.id?'btn-default':'btn-primary'" :data-video="video.video"
                                    @click="help.current=video">@{{video.name}}</button>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
        {{-- Message template --}}
        <div class="modal fade" id="modal-message" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center"></h4>
                    </div>
                </div>
            </div>
        </div>
        {{-- Message template --}}
        <div class="modal fade" id="modal-modal" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body"></div>
                </div>
            </div>
        </div>
        {{-- Confirm template --}}
        <div class="modal fade" id="modal-confirm" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-body">
                        <h4 class="text-center"></h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-cancel" data-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-primary btn-ok">OK</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal email -->
        <employee-smtp ref="employeeStmp" v-bind:smtp-emails="[]" v-bind:user-id="employeeSmtp.userId"></employee-smtp>
        {{-- Modal send email --}}
        <div class="modal fade" id="modal-send-email" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body" :class="{'disabled': sendMail.disabled}">
                        <div>
                            <div class="form-group">
                                <label>Почта менеджера</label>
                                <select id="employee-email" class="form-control" style="width: 100%">
                                    <option v-for="(el, index) in sendMail.employeeEmails" :index="index" :value="el.id">@{{el.smtp_login}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Почта клиента</label>
                                <select id="client-email" class="form-control" style="width: 100%">
                                        <option v-for="(el, index) in sendMail.clientEmails" :index="index" :value="el.email">@{{el.email}}</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Тема</label>
                                <input id="subject" class="form-control" placeholder="Тема:" v-model="sendMail.data.subject">
                            </div>
                            <div class="form-group">
                                <div id="compose-textarea" v-html="sendMail.data.message"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" :class="{'disabled': sendMail.disabled}">
                        <div>
                            <button class="btn btn-default pull-left" data-dismiss="modal">Отмена</button>
                            <button type="submit" id="send" class="btn btn-primary" @click="sendEmailSend()"><i class="fa fa-envelope-o"></i> Отправить</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal send email --}}
        <client ref="client" v-bind:loaded="client.loaded"></client>
    </div>

    <script src="{{url('/js/grapesjs/grapes.min.js')}}"></script>
    <script src="{{url('/js/grapesjs/grapesjs-plugin-kp10-base.min.js?ver='.$templateVersion)}}"></script>
    <script src="{{url('/js/grapesjs/grapesjs-blocks-basic.min.js')}}"></script>
    <script src="{{url('/js/grapesjs/grapesjs-plugin-export.min.js')}}"></script>

    <script src="{{url('/plugins/FileSaver.min.js')}}"></script>
    <script type="text/javascript">
        let _csrf = '{{ csrf_token() }}';

    let editor = grapesjs.init({
        container: '#gjs',
        height: '100%',
//        autorender: 1,
        fromElement: true, //If true fetch HTML and CSS from selected container
        // If true render a select of available devices
        showDevices: 0,
        canvas: {
            scripts: [
                '/plugins/jquery/dist/jquery.min.js',
                '/plugins/bootstrap/dist/js/bootstrap.min.js',
                '/plugins/fancybox/jquery.fancybox.min.js',
                '/plugins/modernizr.custom.js',
                '/plugins/jquery.cbpFWSlider.min.js?ver={{$templateVersion}}',
                '/js/grapesjs/templates/base/main.min.js?ver={{$templateVersion}}',
            ],
            styles: [
                '/plugins/bootstrap/dist/css/bootstrap.min.css',
                // '//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css',
                '/plugins/font-awesome/css/font-awesome.min.css',
                '/css/icomoon.min.css',
                '/plugins/fancybox/jquery.fancybox.min.css',
                '/js/grapesjs/css/grapesjs-plugin-kp10-base.min.css?ver={{$templateVersion}}',
            ]
        },
        panels: {
            defaults: [{
                id: 'commands',
                buttons: [{}]
            }, {
                id: 'options',
                buttons: [
                    {
                        id: 'undo',
                        className: 'fa fa-undo',
                        attributes: {title: 'Отменить', 'data-tooltip-pos': 'bottom'},
                        command: function(e) { return e.runCommand('core:undo') },
                    },
                    {
                        id: 'redo',
                        className: 'fa fa-repeat',
                        attributes: {title: 'Повторить', 'data-tooltip-pos': 'bottom'},
                        command: function(e) { return e.runCommand('core:redo') },
                    },
                    @if( ( ($user->userCan('create offer') && $systemOffer)  //create new
                        || ($user->userCan('edit offer') && !$systemOffer) //edit all not system
                        || ($user->userCan('edit-own offer') && !$systemOffer && $userOfferOwner) ) && !$variantSelected ) //edit own and not system
                    {
                        id: 'floppy',
                        className: 'fa fa-floppy-o',
                        attributes: {title: 'Сохранить', 'data-tooltip-pos': 'bottom'},
                        command: 'storeData'
                    },
                    @endif
                    @if(!$systemOffer && $urlOffer) 
                    {
                        id: 'pdf',
                        className: 'fa fa-file-pdf-o',
                        attributes: {title: 'Скачать PDF', 'data-tooltip-pos': 'bottom'},
                        active: false,
                        command: 'export-pdf'
                    },
                    {
                        id: 'pdf-full',
                        className: 'fa fa-file-pdf-o',
                        attributes: {title: 'Скачать расширенный PDF', 'data-tooltip-pos': 'bottom'},
                        active: false,
                        command: 'export-pdf-full'
                    },
                    {
                        id: 'excel',
                        className: 'fa fa-file-excel-o',
                        attributes: {title: 'Скачать Excel', 'data-tooltip-pos': 'bottom'},
                        command: 'export-excel'
                    },
                    {
                        id: 'link',
                        className: 'fa fa-link',
                        attributes: {title: 'Скопировать ссылку', 'data-tooltip-pos': 'bottom'},
                        command: function(e){
                            const el = document.createElement('textarea');
                            el.value = '{{env('APP_PROTOCOL').$user->domain.'.'.env('APP_DOMAIN').'/'.$urlOffer}}';
                            document.body.appendChild(el);
                            el.select();
                            document.execCommand('copy');
                            document.body.removeChild(el);

                            message('Ссылка на коммерческое предложение скопирована');
                            
                            setTimeout(() => {
                                hideMessage();
                            }, 2000);
                        }
                    },
                    @endif
                    @if($offer->dealCardLink)
                    {
                        id: 'deal-link',
                        className: 'fa fa-external-link',
                        attributes: {title: 'Открыть в CRM', 'data-tooltip-pos': 'bottom'},
                        command: 'deal-link'
                    },
                    @endif
                    @if(!$isTemplate && !$systemOffer)
                    {
                        id: 'send-email',
                        className: 'fa fa-envelope',
                        attributes: {title: 'Отправить на почту', 'data-tooltip-pos': 'bottom'},
                        command: 'send-email'
                    },
                    @endif
                    {
                        id: 'fake'
                    }
                ]
            }, {
                // If you use this id the default CSS will place this panel on top right corner for you
                id: 'views',
                buttons: [{
                    id: 'open-toolbar',
                    className: 'fa fa-wrench',
                    attributes: {title: 'Общие настройки КП', 'data-tooltip-pos': 'bottom'},
                    command: 'open-toolbar',
                    active: false,
                }, {
                    id: 'open-tm',
                    className: 'fa fa-cog',
                    attributes: {title: 'Настройки элементов КП', 'data-tooltip-pos': 'bottom'},
                    command: 'open-tm',
                    active: false,
                }, {
                    id: 'open-block-settings',
                    className: 'fa fa-th-large',
                    attributes: {title: 'Панель элементов КП', 'data-tooltip-pos': 'bottom'},
                    command: 'open-blocks',
                    active: true
                }]
            }, {
                id: 'devices-c',
                visible: true,
                buttons: [{
                    id: 'show-help',
                    command: 'show-help',
                    className: "fa fa-question"
                }]
            }, /*{
                id: 'devices-c',
                visible: true,
                buttons: [{
                    id: 'set-device-desktop',
                    command: 'set-device-desktop',
                    className: "fa fa-desktop",
                    active: true
                }, {
                    id: 'set-device-tablet',
                    command: 'set-device-tablet',
                    className: "fa fa-tablet"
                }, {
                    id: 'set-device-mobile',
                    command: 'set-device-mobile',
                    className: "fa fa-mobile"
                }]
            }*/]
        },
        commands: {
            defaults: [
                @can('create offer')
                {
                    id: 'storeData',
                    run: function (editor, sender) {
                         //Get settings for this template
                        let cpSettingsModel = editor.DomComponents.getWrapper().view.$el.find('#cp-settings').data('model'),
                            cpSettings = cpSettingsModel.get('cp-settings');

                        //Add custom settings
                        editor.StorageManager.get('remote').set('params', {settings: cpSettings, storeData: true});
    
                        //Проверяем, системный ли шаблон
                        @if($systemOffer == 1)
                        let offer_name = prompt('Введите название коммерческого предложения', 'Новое коммерческое предложение');
                        if (offer_name !== null) {
                            $.ajax({
                                url: "/editor/create-empty-offer",
                                data: {parentOfferId: '{{$offer->id}}', _token: _csrf, name: offer_name},
                                type: "POST",
                                success: function (response) {
                                    if (response) {
                                        let id = response.id;
                                        editor.StorageManager.get('remote').set('urlStore', id + '/store');
                                        editor.store();
                                        //Open created offer
                                        window.location = '/editor/' + id;
                                    }
                                }
                            });
                        }
                        @else
                        message('Сохранение коммерческого предложения...');
                        //Enable response messages
                        editor.showStoreReponse = true;
                        editor.checkConfig = false;

                        editor.store();
                        @endif

                        //inactive button
                        sender && sender.set('active', false);
                    },
                }, 
                @endcan
                @if(!$systemOffer && $urlOffer)
                { 
                    id: 'export-pdf',
                    run: function(editor, sender){
                        if(sender.get('clicked')){
                            return true;
                        }
                        location.href = '/{{$urlOffer}}/pdf';
                        //change status
                        sender.set('clicked', true);
                        //inactive button
                        sender && sender.set('active', false);
                    }
                },
                { 
                    id: 'export-pdf-full',
                    run: function(editor, sender){
                        if(sender.get('clicked')){
                            return true;
                        }
                        location.href = '/{{$urlOffer}}/pdf/full';
                        //change status
                        sender.set('clicked', true);
                    }
                },
                { 
                    id: 'export-excel',
                    run: function(editor, sender){
                        if(sender.get('clicked')){
                            return true;
                        }
                        location.href = '/{{$urlOffer}}/excel';
                        //change status
                        sender.set('clicked', true);
                        //inactive button
                        sender && sender.set('active', false);
                    }
                },
                @endcan
                @if($offer->dealCardLink)
                { 
                    id: 'deal-link',
                    run: function(editor, sender){
                        let link = '{{$offer->dealCardLink}}';
                        window.open(link);
                        //inactive button
                        sender && sender.set('active', false);
                    }
                },
                @endif
                // {
                //     id: 'set-device-desktop',
                //     run: function (editor, sender) {
                //         return editor.setDevice("Desktop")
                //     }
                // }, {
                //     id: 'set-device-tablet',
                //     run: function (editor, sender) {
                //         return editor.setDevice("Tablet")
                //     }
                // }, {
                //     id: 'set-device-mobile',
                //     run: function (editor, sender) {
                //         return editor.setDevice("Mobile portrait")
                //     }
                // }, 
                {id: 'open-toolbar'},
                {
                    id: 'show-help',
                    run: function(editor, sender){
                        //Show help block
                        window.utilities.showHelp();
                        //inactive button
                        sender && sender.set('active', false);
                    }
                }
            ]
        },
        plugins: ['grapesjs-plugin-kp10-base', 'gjs-plugin-export'],
        pluginsOpts: {
            'grapesjs-plugin-kp10-base': {
                token: _csrf,
                products: {!! $products !!},
                // clients: { !! $clients !!},
                path: '{{$path}}',
                productEmptyImg: '{{url("/storage/resource/templates/base/product/empty.png")}}',
                storage: {
                    gallery: 'Галерея'
                },
                employees: {!! $employees !!},
                offer: {!! $offer !!},
                integration: {!! $integration !!},
                currencies: {!! $currencies !!},
                webUrlOffer: '{{$webUrlOffer}}'
            }
        },
        // Default configuration
        storageManager: {
            id: 'gjs-',
            type: 'remote',
            autosave: false,
            autoload: true,
            urlLoad: '/editor/{{$offer->id}}/load',
            urlStore: '/editor/{{$offer->id}}/store',

            storeComponents: true, // Enable/Disable storing of components in JSON format
            storeStyles: true,     // Enable/Disable storing of rules/style in JSON format
            storeHtml: true,        // Enable/Disable storing of components as HTML string
            storeCss: true,         // Enable/Disable storing of rules/style as CSS string
            params: {test: 'some text here'},   // For custom values on requests
            headers: {'X-CSRF-TOKEN': _csrf},
            contentTypeJson: true, //Json format
        },
        assetManager: {
            upload: @can('edit file-manager') '/file-manager/upload' @else false @endcan,
            headers: {'X-CSRF-TOKEN': _csrf},
            uploadText: @can('edit file-manager') 'Для загрузки фото перетащите файл или кликните по выделенной области<br>Не более 2Mb' @else 'Нет прав для загрузки фотографий' @endcan,
            modalTitle: 'Выбор изображения',
            params: {path: '{{$path}}'},
            dropzone: 0, // 0!
            assets: {!! $assets?$assets:'[]' !!}
        }
        // deviceManager: {
        //     devices: [{
        //         name: 'Desktop',
        //         width: '',
        //     }, {
        //         name: 'Tablet',
        //         width: '768px',
        //         widthMedia: '992px',
        //     }, {
        //         name: 'Mobile landscape',
        //         width: '568px',
        //         widthMedia: '768px',
        //     }, {
        //         name: 'Mobile portrait',
        //         width: '320px',
        //         widthMedia: '480px',
        //     }],
        // },
    });

    //Fix when double save html
    editor.saveHtml = false;
    //Check template structure
    editor.checkConfig = true;
    //Update template. need wait
    editor.configuring = true;
    //Show message response from
    editor.showStoreReponse = false;
    
    //Fix for titiles
    var titles = document.querySelectorAll('*[title]');

    for (var i = 0; i < titles.length; i++) {
        var el = titles[i];
        var title = el.getAttribute('title');
        title = title ? title.trim(): '';
        if(!title)
          break;
        el.setAttribute('data-tooltip', title);
        el.setAttribute('title', '');
      }
    
    /** 
     * Modal message for
     * message({text: 'Ссылка на коммерческое предложение скопирована', error: true});
     **/
    window.message = function(message, timeout){
        let $modalMessage = $('#modal-message'),
            $text = $modalMessage.find('h4.text-center');

            //Remove previously added classes
            $modalMessage.removeClass('modal-danger');

            if(!$modalMessage.hasClass('in')){
                show();
            }else{
                $modalMessage.modal('hide');
                $modalMessage.data('bs.modal', null); 
                //after window was hide
                $modalMessage.on('hidden.bs.modal', function(){
                    //Show new message
                    setTimeout(function(){show();}, 100);
                    //
                    $modalMessage.unbind('hidden.bs.modal');
                });
            }
        
            function show() {
                let text = message;
                if(typeof message === 'object'){
                    if(message.error){
                        //Add passed classes
                        $modalMessage.addClass('modal-danger');
                    }
                    //set message
                    text = message.text;
                }

                //Set message
                $text.empty().html(text);

                if(typeof message !== 'object'){
                    //Show modal
                    $modalMessage.modal('show');
                } else {
                    $modalMessage.modal(message);
                }

                //For autohide
                if(timeout){
                    setTimeout(function(){
                        window.hideMessage();
                    }, timeout);
                }
            }
    }

    window.modal = function(message){
        let $modalMessage = $('#modal-modal'),
            $modalDialogue = $modalMessage.find('.modal-dialog'),
            $modalBody = $modalMessage.find('.modal-body');

            //Remove previously added classes
            $modalMessage.find('.modal-dialog').removeClass('modal-lg');

            if(!$modalMessage.hasClass('in')){
                show();
            }else{
                $modalMessage.modal('hide');

                //after window was hide
                $modalMessage.on('hidden.bs.modal', function(){
                    //Show new message
                    show();
                    //
                    $modalMessage.unbind('hidden.bs.modal');
                });
            }
        
            function show() {
                if(typeof message === 'object'){
                
                    //Class for dialogue
                    if(message.big){
                        $modalDialogue.addClass('modal-lg');
                    }

                    message = message.body;

                }

                //Set message
                $modalBody.empty().html(message);

                //Show modal
                $modalMessage.modal('show');
            }
    }

    window.hideMessage = function(){
        $('#modal-message').modal('hide');
    }

    /**
     * Modal confirm
     **/
    window.confirm = function(message, success, cancel){
        let $modal = $('#modal-confirm'),
            $text = $modal.find('h4.text-center');

            //Remove previously added classes
            $modal.removeClass('modal-danger');

            if(!$modal.hasClass('in')){
                show();
            }else{
                $modal.modal('hide');

                //after window was hide
                $modal.on('hidden.bs.modal', function(){
                    //Show new message
                    show();
                    //
                    $modal.unbind('hidden.bs.modal');
                });

                return;
            }
            //Run callback after press OK
            $modal.find('.btn-ok').unbind('click').on('click', () => success($modal));
            //after window was hide
            $modal.on('hide.bs.modal', function(){
                if(cancel){
                    cancel();
                }
                //
                $modal.unbind('hidden.bs.modal');
            });
        
            function show() {
                if(typeof message === 'object'){
                
                    if(message.error){
                        //Add passed classes
                        $modal.addClass('modal-danger');
                    }
                    //set message
                    message = message.text;
                }

                //Set message
                $text.empty().html(message);

                //Show modal
                $modal.modal('show');
            }
    }  

    window.message('Пожалуйста подождите, происходит конфигурирование коммерческого предложения');
   
    /*
     data-gjs-type=""
     data-gjs-badgable="false"
     data-gjs-droppable="false"
     data-gjs-draggable="false"
     data-gjs-removable="false"
     data-gjs-stylable="false"
     data-gjs-highlightable="false"
     data-gjs-editable="false"
     data-gjs-resizable="false"

     defaults: {
      tagName: 'div',
      type: '',
      name: '',
      removable: true,
      draggable: true,
      droppable: true,
      badgable: true,
      stylable: true,
      'stylable-require': '',
      unstylable: '',
      highlightable: true,
      copyable: true,
      resizable: false,
      editable: false,
      layerable: true,
      selectable: true,
      hoverable: true,
      void: false,
      state: '', // Indicates if the component is in some CSS state like ':hover', ':active', etc.
      status: '', // State, eg. 'selected'
      content: '',
      icon: '',
      style: '', // Component related style
      classes: '', // Array of classes
      script: '',
      attributes: '',
      traits: ['id', 'title'],
      propagate: '',
      toolbar: null
    },
     */
    </script>
</body>

</html>