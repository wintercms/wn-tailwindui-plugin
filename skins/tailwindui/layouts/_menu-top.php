<?php
    $menuLocation = \Backend\Models\BrandSetting::get('menu_location');
    $iconLocation = \Backend\Models\BrandSetting::get('icon_location');
    $mySettings = System\Classes\SettingsManager::instance()->listItems('mysettings');
?>

<headless-disclosure
    as="nav"
    id="layout-sidenav-1"
    v-slot="{ open }"
    class="print:hidden
        <?php if ($menuLocation === 'top'): ?>
            bg-gray-900
        <?php else: ?>
            bg-gray-900 md:bg-white dark:bg-gray-900 dark:md:bg-gray-900 md:shadow-bottom
        <?php endif; ?>
    "
>
    <div class="px-6 lg:px-0 lg:mr-6">
        <div class="
            relative flex items-center
            <?php if ($menuLocation === 'top' && $iconLocation === 'tile'): ?>
                p-2
            <?php else: ?>
                justify-between h-16
            <?php endif; ?>
        ">

            <!-- Mobile menu button-->
            <headless-disclosure-button
                class="btn btn-secondary btn-sm px-0 mr-4 md:hidden"
            >
                <!-- @TODO: Needs translation -->
                <span class="sr-only">Open main menu</span>
                <menu-icon v-if="!open" class="block h-6 w-6" aria-hidden="true"></menu-icon>
                <x-icon v-else class="block h-6 w-6" aria-hidden="true"></x-icon>
            </headless-disclosure-button>

            <?= $this->fireViewEvent('backend.partials.menuTop.extend', [
                'menuLocation' => $menuLocation,
                'iconLocation' => $iconLocation,
            ]); ?>

            <!-- Main menu -->
            <div
                class="flex-1 flex overflow-x-auto"
            >
                <div
                    class="
                        hidden md:block
                        <?php if ($menuLocation === 'side'): ?>
                            md:mx-6
                        <?php endif; ?>
                    ">
                    <div class="flex items-stretch space-x-2 pl-2">
                        <!-- Header search - side menu -->
                        <!-- TODO: unhide when implmented -->
                        <?php if ($menuLocation === 'side'): ?>
                            <?= $this->makeLayoutPartial('partials/menu/header-search'); ?>
                        <?php endif; ?>

                        <!-- main menu items -->
                        <?php foreach (BackendMenu::listMainMenuItems() as $item): ?>
                            <?php $isActive = BackendMenu::isMainMenuItemActive($item); ?>
                            <?php $hasChildren = (bool) count($item->sideMenu); ?>
                            <?php if ($menuLocation === 'top'): ?>
                                <headless-menu
                                    as="div"
                                    class="headless-menu flex items-stretch"
                                    v-slot="{ show, open }"
                                >
                                    <div
                                        class="
                                            flex relative items-stretch group rounded-md min-w-max ptransition duration-300 ease-in
                                            <?php if ($iconLocation === 'tile') : ?>
                                                pr-2
                                            <?php else: ?>
                                                pr-3
                                            <?php endif; ?>
                                            <?php if ($isActive) : ?>
                                                bg-primary text-white
                                            <?php else: ?>
                                                text-gray-300 hover:bg-gray-700
                                            <?php endif; ?>
                                        "
                                    >
                                        <a
                                            href="<?= $item->url ?>"
                                            class="
                                                flex
                                                rounded-md text-sm font-medium
                                                group-hover:text-white hover:no-underline
                                                active:no-underline focus:no-underline focus:text-white
                                                <?php if ($isActive) : ?>
                                                    bg-primary text-white hover:!bg-primary
                                                <?php else: ?>
                                                    text-gray-300
                                                <?php endif; ?>
                                                <?php if ($iconLocation === 'tile'): ?>
                                                    flex-col justify-between pl-2 py-1.5 min-w-[70px]
                                                <?php else: ?>
                                                   items-center pl-3 py-2
                                                <?php endif; ?>
                                            "
                                            <?php if ($isActive) : ?>
                                                aria-current="page"
                                            <?php endif; ?>
                                        >
                                            <?php if ($iconLocation !== 'hidden') : ?>
                                                <?php if ($item->iconSvg): ?>
                                                    <img
                                                        src="<?= Url::asset($item->iconSvg) ?>"
                                                        class="
                                                            <?= $this->makeLayoutPartial('partials/menu/top/icon-classes', [
                                                                'item' => $item,
                                                                'iconLocation' => $iconLocation,
                                                            ]); ?>
                                                        "
                                                        alt="<?= $iconLocation === 'only' ? e(trans($item->label)) : '' ?>"
                                                        title="<?= $iconLocation === 'only' ? e(trans($item->label)) : '' ?>"
                                                        loading="lazy"
                                                    >
                                                <?php else: ?>
                                                    <i
                                                        class="
                                                            <?= $this->makeLayoutPartial('partials/menu/top/icon-classes', [
                                                                'item' => $item,
                                                                'iconLocation' => $iconLocation,
                                                            ]); ?>
                                                        "
                                                        title="<?= $iconLocation === 'only' ? e(trans($item->label)) : '' ?>"
                                                    >
                                                    </i>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <?php if ($iconLocation !== 'only'): ?>
                                                <span class="text-center whitespace-nowrap">
                                                    <?= e(trans($item->label)) ?>
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                        <?php if ($item->counter): ?>
                                            <span
                                                class="counter"
                                                data-menu-id="<?= e($item->code) ?>"
                                                <?php if ($item->counterLabel): ?>
                                                    title="<?= e(trans($item->counterLabel)) ?>"
                                                <?php endif ?>
                                            >
                                                <?= e($item->counter) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($hasChildren): ?>
                                            <headless-menu-button
                                                as="div"
                                                <?php if ($iconLocation === 'tile'): ?>
                                                    class="flex flex-col justify-end"
                                                <?php endif; ?>
                                            >
                                                <chevron-down-icon
                                                    class="
                                                        h-4 w-4 cursor-pointer
                                                        <?php if ($isActive) : ?>
                                                            bg-primary text-white
                                                        <?php else: ?>
                                                            text-gray-300
                                                        <?php endif ?>
                                                        <?php if ($iconLocation === 'tile'): ?>
                                                            mb-2 ml-1
                                                        <?php else: ?>
                                                            my-4 ml-2
                                                        <?php endif; ?>
                                                    "
                                                    aria-hidden="true"
                                                />
                                            </headless-menu-button>
                                        <?php endif; ?>
                                    </div>

                                    <!-- child menu -->
                                    <?php if ($hasChildren): ?>
                                        <transition
                                            v-show="open"
                                            enter-active-class="transition ease-out duration-100"
                                            enter-from-class="opacity-0 scale-95"
                                            enter-to-class="opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="opacity-100 scale-100"
                                            leave-to-class="opacity-0 scale-95"
                                        >
                                            <headless-menu-items
                                                class="origin-top-left absolute left-0 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 focus:outline-none"
                                                data-control="sidenav"
                                                data-active-class="active"
                                                static
                                            >
                                                <?php foreach ($item->sideMenu as $child): ?>
                                                    <?php $childIsActive = BackendMenu::isSideMenuItemActive($child); ?>
                                                    <headless-menu-item
                                                        v-slot="{ active }"
                                                    >
                                                        <a
                                                            href="<?= $child->url ?>"
                                                            <?php if ($child->url === 'javascript:;'): ?>
                                                                data-menu-item="<?= $child->code ?>"
                                                            <?php endif; ?>
                                                            class="
                                                                group flex relative items-center px-4 py-2 text-sm hover:no-underline transition duration-300 ease-in
                                                                <?php if ($childIsActive): ?>
                                                                    bg-primary text-white hover:text-white hover:bg-primary dark:bg-gray-700 dark:text-white dark:hover:text-white dark:hover:bg-gray-700
                                                                <?php else: ?>
                                                                    text-gray-700 hover:text-white hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700
                                                                <?php endif; ?>
                                                            "
                                                        >
                                                            <?php if ($iconLocation !== 'hidden') : ?>
                                                                <?php if ($child->iconSvg): ?>
                                                                    <img
                                                                        src="<?= Url::asset($child->iconSvg) ?>"
                                                                        class="svg-icon w-4 h-4"
                                                                        loading="lazy"
                                                                    >
                                                                <?php else: ?>
                                                                    <i
                                                                        class="
                                                                            <?= $child->icon ?> mr-3 h-4 w-4
                                                                            <?php if ($childIsActive): ?>
                                                                                text-white group-hover:text-white
                                                                            <?php else: ?>
                                                                                text-gray-400 text-gray-300 group-hover:text-gray-500 dark:group-hover:text-white
                                                                            <?php endif; ?>
                                                                        "
                                                                    >
                                                                    </i>
                                                                <?php endif; ?>
                                                            <?php endif; ?>
                                                            <span><?= e(trans($child->label)) ?></span>
                                                            <?php if ($child->counter): ?>
                                                                <span
                                                                    class="counter"
                                                                    data-menu-id="<?= e($child->code) ?>"
                                                                    <?php if ($child->counterLabel): ?>
                                                                        title="<?= e(trans($child->counterLabel)) ?>"
                                                                    <?php endif ?>
                                                                >
                                                                    <?= e($child->counter) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </a>
                                                    </headless-menu-item>
                                                <?php endforeach; ?>
                                            </headless-menu-items>
                                        </transition>
                                    <?php endif ?>
                                </headless-menu>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- quick actions -->
            <div class="flex items-center">
                <?= $this->makeLayoutPartial('partials/menu/top/quick-actions', [
                    'mySettings' => $mySettings,
                    'menuLocation' => $menuLocation,
                ]); ?>
            </div>
        </div>
    </div>

    <!-- mobile menu -->
    <?= $this->makeLayoutPartial('partials/menu/top/mobile-menu', [
        'iconLocation' => $iconLocation,
    ]); ?>
</headless-disclosure>
