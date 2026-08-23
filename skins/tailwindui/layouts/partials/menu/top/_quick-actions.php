<!--
    Quick Create button
    @TODO:
        - Refactor QuickAction items to be able to render themselves via a
        partial with the additional aim of supporting dropdown menus defined
        within said partial with the usage of certain classes. This would then
        be used for the QuickCreate button as well as the UserProfile menu
        - Unhide when implemented
-->
<div class="shrink-0 hidden">
    <div class="tw-dropdown ml-3">
        <button type="button" class="btn btn-primary relative inline-flex items-center px-4 py-2 shadow-sm">
            <span>Create</span>
            <svg class="ml-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6" />
            </svg>
        </button>

        <div class="tw-dropdown-menu tw-dropdown-menu-right mt-0 py-1 ring-1 ring-black dark:ring-gray-500">
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline">Something</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline">Something else</a>
            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline">Some other thing</a>
        </div>
    </div>
</div>

<div class="flex items-center ml-4 shrink-0">
    <!--
        Notifications item
        @TODO:
            - Implement as QuickAction item provided by future Winter.Notifications
            plugin or perhaps in the core backend / system module
    -->
    <button
        type="button"
        class="quick-link<?= $menuLocation === 'side' ? ' quick-link-light' : '' ?> hidden"
    >
        <span class="absolute -top-1 -right-1 p-0.5 bg-red-500 rounded-full text-xxs text-white font-semibold leading-none z-10">9+</span>
        <!-- @TODO: Needs translation -->
        <span class="sr-only">View notifications</span>
        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
    </button>

    <!-- quick link actions -->
    <?php foreach (BackendMenu::listQuickActionItems() as $item): ?>
        <a
            href="<?= $item->url ?>"
            title="<?= e(trans($item->label)) ?>"
            <?= Html::attributes($item->attributes) ?>
            class="quick-link<?= $menuLocation === 'side' ? ' quick-link-light' : '' ?>"
        >

            <?php if ($item->iconSvg): ?>
                <img
                    src="<?= Url::asset($item->iconSvg) ?>"
                    class="svg-icon h-6 w-6" loading="lazy" />
            <?php endif ?>

            <i class="<?= $item->iconSvg ? 'svg-replace' : null ?> <?= $item->icon ?> text-2xl"></i>
        </a>
    <?php endforeach ?>

    <!-- user profile menu -->
    <div class="tw-dropdown ml-3">
        <button
            type="button"
            class="bg-gray-800 flex text-sm rounded-full focus:outline-none"
        >
            <!-- @TODO: Needs translation -->
            <span class="sr-only">Open user menu</span>
            <img
                class="h-8 w-8 rounded-full"
                src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>"
                loading="lazy"
                alt="<?= e(trans('backend::lang.account.signed_in_as', ['full_name' => $this->user->full_name])) ?>"
            />
        </button>

        <div
            class="tw-dropdown-menu tw-dropdown-menu-right py-1 w-64 ring-1 ring-black dark:ring-gray-500 divide-y divide-gray-200 dark:divide-gray-500"
        >
            <div class="px-4 py-3">
                <div class="shrink-0 group block">
                    <div class="flex items-center">
                        <div>
                            <img class="inline-block h-8 w-8 rounded-full" src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>" alt="<?= $this->user->full_name ?>" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm">
                                <?= e(trans('backend::lang.account.signed_in_as', ['full_name' => null])) ?>
                            </p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-500 truncate">
                                <?= $this->user->full_name ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php foreach ($mySettings as $category => $items): ?>
                <div class="py-1">
                    <?php foreach ($items as $item): ?>
                        <a
                            href="<?= $item->url ?>"
                            class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700"
                        >
                            <i class="<?= $item->icon ?> mr-2 text-sm text-center min-w-[1.25em] text-gray-400 group-hover:text-gray-500 dark:group-hover:text-white"></i>
                            <?= e(trans($item->label)) ?>
                        </a>
                    <?php endforeach ?>
                </div>
            <?php endforeach ?>
            <?php if (BackendAuth::user() && BackendAuth::user()->hasAccess('winter.tailwindui.manage_own_appearance.dark_mode')) : ?>
                <div class="py-1">
                    <?= Form::open(['class' => 'px-4 py-1']) ?>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                data-request="onTailwindUISetTheme"
                                data-request-data="dark_mode: 'auto'"
                                data-request-success="updateColorScheme(data.dark_mode);"
                                class="btn-darkmode"
                                title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.auto')) ?>"
                            >
                                <i class="icon-computer"></i>
                            </button>
                            <button
                                type="button"
                                data-request="onTailwindUISetTheme"
                                data-request-data="dark_mode: 'light'"
                                data-request-success="updateColorScheme(data.dark_mode);"
                                class="btn-darkmode"
                                title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.light')) ?>"
                            >
                                <i class="icon-sun"></i>
                            </button>
                            <button
                                type="button"
                                data-request="onTailwindUISetTheme"
                                data-request-data="dark_mode: 'dark'"
                                data-request-success="updateColorScheme(data.dark_mode);"
                                class="btn-darkmode"
                                title="<?= e(trans('winter.tailwindui::lang.preferences.dark_mode.dark')) ?>"
                            >
                                <i class="icon-moon"></i>
                            </button>
                        </div>
                    <?= Form::close() ?>
                </div>
            <?php endif; ?>
            <div class="py-1">
                <a
                    href="<?= Backend::url('backend/auth/signout') ?>"
                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 hover:no-underline dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700"
                >
                    <i class="icon-sign-out mr-2 text-sm text-gray-400 group-hover:text-gray-500 dark:group-hover:text-white text-center min-w-[1.25em]"></i>
                    <?php if (\BackendAuth::isImpersonator()) : ?>
                        <?= e(trans('backend::lang.account.stop_impersonating')) ?>
                    <?php else: ?>
                        <?= e(trans('backend::lang.account.sign_out')) ?>
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>
