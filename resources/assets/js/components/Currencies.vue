<template>
    <div>
        <table class="table table-hover">
            <thead>
            <tr>
                <th style="width:80px;" class="">Базовая</th>
                <th>Валюта</th>
                <th>Код валюты</th>
                <th>Курс</th>
                <th>Синхронизация</th>
            </tr>
            </thead>
            <tbody id="table-body">
            <!--<tr v-for="(currency, index) in currencies" :key="index" @mouseover="showByIndex = index" @mouseout="showByIndex = null">-->

            <tr v-for="(currency, index) in currencies" :key="index" @mouseover="showByIndexLocal = index" @mouseout="showByIndexLocal = null">

                <td align="left">
                    <input @click.prevent="setBasicCurrencie" :data-id="currency.id" type="radio"
                           name="basic" :checked="currency.basic == 1">
                </td>
                <td>
                    <a :href="'/settings/currencies/'+ currency.id + '/edit'">{{currency.name}}</a>
                </td>

                <td>
                    {{currency.charCode}}
                </td>

                <td>
                    <span v-show="currency.sync === 1">
                    {{currency.syncRate}}
                    </span>

                    <span v-show="currency.sync !== 1">
                    {{currency.rate}}
                    </span>
                </td>

                <td width="20px" align="center">
                    <input v-show="currency.basic !== 1" :data-id="currency.id" @click="changeSync"
                           type="checkbox" value="" :checked="currency.sync == 1">

                    <div class="offer-panel" v-show="showByIndexLocal === index">
                        <div class="btn-group">
                            <a  class="btn btn-default" data-toggle="tooltip" title="Удалить" @click.prevent="deleteCurrencie(currency.id)"><i class="fa fa-trash"></i></a>
                        </div>
                    </div>
                </td>

            </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
	export default {
		props: {
			currencies: {},
			showByIndex:null
		},
 		data() {
			return {
				showByIndexLocal:this.showByIndex
            };
		},
		mounted: function () {
			console.log(this.currencies);
		},
		methods: {
			setBasicCurrencie(el){
				let dataId = $(el.target).attr('data-id');
				this.$parent.$emit("setBasicCurrencie", {
					id: dataId
				});
            },
			changeSync(el){
				let dataId = $(el.target).attr('data-id');
				this.$parent.$emit("changeSync", {
					id: dataId
				});
            },
			deleteCurrencie(currencyId){
				this.$parent.$emit("deleteCurrencie", {
					id: currencyId
				});
            }
        }
	}
</script>

