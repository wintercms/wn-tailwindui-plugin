<template>
    <ul class="px-2 flex flex-row md:flex-col">
        <li v-for="item in actionItems" class="h-full">
            <a
                :href="item.url"
                :title="item.label"
                class="align-middle"
            >
                <img v-if="item.iconSvg" :src="item.iconSvg" class="w-[30px] mr-3 ml-1">
                <i v-else-if="item.icon" :class="'w-[30px] block mr-3 ml-1 ' + item.icon"></i>
            </a>
        </li>
    </ul>
</template>
<script>
export default {
    props: ['actions'],
    computed: {
        actionItems: {
            get() {
                return Object.values(this.actions).sort((a, b) => a.sort > b.sort ? 1 : 0)
            }
        }
    },
    components: {}
}
</script>
