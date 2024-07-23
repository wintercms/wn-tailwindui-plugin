<?php
$activeItem = BackendMenu::getActiveMainMenuItem();
$mySettings = System\Classes\SettingsManager::instance()->listItems('mysettings');
$iconLocation = Backend\Models\BrandSetting::get('icon_location');
$appName = e(Backend\Models\BrandSetting::get('app_name'));

$logoImage = (in_array($iconLocation, ['only', 'tile']))
    ? Backend\Models\BrandSetting::getFavicon()
    : Backend\Models\BrandSetting::getLogo();

$context = BackendMenu::getContext();
$contextSidenav = BackendMenu::getContextSidenavPartial($context->owner, $context->mainMenuCode);

$menu = array_map(function ($menuItem) use ($context, $contextSidenav) {

    $menuItem->label = e(trans($menuItem->label));
    $menuItem->isActive = BackendMenu::isMainMenuItemActive($menuItem);
    $menuItem->iconSvg = $menuItem->iconSvg ? Url::asset($menuItem->iconSvg) : null;

    $menuItem->sideMenu = array_map(function ($item) {
        $item->label = e(trans($item->label));
        $item->active = BackendMenu::isSideMenuItemActive($item);
        $item->iconSvg = $item->iconSvg ? Url::asset($item->iconSvg) : null;
        return $item;
    }, $menuItem->sideMenu);

    return $menuItem;
}, BackendMenu::listMainMenuItems());

$quickActions = array_map(function ($action) {
    $action->label = e(trans($action->label));
    $action->isActive = BackendMenu::isMainMenuItemActive($action);
    $action->iconSvg = $action->iconSvg ? Url::asset($action->iconSvg) : null;
    $action->attributes = Html::attributes($action->attributes);
    return $action;
}, BackendMenu::listQuickActionItems());

$user = [
    'title' => e(trans('backend::lang.account.signed_in_as', ['full_name' => $this->user->full_name])),
    'icon' => $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']),
    'settings' => array_map(function ($item) {
        $item->label = e(trans($item->label));
        $item->description = e(trans($item->description));
        return $item;
    }, ...array_values(System\Classes\SettingsManager::instance()->listItems('mysettings')))
];

?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
        <?= $this->makeLayoutPartial('head') ?>
        <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'default']) ?>
    </head>
    <body>
        <div id="backend-ui">
            <backend logo="<?= e($logoImage) ?>"
                app-name="<?= $appName ?>"
                :menu='<?= json_encode($menu) ?>'
                :quick-actions='<?= json_encode($quickActions) ?>'
                :user='<?= json_encode($user) ?>'
            >
                <?php $flyoutContent = Block::placeholder('sidepanel-flyout') ?>

                <div class="layout-row bg-white rounded-lg shadow-lg">
                    <div class="layout flyout-container"
                        <?php if ($flyoutContent): ?>
                            data-control="flyout"
                            data-flyout-width="400"
                            data-flyout-toggle="#layout-sidenav"
                        <?php endif ?>
                    >
                        <?php if ($flyoutContent): ?>
                            <div class="layout-cell flyout"> <?= $flyoutContent ?></div>
                        <?php endif ?>

                        <!-- Side panel -->
                        <?php if ($sidePanelContent = Block::placeholder('sidepanel')): ?>
                            <div class="layout-cell w-350 hide-on-small" id="layout-side-panel" data-control="layout-sidepanel">
                                <?= $sidePanelContent ?>
                            </div>
                        <?php endif ?>

                        <!-- Content Body -->
                        <div class="layout-cell layout-container" id="layout-body">
                            <div class="layout-relative">
                                <?php if ($breadcrumbContent = Block::placeholder('breadcrumb')): ?>
                                    <!-- Breadcrumb -->
                                    <div class="control-breadcrumb relative">
                                        <?= $breadcrumbContent ?>
                                    </div>
                                <?php endif ?>

                                <div class="layout">
                                    <!-- Content -->
                                    <div class="layout-row">
                                        <?= Block::placeholder('body') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </backend>
        </div>
        <?= \System\Classes\Asset\Vite::tags(['assets/src/js/app.js'], 'winter.tailwindui') ?>
    </body>
</html>
