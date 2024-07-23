<template>
    <a :href="url" :class="(active ? 'text-blue-400' : 'grayscale') + ' flex relative basis-auto px-6 md:px-4 py-3 md:pl-6 border-blue-500 text-center'">
        <img v-if="image" :src="image" class="w-[20px] mr-3 ml-1">
        <i v-else-if="icon" :class="'w-[20px] block mr-3 ml-1 ' + icon"></i>
        <slot></slot>
        <a ref="anchor" v-if="sideMenuItems.length" href="javascript:void(0);" :class="(active ? 'rotate-180' : '' ) + ' absolute transition-all right-6'" v-on:click="dropDown">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </a>
    </a>

    <ul ref="submenu" :class="(active ? '' : 'h-0 hidden') + ' flex flex-col transition-all text-sm pl-12 pr-4'">
        <li v-for="(item, key, index) in sideMenuItems" class="w-full my-1">
            <a :href="isInteractive(item) ? `${url}#data-menu-item=${item.code}` : item.url"
               :class="(item.active ? 'text-blue-400' : '') + ' w-full flex px-3 py-2 md:rounded-r-full'"
               v-on:click="isInteractive(item) ? activateSideMenu(item) : void(0)"
            >
                {{ item.label }}
            </a>
        </li>
    </ul>
</template>
<script>
export default {
    props: ['image', 'icon', 'url', 'active', 'sideMenu'],
    computed: {
        sideMenuItems: {
            get() {
                return Object.values(this.sideMenu).sort((a, b) => a.sort > b.sort ? 1 : 0)
            }
        },
        activeItem: {
            get() {
                return Object.values(this.sideMenu).find((item) => item.active).code
            }
        }
    },
    methods: {
        dropDown() {
            this.$refs.anchor.classList.toggle('rotate-180');
            ['hidden', 'h-0'].map((c) => this.$refs.submenu.classList.toggle(c));
        },
        isInteractive(item) {
            return ['javascript:;', 'javascript:void(0);'].indexOf(item.url) > -1;
        },
        activateSideMenu(item) {
            Object.keys(this.sideMenu).forEach((e) => {
                this.sideMenu[e].active = this.sideMenu[e].code === item.code;
            });

            this.$forceUpdate();

            this.portal.querySelectorAll('.layout[data-content-id]:not(.hide)').forEach((e) => {
                e.classList.add('hide')
            })
            this.portal.querySelector(`[data-content-id="${item.code}"]`)?.classList.remove('hide');
        }
    },
    mounted() {
        if (this.active) {
            window.addEventListener('load', () => {
                const hash = window.location.hash.substring(1);
                if (!hash) {
                    return;
                }
                const match = hash.match(/data-menu-item=(\w*.?)/);
                if (match) {
                    this.activateSideMenu({code: match[1] || this.activeItem});
                }
            });
        }
    }
}
</script>
