<?php
    $menuLocation = \Backend\Models\BrandSetting::get('menu_location');
    $iconLocation = \Backend\Models\BrandSetting::get('icon_location');
    $mySettings = System\Classes\SettingsManager::instance()->listItems('mysettings');
?>

<nav
    id="layout-sidenav-1"
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
                justify-between min-h-[4rem]
            <?php endif; ?>
        ">

            <!-- Mobile menu button-->
            <button
                type="button"
                data-control="mobile-menu-toggle"
                aria-expanded="false"
                aria-controls="layout-mobile-menu"
                class="btn btn-secondary btn-sm px-0 mr-4 md:hidden"
            >
                <!-- @TODO: Needs translation -->
                <span class="sr-only">Open main menu</span>
                <svg class="mobile-menu-icon-open block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg class="mobile-menu-icon-close hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <?= $this->fireViewEvent('backend.partials.menuTop.extend', [
                'menuLocation' => $menuLocation,
                'iconLocation' => $iconLocation,
            ]); ?>

            <!-- Main menu -->
            <div
                class="flex-1 flex min-w-0"
            >
                <div
                    class="
                        hidden md:block
                        <?php if ($menuLocation === 'side'): ?>
                            md:mx-6
                        <?php endif; ?>
                    ">
                    <div class="flex flex-wrap items-stretch gap-2 pl-2">
                        <!-- Header search - side menu -->
                        <!-- TODO: unhide when implmented -->
                        <?php if ($menuLocation === 'side'): ?>
                            <?= $this->makeLayoutPartial('partials/menu/header-search'); ?>
                        <?php endif; ?>

                        <!-- main menu items -->
                        <?php foreach (BackendMenu::listMainMenuItems() as $item): ?>
                            <?php $isActive = BackendMenu::isMainMenuItemActive($item); ?>
                            <?php $hasChildren = (bool) count($item->sideMenu); ?>
                            <?php $itemFullCode = $item->owner . '.' . $item->code; ?>
                            <?php if ($menuLocation === 'top'): ?>
                                <div class="tw-dropdown flex items-stretch">
                                    <div
                                        class="
                                            flex items-stretch group rounded-md min-w-max transition duration-300 ease-in
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
                                            <span
                                                class="flex items-center pointer-events-none
                                                    <?php if ($iconLocation === 'tile'): ?>
                                                        flex-col justify-end
                                                    <?php endif; ?>
                                                "
                                                aria-hidden="true"
                                            >
                                                <svg
                                                    class="
                                                        h-4 w-4
                                                        <?php if ($isActive) : ?>
                                                            text-white
                                                        <?php else: ?>
                                                            text-gray-300
                                                        <?php endif ?>
                                                        <?php if ($iconLocation === 'tile'): ?>
                                                            mb-2 ml-1
                                                        <?php else: ?>
                                                            ml-2
                                                        <?php endif; ?>
                                                    "
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- child menu -->
                                    <?php if ($hasChildren): ?>
                                        <div
                                            class="tw-dropdown-menu py-1"
                                            data-control="sidenav"
                                            data-menu-code="<?= $itemFullCode; ?>"
                                            data-active-class="active"
                                        >
                                            <?php foreach ($item->sideMenu as $child): ?>
                                                <?php $childIsActive = BackendMenu::isSideMenuItemActive($child); ?>
                                                <a
                                                    href="<?= $child->url === 'javascript:;' ? "$item->url#menu-item-{$itemFullCode}-child-{$child->code}" : $child->url ?>"
                                                    <?php if ($child->url === 'javascript:;'): ?>
                                                        data-menu-item="<?= $child->code ?>"
                                                    <?php endif; ?>
                                                    class="
                                                        group flex relative items-center px-4 py-2 text-sm hover:no-underline transition duration-300 ease-in
                                                        text-gray-700 hover:bg-gray-100 hover:text-gray-900
                                                        dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700
                                                        <?= $childIsActive ? 'active' : '' ?>
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
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif ?>
                                </div>
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
</nav>
