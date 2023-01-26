<!DOCTYPE html>
<?php
    $brandSettings = Backend\Models\BrandSetting::instance();
    $backgroundImage = Url::asset(Config::get('brand.backgroundImage'));

    if (isset($brandSettings->background_image)) {
        $backgroundImage = $brandSettings->background_image->path;
    }
?>
<html lang="<?= App::getLocale() ?>" class="no-js" data-color-scheme="<?= e(\Backend\Models\Preference::instance()->get('dark_mode', 'light')); ?>">
    <head>
        <?= $this->makeLayoutPartial('head_auth') ?>
        <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'auth']) ?>
    </head>

    <body class="outer <?= $this->bodyClass ?> font-sans m-0">
        <div class="min-h-screen bg-white dark:bg-gray-800 flex">
            <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <div>
                        <img class="max-h-20 m-auto" src="<?= e(Backend\Models\BrandSetting::getLogo()); ?>" alt="<?= e(Backend\Models\BrandSetting::get('app_name')); ?>">
                        <h2 class="mt-6 text-2xl text-center font-extrabold text-gray-900 dark:text-gray-300"><?= e(Backend\Models\BrandSetting::get('app_tagline')); ?></h2>
                    </div>

                    <div class="mt-8">
                        <div class="mt-6">
                            <?= Block::placeholder('body') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="hidden lg:block relative w-0 flex-1">
                <img class="absolute inset-0 h-full w-full object-cover" src="<?= e($backgroundImage); ?>" alt="">
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
