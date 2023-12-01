<template>
    <div class="flex flex-col md:flex-row min-h-screen">
        <div class="w-full w-full md:w-64 pb-6 border border-0 bg-[#0A0A0A] text-white">
            <Menu :menu="menu" :logo="logo" :appName="appName"></Menu>
        </div>
        <div class="w-full">
            <div class="w-full bg-white p-4 flex justify-between">
                <input type="text" class="border-0 w-auto focus:outline-0 focus:outline-b-1" placeholder="magic search todo...">
                <div class="flex flex-row gap-3">
                    <QuickActions :actions="quickActions"></QuickActions>
                    <User :user="user"></User>
                </div>
            </div>
            <section class="winter-portal bg-gray-100 h-full p-3 pt-5">
                <slot></slot>
            </section>
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
}
</script>
