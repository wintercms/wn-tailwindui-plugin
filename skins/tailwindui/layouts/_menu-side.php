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
                    flex-1 px-2 bg-gray-800 max-h-screen
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
                        src="<?= e($logoImage) ?: Url::asset('modules/backend/assets/images/winter-logo-white.svg') ?>"
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
<script>
    /*
     * Pre-paint side-menu deep-link selection.
     *
     * JS-driven sub-menu children (see partials/menu/side/_item-contents.php)
     * link to "<parent-url>#menu-item-<owner.code>-child-<child-code>" so the
     * intended child can be re-selected after a full page load. That re-selection
     * runs from winter.sidepaneltab.js, which is bundled into the deferred app.js
     * module and executes ~200ms after the page is interactive — long enough that
     * the server's default child stays highlighted and then visibly flips to the
     * real target (the "flash" of the wrong menu item).
     *
     * Applying the active state here, synchronously, before the first paint kills
     * that flash. winter.sidepaneltab.js still runs afterwards to open the side
     * panel, switch the tab and clear the hash; re-applying the same active state
     * is idempotent. This only ships with the side menu (default.php omits this
     * partial in "top" menu mode), so it never touches the top-menu layout.
     */
    (function () {
        var prefix = '#menu-item-';
        var hash = window.location.hash;
        if (hash.lastIndexOf(prefix, 0) !== 0) {
            return;
        }
        // Match the hash against each side-nav's own known menu code rather than
        // splitting on "-child-", so a parent code that itself contains dashes or
        // a literal "-child-" still resolves to the correct navigation element.
        var navs = document.querySelectorAll('[data-control="sidenav"][data-menu-code]');
        for (var n = 0; n < navs.length; n++) {
            var expected = prefix + navs[n].getAttribute('data-menu-code') + '-child-';
            if (hash.lastIndexOf(expected, 0) !== 0) {
                continue;
            }
            var childCode = hash.substring(expected.length);
            var items = navs[n].querySelectorAll('li[data-menu-item]');
            for (var i = 0; i < items.length; i++) {
                items[i].classList.toggle('active', items[i].getAttribute('data-menu-item') === childCode);
            }
            break;
        }
    })();
</script>
