<template>
   <div class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Название</h4>
                </div>

                <slot name="modal-body"></slot>
                
                <slot name="modal-footer"></slot>
                <!--                 
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-left btn-cancel" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary btn-ok">Да</button>
                </div> -->
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.');
            // console.log( $(this.$el).modal('show'));
        },
        methods: {
            show(){
                $(this.$el).modal('show')
            },
            hide(){
                $(this.$el).modal('hide')
            }
        }
    }
</script>
