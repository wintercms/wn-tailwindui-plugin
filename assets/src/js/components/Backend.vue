<template>
    <div class="flex flex-col md:flex-row min-h-screen">
        <div class="w-full">
            <div class="w-full bg-gray-800 p-4 flex">
                <div class="flex items-center mb-3 h-8 w-[240px] shrink-0">
                    <img
                        class="h-12 w-auto px-4 m-auto"
                        :src="logo"
                        :alt="appName"
                    >
                </div>
                <div class="flex flex-1 justify-center lg:justify-start">
                    <div class="w-full max-w-lg lg:max-w-xs">
                        <label for="search" class="sr-only">Search</label>
                        <div class="relative text-gray-400 focus-within:text-gray-600">
                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center pl-3">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input id="search" class="block w-full rounded border-0 bg-white py-1.5 pl-3 pr-3 text-gray-900 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-indigo-600 sm:text-sm sm:leading-6 mt-1" placeholder="Search" type="search" name="search">
                        </div>
                    </div>
                </div>
                <div class="flex flex-row gap-3">
                    <QuickActions :actions="quickActions"></QuickActions>
                    <User :user="user"></User>
                </div>
            </div>
            <div class="flex h-full">
                <div class="w-full md:w-64 pb-6 border border-0 bg-gray-900 text-white">
                    <Menu :menu="menu"></Menu>
                </div>
                <section ref="portal" class="winter-portal bg-gray-100 h-full min-h-[600px] p-3 pt-5">
                    <slot></slot>
                </section>
            </div>
        </div>
    </div>
</template>
<script>
import QuickActions from "./menu/QuickActions.vue";
import Menu from "./menu/Menu.vue";
import User from "./menu/User.vue";

export default {
    components: {User, Menu, QuickActions},
    props: ['appName', 'logo', 'menu', 'quickActions', 'user'],
    mounted() {
        this.$.appContext.config.globalProperties.portal = this.$refs.portal;
    }
}
</script>
