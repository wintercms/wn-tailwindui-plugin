<!DOCTYPE html>
<html lang="<?= App::getLocale() ?>" class="no-js h-full bg-gray-50" data-color-scheme="<?= e(\Backend\Models\Preference::instance()->get('dark_mode', 'light')); ?>">
    <head>
        <?= $this->makeLayoutPartial('head_auth') ?>
        <?= $this->fireViewEvent('backend.layout.extendHead', ['layout' => 'auth']) ?>
    </head>
    <body class="<?= $this->bodyClass ?> font-sans m-0 h-full bg-cover bg-no-repeat bg-center" style="background-image: url('<?= e(config('brand.backgroundImage')); ?>');">
        <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
            <div class="mx-auto sm:w-full sm:max-w-md sm:max-w-sm">
                <img class="max-h-20 m-auto" src="<?= e(Backend\Models\BrandSetting::getLogo()); ?>" alt="<?= e(Backend\Models\BrandSetting::get('app_name')); ?>">
                <h2 class="mt-6 text-2xl text-center font-medium text-gray-900"><?= e(Backend\Models\BrandSetting::get('app_tagline')); ?></h2>
            </div>

            <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md sm:max-w-sm">
              <div class="bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                    <?= Block::placeholder('body') ?>
              </div>
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
