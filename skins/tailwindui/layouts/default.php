<!DOCTYPE html>
<html lang="<?= App::getLocale() ?>" class="no-js <?= $this->makeLayoutPartial('browser_detector') ?>" data-color-scheme="<?= e(\Backend\Models\Preference::instance()->get('dark_mode', 'light')); ?>">
    <head>
        <?= $this->makeLayoutPartial('head') ?>
        <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'default']) ?>
    </head>
    <body class="relative <?= $this->bodyClass ?>">
        <?php
            $menuLocation = \Backend\Models\BrandSetting::get('menu_location');
            $iconLocation = \Backend\Models\BrandSetting::get('icon_location');
            $currentMainMenuItem = \BackendMenu::getActiveMainMenuItem();
        ?>

        <?= $this->makeLayoutPartial('partials/notices/impersonation') ?>

        <div class="default-layout default-layout-<?= $menuLocation ?> default-layout-<?= $menuLocation ?>-<?= $iconLocation ?>">
            <?php if ($menuLocation === 'top') : ?>
                <!-- Top Menu - top mode -->
                <div id="vue-app-1" class="layout-topmenu sticky top-0 z-topmenu">
                    <?= $this->makeLayoutPartial('menu-top') ?>
                </div>
            <?php endif; ?>

            <?php if ($menuLocation === 'side') : ?>
                <!-- Side Menu -->
                <?= $this->makeLayoutPartial('menu-side') ?>
            <?php endif; ?>

            <!-- Content Body -->
            <div class="layout-content relative">

                <!-- Top Menu - side mode -->
                <?php if ($menuLocation === 'side') : ?>
                    <div id="vue-app-1" class="layout-topmenu sticky top-0 z-sidemenu">
                        <?= $this->makeLayoutPartial('menu-top') ?>
                    </div>
                <?php endif; ?>

                <div class="layout">
                    <div class="layout-row relative">
                        <!-- Context menu -->
                        <?= $this->makeLayoutPartial('context-sidenav') ?>

                        <!-- Side panel -->
                        <?php if ($sidePanelContent = Block::placeholder('sidepanel')) : ?>
                            <div
                                id="layout-side-panel"
                                class="layout-cell w-350 hide-on-small"
                                data-control="layout-sidepanel"
                                data-menu-code="<?= $currentMainMenuItem->owner . '.' . $currentMainMenuItem->code; ?>"
                            >
                                <?= $sidePanelContent ?>
                            </div>
                        <?php endif ?>

                        <div class="layout-cell w-full layout-container" id="layout-body">
                            <div class="layout">
                                <?php if ($breadcrumbContent = Block::placeholder('breadcrumb')) : ?>
                                    <!-- Breadcrumb -->
                                    <div class="control-breadcrumb">
                                        <?= $breadcrumbContent ?>
                                    </div>
                                <?php endif ?>

                                <!-- Content -->
                                <div class="layout-row">
                                    <div class="layout-cell">
                                        <?= Block::placeholder('body') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <?= $this->makeLayoutPartial('flash-messages') ?>

        <?php if (config('winter.tailwindui::show_breakpoint_debugger', false)) : ?>
            <?= $this->makeLayoutPartial('breakpoint-debugger') ?>
        <?php endif; ?>

        <?php
            $jsLastModified = filemtime(plugins_path('winter/tailwindui/assets/js/dist/app.js'));
        ?>
        <script src="<?= Url::asset('/plugins/winter/tailwindui/assets/js/dist/app.js') ?>?v=<?= $jsLastModified ?>"></script>
        <link rel="stylesheet" href="<?= Url::asset('/modules/system/assets/css/snowboard.extras.css') ?>">
    </body>
</html>
