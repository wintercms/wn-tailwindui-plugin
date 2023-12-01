<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0, minimal-ui">
<meta name="robots" content="noindex">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="app-timezone" content="<?= e(Config::get('app.timezone')) ?>">
<meta name="backend-base-path" content="<?= Backend::baseUrl() ?>">
<meta name="backend-timezone" content="<?= e(Backend\Models\Preference::get('timezone')) ?>">
<meta name="backend-locale" content="<?= e(Backend\Models\Preference::get('locale')) ?>">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<link rel="icon" type="image/png" href="<?= e(Backend\Models\BrandSetting::getFavicon()) ?>">
<title data-title-template="<?= empty($this->pageTitleTemplate) ? '%s' : e($this->pageTitleTemplate) ?> | <?= e(Backend\Models\BrandSetting::get('app_name')) ?>">
    <?= e(trans($this->pageTitle)) ?> | <?= e(Backend\Models\BrandSetting::get('app_name')) ?>
</title>
<?php
$coreBuild = System\Models\Parameter::get('system::core.build', 1);

// Styles
$styles = [
    Url::asset('plugins/winter/tailwindui/assets/dist/css/backend.css'),
    Url::asset('plugins/winter/tailwindui/assets/dist/css/winter.css'),
    Url::asset('modules/system/assets/ui/icons.css'),
];

// Scripts
$scripts = [
    Backend::skinAsset('assets/js/vendor/jquery.min.js'),
    Backend::skinAsset('assets/js/vendor/jquery-migrate.min.js'),
    Url::asset('modules/system/assets/js/framework.js'),
    Url::asset('modules/system/assets/js/build/manifest.js'),
    Url::asset('modules/system/assets/js/snowboard/build/snowboard.vendor.js'),
    Url::asset(
        (Config::get('develop.debugSnowboard', false) === true)
            ? 'modules/system/assets/js/build/system.debug.js'
            : 'modules/system/assets/js/build/system.js'
    ),
    Url::asset('modules/backend/assets/ui/js/build/manifest.js'),
    Url::asset('modules/backend/assets/ui/js/build/vendor.js'),
    Url::asset('modules/backend/assets/ui/js/build/backend.js'),
];
if (Config::get('develop.decompileBackendAssets', false)) {
    $scripts = array_merge($scripts, Backend::decompileAsset('modules/system/assets/ui/storm.js'));
    $scripts = array_merge($scripts, Backend::decompileAsset('assets/js/winter.js', true));
} else {
    $scripts = array_merge($scripts, [Url::asset('modules/system/assets/ui/storm-min.js')]);
    $scripts = array_merge($scripts, [Backend::skinAsset('assets/js/winter-min.js')]);
}
$scripts = array_merge($scripts, [
    Url::asset('modules/system/assets/js/lang/lang.'.App::getLocale().'.js'),
    Backend::skinAsset('assets/js/winter.flyout.js'),
    Backend::skinAsset('assets/js/winter.tabformexpandcontrols.js'),
]);
?>

<?php foreach ($styles as $style): ?>
    <link href="<?= $style . '?v=' . $coreBuild; ?>" rel="stylesheet" importance="high">
    <link href="<?= $style . '?v=' . $coreBuild; ?>" rel="preload" as="style" importance="high">
<?php endforeach; ?>

<?php foreach ($scripts as $script): ?>
    <script data-cfasync="false" src="<?= $script . '?v=' . $coreBuild; ?>" importance="high"></script>
    <link href="<?= $script . '?v=' . $coreBuild; ?>" rel="preload" as="script" importance="high">
<?php endforeach; ?>

<?php // @TODO: remove ?>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
<link href="<?= Url::asset('plugins/winter/tailwindui/assets/dist/css/backend.css') ?>" rel="stylesheet" importance="high">

<?php if (!Config::get('cms.enableBackendServiceWorkers', false)): ?>
    <script>
        "use strict";
        /* Only run on HTTPS connections
         * Block off Front-end Service Worker from running in the Backend allowing security injections, see GitHub #4384
         */
        if (location.protocol === 'https:') {
            // Unregister all service workers before signing in to prevent cache issues, see github issue: #3707
            navigator.serviceWorker.getRegistrations().then(
                function (registrations) {
                    registrations.forEach(function (registration) {
                        registration.unregister();
                    });
                }
            );
        }
    </script>
<?php endif; ?>

<?= $this->makeAssets() ?>
<?= Block::placeholder('head') ?>
<?= $this->makeLayoutPartial('custom_styles') ?>
