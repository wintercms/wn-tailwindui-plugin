<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">
<meta name="robots" content="noindex">
<meta name="mobile-web-app-capable" content="yes">
<meta name="backend-base-path" content="<?= Backend::baseUrl() ?>">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<link rel="icon" type="image/png" href="<?= e(Backend\Models\BrandSetting::getFavicon()) ?>">
<title><?= e(trans('backend::lang.auth.title')) ?></title>

<?php
    $coreBuild = System\Models\Parameter::get('system::core.build', 1);
    $styles = [
        Url::asset('modules/system/assets/ui/storm.css'),
        Backend::skinAsset('assets/css/winter.css'),
    ];
    // Temporarily disable loading of default backend styling files
    $styles = [];
    foreach ($styles as $style) {
        $this->addCss($style, [
            'build' => 'core',
            'order' => 1,
        ]);
    }
    $scripts = [
        Backend::skinAsset('assets/js/vendor/jquery.min.js'),
        Backend::skinAsset('assets/js/vendor/jquery-migrate.min.js'),
        Url::asset('modules/system/assets/js/framework.js'),
        Url::asset('modules/system/assets/js/framework.extras.js'),
        Url::asset('modules/system/assets/ui/storm-min.js'),
        Backend::skinAsset('assets/js/winter-min.js'),
        Url::asset('modules/backend/assets/js/auth/auth.js'),
        Url::asset('modules/system/assets/js/lang/lang.'.App::getLocale().'.js'),
    ];
    foreach ($scripts as $script) {
        $this->addJs($script, [
            'build' => 'core',
            'order' => 1,
        ]);
    }  
?>

<?php if (!Config::get('cms.enableBackendServiceWorkers', false)) : ?>
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

<link rel="stylesheet" href="https://rsms.me/inter/inter.css">
<link rel="stylesheet" href="<?= Url::asset('/plugins/winter/tailwindui/assets/css/dist/backend.css'); ?>">

<?php
    // TEMPORARY FOR FLASH & LOADER STYLING
    $this->addCss('/modules/system/assets/css/framework.extras.css');
?>

<?= $this->makeAssets() ?>
<?= Block::placeholder('head') ?>
<?= $this->makeLayoutPartial('branding') ?>
<?= $this->makeLayoutPartial('custom_styles') ?>
