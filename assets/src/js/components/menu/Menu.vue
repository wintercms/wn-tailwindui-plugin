<template>
    <ul class="mt-4 w-[256px] flex flex-row md:flex-col overflow-x-auto">
        <li v-for="item in menuItems">
            <MenuItem :image="item.iconSvg"
                      :icon="item.icon"
                      :url="item.url"
                      :active="item.isActive"
                      :sideMenu="item.sideMenu"
            >
                {{ item.label }}
            </MenuItem>
        </li>
    </ul>
</template>
<script>
import MenuItem from "./MenuItem.vue";
export default {
    props: ['menu', 'logo', 'appName'],
    computed: {
        menuItems: {
            get() {
                return Object.values(this.menu).sort((a, b) => a.sort > b.sort ? 1 : 0)
            }
        }
    },
    components: {MenuItem},
}
</script>
