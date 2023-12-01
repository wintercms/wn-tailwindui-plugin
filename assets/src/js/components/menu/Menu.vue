<template>
    <ul class="mt-4 flex flex-row md:flex-col overflow-x-auto">
        <li>
            <div class="flex items-center mb-3 h-16 shrink-0">
                <img
                    class="h-12 w-auto px-4 m-auto"
                    :src="logo"
                    :alt="appName"
                >
            </div>
        </li>
        <li v-for="item in menuItems">
            <MenuItem :image="item.iconSvg"
                      :icon="item.icon"
                      :url="item.url"
                      :active="item.isActive"
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
    components: {MenuItem}
}
</script>
