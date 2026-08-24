<!-- mobile menu -->
<div
    id="layout-mobile-menu"
    data-control="mobile-menu-panel"
    class="hidden md:hidden bg-gray-800"
>
    <div class="after:px-2 pt-2 pb-3 space-y-1">
        <!-- Header search - mobile menu -->
        <!-- @TODO unhide when impemented -->
        <div class="px-3 hidden">
            <?= $this->makeLayoutPartial('partials/menu/header-search'); ?>
        </div>

        <!-- menu items -->
        <?php foreach (BackendMenu::listMainMenuItems() as $item): ?>
            <?php $isActive = BackendMenu::isMainMenuItemActive($item); ?>
            <?php $hasChildren = (bool) count($item->sideMenu); ?>
            <?php $itemFullCode = $item->owner . '.' . $item->code; ?>
            <div class="
                flex items-center w-full
                <?php if ($isActive) : ?>
                    bg-gray-900 text-white
                <?php else: ?>
                    text-gray-300 hover:bg-gray-700 hover:text-white
                <?php endif; ?>
            ">
                <a
                    href="<?= $item->url ?>"
                    class="
                        flex grow items-center px-6 py-2 text-base font-medium
                    "
                    <?php if ($isActive) : ?>
                        aria-current="page"
                    <?php endif; ?>
                >
                    <?php if ($iconLocation !== 'hidden') : ?>
                        <?php if ($item->iconSvg): ?>
                            <img
                                src="<?= Url::asset($item->iconSvg) ?>"
                                class="svg-icon w-4 h-4 mr-3"
                                loading="lazy"
                            >
                        <?php else: ?>
                            <i class="<?= $item->icon ?> mr-3 h-4 w-4"></i>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?= e(trans($item->label)) ?>
                </a>

                <?php if ($hasChildren): ?>
                    <button
                        class="text-white px-6 py-2 <?= $isActive ? 'open' : ''?>"
                        data-toggle="collapse"
                        data-target="#<?= $item->code ?>"
                        aria-expanded="<?= $isActive ? 'true' : 'false' ?>"
                        aria-controls="<?= $item->code ?>"
                    >
                        <svg
                            viewBox="0 0 20 20"
                            aria-hidden="true"
                            class="w-6 h-6 pointer-events-none"
                        >
                            <path
                                d="M6 6L14 10L6 14V6Z"
                                fill="currentColor"
                            />
                        </svg>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Child menu -->
            <?php if ($hasChildren) : ?>
                <nav
                    class="mt-1 collapsible<?= $isActive ? ' show' : '' ?>"
                    id="<?= $item->code ?>"
                    data-menu-code="<?= $item->owner . '.' . $item->code; ?>"
                    data-control="sidenav"
                    data-active-class="active"
                >
                    <ul class="list-none px-6 space-y-1">
                        <?php
                            foreach ($item->sideMenu as $child):
                                $sideMenuIsActive = BackendMenu::isSideMenuItemActive($child);
                                $iconDefaultClass = "$child->icon mr-3 h-4 w-4";
                                $iconClass = $sideMenuIsActive ? 'text-white group-hover:text-white' : 'text-gray-200 group-hover:text-white group-hover:bg-transparent';

                                // Honour the registered attributes (data-menu-item,
                                // data-builder-command, data-no-side-panel) like the core
                                // sidenav, and build the deep-link hash from the real
                                // data-menu-item value. See side/_item-contents.php.
                                $childMenuItem = $child->attributes['data-menu-item'] ?? $child->code;
                                $childIsJsNav = in_array($child->url, ['javascript:;', '#'], true);
                                $childHasPane = $childIsJsNav && empty($child->attributes['data-no-side-panel']);
                                $childHref = $childHasPane
                                    ? "{$item->url}#menu-item-{$itemFullCode}-child-{$childMenuItem}"
                                    : $child->url;
                        ?>
                            <li
                                class="<?= $sideMenuIsActive ? 'active' : '' ?>"
                                <?= Html::attributes($child->attributes) ?>
                            >
                                <a
                                    href="<?= $childHref ?>"
                                    class="
                                        group w-full flex items-center py-1.5
                                        text-sm text-white font-medium rounded-md hover:text-white
                                        hover:no-underline hover:bg-gray-700 focus:no-underline
                                        focus:text-white
                                        pl-6 pr-2
                                    "
                                >
                                    <?php if (!$child->iconSvg && $iconLocation !== 'hidden'): ?>
                                        <i
                                            class="<?= $iconDefaultClass ?> <?= $iconClass ?>"
                                        >
                                        </i>
                                    <?php elseif ($child->iconSvg && $iconLocation !== 'hidden'): ?>
                                        <img
                                            src="<?= Url::asset($child->iconSvg) ?>"
                                            class="svg-icon w-4 h-4"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                    <span class="whitespace-nowrap">
                                        <?= e(trans($child->label)) ?>
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>
