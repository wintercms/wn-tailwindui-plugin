<!DOCTYPE html>
<html lang="<?= App::getLocale() ?>" class="no-js">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0">
        <meta name="robots" content="noindex">
        <meta name="apple-mobile-web-app-capable" content="yes">
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
            // Temporarily disable loading of default backend styling files
            $styles = [];
        ?>

        <?php foreach ($styles as $style) : ?>
            <link href="<?= $style . '?v=' . $coreBuild; ?>" rel="stylesheet" importance="high">
            <link href="<?= $style . '?v=' . $coreBuild; ?>" rel="preload" as="style" importance="high">
        <?php endforeach; ?>

        <?php foreach ($scripts as $script) : ?>
            <script data-cfasync="false" src="<?= $script . '?v=' . $coreBuild; ?>" importance="high"></script>
            <link href="<?= $script . '?v=' . $coreBuild; ?>" rel="preload" as="script" importance="high">
        <?php endforeach; ?>

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

        <!--
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Inter var'],
                        },
                    },
                },
                darkMode: 'media'
            }
        </script>
        -->
        <link rel="stylesheet" href="<?= Url::asset('/plugins/winter/tailwindui/assets/css/dist/backend.css'); ?>">


        <?php
            // TEMPORARY FOR FLASH & LOADER STYLING
            $this->addCss('/modules/system/assets/css/framework.extras.css');
        ?>

        <?= $this->makeAssets() ?>
        <?= Block::placeholder('head') ?>
        <?= $this->makeLayoutPartial('custom_styles') ?>
        <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'auth']) ?>
    </head>
    <body class="outer <?= $this->bodyClass ?> font-sans m-0">
        <div class="min-h-screen bg-white flex">
            <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <div>
                        <img class="max-h-20 m-auto" src="<?= e(Backend\Models\BrandSetting::getLogo()); ?>" alt="<?= e(Backend\Models\BrandSetting::get('app_name')); ?>">
                        <h2 class="mt-6 text-2xl text-center font-extrabold text-gray-900"><?= e(Backend\Models\BrandSetting::get('app_tagline')); ?></h2>
                    </div>

                    <div class="mt-8">
                        <div class="mt-6">
                            <?= Block::placeholder('body') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block relative w-0 flex-1">
                <img class="absolute inset-0 h-full w-full object-cover" src="<?= e(Backend\Models\BrandSetting::instance()->backgroundImage->path); ?>" alt="">
            </div>
        </div>

        <!-- Flash Messages -->
        <style>
            div#layout-flash-messages + div {
                display: none;
            }
        </style>
        <div id="layout-flash-messages"><?= $this->makeLayoutPartial('flash_messages') ?></div>
    </body>
</html>
