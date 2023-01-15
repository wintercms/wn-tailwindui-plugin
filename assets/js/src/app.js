import { createApp, onMounted } from 'vue';
import './darkmode';
import './menu';
import './winter.sidepaneltab';

import {
    Disclosure,
    DisclosureButton,
    DisclosurePanel,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems
} from '@headlessui/vue';

import {
    BellIcon,
    ChevronDownIcon, MenuIcon, PlusSmIcon, SearchIcon,
    XIcon
} from '@heroicons/vue/outline';

// TODO: for now we are mounting multiple vue apps as plugins can inject <script> tags inside our vue mounted elements
// elements with an id that starts with `vue-app-` will automatically be mounted
// @see https://github.com/laravel-mix/laravel-mix/issues/3112

// `menu` is a reserved html element so we rename headlessui components with `headless-` prefix
// https://stackoverflow.com/questions/69397459/do-not-use-built-in-or-reserved-html-elements-with-vue-tailwindui-and-headless

const rootApp = {
    components: {
        'headless-disclosure': Disclosure,
        'headless-disclosure-button': DisclosureButton,
        'headless-disclosure-panel': DisclosurePanel,
        'headless-menu': Menu,
        'headless-menu-button': MenuButton,
        'headless-menu-item': MenuItem,
        'headless-menu-items': MenuItems,
        'bell-icon': BellIcon,
        'chevron-down-icon': ChevronDownIcon,
        'plus-sm-icon': PlusSmIcon,
        'menu-icon': MenuIcon,
        'search-icon': SearchIcon,
        'x-icon': XIcon,
    },
    setup() {
        onMounted(() => {
            $(function() {
                const $menuBtnSelector = $('[id^="headlessui-menu-button-"]');

                // Popping Out of Hidden Overflow
                // @see https://css-tricks.com/popping-hidden-overflow/
                $menuBtnSelector.on('click', function() {
                    const $menuBtn = $(this);
                    const $menuItem = $menuBtn.closest('.headless-menu');
                    const $wrapper =  $menuItem.find('[id^="headlessui-menu-items-"]');

                    if ($menuItem.length) {
                        // grab the menu item's position relative to its positioned parent
                        const position = $menuItem.position();
                        const offset = parseInt($menuItem.css('marginLeft').replace('px', ''));
                        const top = position.top + $menuItem.offset().top + $menuItem.outerHeight(true);
                        const left = position.left + offset;

                        // place the submenu in the correct position relevant to the menu item
                        if ($wrapper.length) {
                            $wrapper.css({top, left});
                        }
                    }
                });

                // menu hover
                $('.headless-menu').on('mouseover mouseout', function() {
                    const $this = $(this);
                    const $button = $this.find('[id^="headlessui-menu-button-"]');
                    const $menu = $(`#${$button.attr('aria-controls')}`);
                    $button.trigger('click');
                });
            });
        });
    },
};

const appFactory = () => {
    return createApp({ ...rootApp });
};

const vueApps = document.querySelectorAll('[id^="vue-app-"]');

for (const prop of vueApps) {
    appFactory().mount(document.getElementById(prop.id));
}
