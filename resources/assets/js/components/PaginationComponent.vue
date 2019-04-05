<template>
        <div id="table-pagination">
            <ul class="pagination">
                <li class="paginate_button">
                    <a href="#" class="pagination-previous" @click.prevent="changePage(1)"
                       :disabled="pagination.current_page <= 1">Первая</a>
                </li>
                <li class="paginate_button">
                    <a href="#" class="pagination-previous" @click.prevent="changePage(pagination.current_page - 1)"
                       :disabled="pagination.current_page <= 1">Назад</a>
                </li>

                <li class="paginate_button" v-for="(page, index) in pages" :key="index" :class="isCurrentPage(page) ? 'active' : ''">
                    <a href="#" class="pagination-link" :class="isCurrentPage(page) ? 'is-current' : ''"
                       @click.prevent="changePage(page)">{{ page }}</a>
                </li>

                <li clas s="paginate_button">
                    <a href="#" class="pagination-next" @click.prevent="changePage(pagination.current_page + 1)"
                       :disabled="pagination.current_page >= pagination.last_page">Вперёд</a>
                </li>
                <li class="paginate_button">
                    <a href="#" class="pagination-next" @click.prevent="changePage(pagination.last_page)"
                       :disabled="pagination.current_page >= pagination.last_page">Последняя</a>
                </li>
            </ul>
        </div>
</template>

<script>
	export default {
		props: ['pagination', 'offset'],
		methods: {
			isCurrentPage(page) {
				return this.pagination.current_page === page;
			},
			changePage(page) {
				if (page > this.pagination.last_page) {
					page = this.pagination.last_page;
				}
				this.pagination.current_page = page;
				this.$emit('paginate');
			}
		},
		computed: {
			pages() {
				let pages = [];
				let from = this.pagination.current_page - Math.floor(this.offset / 2);
				if (from < 1) {
					from = 1;
				}
				let to = from + this.offset - 1;
				if (to > this.pagination.last_page) {
					to = this.pagination.last_page;
				}
				while (from <= to) {
					pages.push(from);
					from++;
				}
				return pages;
			}
		}
	}
</script>