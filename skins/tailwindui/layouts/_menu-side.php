<?php
    $iconLocation = \Backend\Models\BrandSetting::get('icon_location');
    $mySettings = System\Classes\SettingsManager::instance()->listItems('mysettings');
    $appName = e(Backend\Models\BrandSetting::get('app_name'));

    if (in_array($iconLocation, ['only', 'tile'])) {
        $itemMode = 'tile';
        $logoImage = Backend\Models\BrandSetting::getFavicon();
    } else {
        $itemMode = 'inline';
        $logoImage = Backend\Models\BrandSetting::getLogo();
    }
?>
<div class="hidden md:block sidemenu layout-sidemenu layout-sidemenu-<?= $iconLocation ?> h-full print:hidden">
    <div
        class="flex flex-col fixed top-0 left-0 z-sidemenu border-r border-gray-700 pt-2 pb-4 bg-gray-800 h-full"
        id="layout-sidenav-2"
    >
        <div class="flex flex-col">
            <nav
                class="
                    flex-1 px-2 bg-gray-800 max-w-6 max-h-screen
                    <?= $itemMode === 'tile' ? 'space-y-2' : 'space-y-1' ?>
                    <?php if ($iconLocation !== 'tile' && $iconLocation !== 'only'): ?>
                        overflow-y-auto overflow-x-hidden
                    <?php endif; ?>
                "
                aria-label="Sidebar"
            >
                <!-- logo -->
                <div class="flex items-center mb-4 h-16 shrink-0">
                    <img
                        class="h-12 w-auto <?= $itemMode === 'tile' ? 'm-auto' : '' ?>"
                        src="<?= e($logoImage) ?>"
                        alt="<?= $appName ?>"
                    >
                </div>

                <!-- main items -->
                <?php foreach (BackendMenu::listMainMenuItems() as $item): ?>
                    <?php
                        $iconClass = [];
                        $isActive = BackendMenu::isMainMenuItemActive($item);

                        if ($item->iconSvg) {
                            array_push($iconClass, 'svg-icon');
                        }

                        if (!$item->iconSvg && $item->icon) {
                            array_push($iconClass, $item->icon);
                        }

                        if ($iconLocation === 'inline') {
                            array_push($iconClass, 'inline-block', 'mr-3', 'min-w-[1.25rem]', 'text-center');

                            if ($item->iconSvg) {
                                array_push($iconClass, 'w-5', 'h-5'); // 16px x 16px
                            }
                        }

                        if ($iconLocation === 'tile') {
                            array_push($iconClass, 'block', 'mx-auto');

                            if ($item->iconSvg) {
                                array_push($iconClass, 'w-6', 'h-6'); // 20px x 20px
                            }

                            if (!$item->iconSvg && $item->icon) {
                                array_push($iconClass, 'icon-tile');
                            }
                        }

                        if ($iconLocation === 'only') {
                            array_push($iconClass, 'block', 'mx-auto');

                            if ($item->iconSvg) {
                                array_push($iconClass, 'w-6', 'h-6'); // 24px x 24px
                            }

                            if (!$item->iconSvg && $item->icon) {
                                array_push($iconClass, 'icon-only');
                            }
                        }
                    ?>
                    <div class="sidemenu-item relative group">
                        <?= $this->makeLayoutPartial('partials/menu/side/item-contents', [
                            'item' => $item,
                            'itemMode' => $itemMode,
                            'isActive' => $isActive,
                            'iconLocation' => $iconLocation,
                            'iconClass' => $iconClass,
                        ]); ?>
                    </div>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>
</div>
