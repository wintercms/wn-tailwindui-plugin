<?php namespace Winter\TailwindUI;

use Url;
use Yaml;
use Event;
use Config;
use Request;
use BackendAuth;
use System\Classes\PluginBase;
use Backend\Models\BrandSetting;
use Backend\Classes\BackendController as CoreBackendController;
use Backend\Classes\Controller as BaseBackendController;
use Backend\Controllers\Auth as AuthController;
use System\Controllers\Settings as SettingsController;
use Backend\Controllers\Preferences as PreferencesController;
use Backend\Models\Preference as PreferenceModel;
use Winter\TailwindUI\Console\TailwindPlugin;

/**
 * TailwindUI Plugin Information File
 */
class Plugin extends PluginBase
{
    public $elevated = true;

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'winter.tailwindui::lang.plugin.name',
            'description' => 'winter.tailwindui::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-leaf',
        ];
    }

    /**
     * Boot method, called right before the request route.
     */
    public function boot()
    {
        // Only apply skin modifications to the backend context
        if ($this->app->runningInBackend()) {
            $this->applyBackendSkin();
            $this->extendBackendControllers();
            $this->extendBrandSettingsForm();
            $this->extendBackendAuthController();
        }

        $this->registerConsoleCommand('tailwindui.plugin', TailwindPlugin::class);
    }

    /**
     * Apply the TailwindUI backend skin as the selected backend skin
     */
    protected function applyBackendSkin()
    {
        Config::set('cms.backendSkin', \Winter\TailwindUI\Skins\TailwindUI::class);
        Config::set('brand.backgroundImage', '/plugins/winter/tailwindui/assets/images/background.jpg');

        // Set a default logo that will work with the default dark sidebar as a fallback
        // @TODO: add support for light / dark modes / variations of all primary branding (logo, favicon, colour scheme) and apply as necessary
        if (empty(Config::get('brand.logoPath'))) {
            Config::set('brand.logoPath', '~/modules/backend/assets/images/winter-logo-white.svg');
            // Config::set('brand.logoPath', '~/modules/backend/assets/images/winter-logo.svg');
        }
    }

    /**
     * Extend all backend controllers to inject view overrides and apply custom
     * brand settings data
     */
    protected function extendBackendControllers(): void
    {
        // Add our view override paths
        BaseBackendController::extend(function ($controller) {
            $path = strtolower(get_class($controller));
            $cleanPath = str_replace("\\", "/", $path);
            $controller->addViewPath('$/winter/tailwindui/skins/tailwindui/views/' . $cleanPath);

            // @TODO: Handle cache busting through some other method
            $cssLastModified = filemtime(plugins_path('winter/tailwindui/assets/css/dist/backend.css'));

            $controller->addCss(Url::asset('/plugins/winter/tailwindui/assets/css/dist/backend.css'), (string) $cssLastModified);

            $this->extendBrandSettingsData();
        });

        // Extend the Settings controller to force the page to reload after updating branding
        SettingsController::extend(function ($controller) {
            $controller->bindEvent('ajax.beforeRunHandler', function ($handler) use ($controller) {
                if ($handler === 'onSave' && !empty(Request::post('BrandSetting'))) {
                    $controller->update_onSave(...CoreBackendController::$params);
                    return redirect()->refresh();
                }
            });
        });

        // Extend the Preferences controller to force the page to reload after updating preferences
        PreferencesController::extend(function ($controller) {
            $controller->bindEvent('ajax.beforeRunHandler', function ($handler) use ($controller) {
                if ($handler === 'onSave' && !empty(Request::post('Preference'))) {
                    $controller->update_onSave(...CoreBackendController::$params);
                    return redirect()->refresh();
                }
            });
        });
    }

    /**
     * Populate brand settings data with initial values if not set
     */
    protected function extendBrandSettingsData(): void
    {
        // Initialize the backend branding data from the config if it's not set already
        $fieldDefaults = [];
        $fields = Yaml::parseFile(plugins_path('winter/tailwindui/models/brandsetting/fields.yaml'));
        if (!empty($fields['tabs'])) {
            foreach ($fields['tabs']['fields'] as $name => $config) {
                if (isset($config['default'])) {
                    $fieldDefaults[$name] = $config['default'];
                }
            }
        }

        if (!empty($fieldDefaults)) {
            $settings = BrandSetting::instance();
            $userSettings = (!is_null(BackendAuth::user()))
                ? PreferenceModel::instance()
                : null;
            foreach ($fieldDefaults as $name => $default) {
                // Check the current user for an overridden preference
                $userValue = (!is_null($userSettings)) ? $userSettings->get($name) : null;

                if (!empty($userValue)) {
                    $settings->setSettingsValue($name, $userValue);
                    $settings->attributes[$name] = $userValue;

                // Apply defaults from brand config if no value is set in the DB
                } elseif (empty($settings->getSettingsValue($name))) {
                    $settings->setSettingsValue($name, $this->app->config->get("brand.$name", $default));
                    $settings->attributes[$name] = $this->app->config->get("brand.$name", $default);
                }
            }
        }
    }

    /**
     * Extend the brand settings form to include the settings provided by this plugin
     */
    protected function extendBrandSettingsForm(): void
    {
        BrandSetting::extend(function($model) {
            $model->addAttachOneRelation('background_image', [\System\Models\File::class]);
        });

        Event::listen('backend.form.extendFields', function ($form) {
            // Only extend the desired form
            if (!(
                $form instanceof \Backend\Widgets\Form
                && !$form->isNested
                && (
                    (
                        $form->getController() instanceof SettingsController
                        && $form->model instanceof BrandSetting
                    )
                    || (
                        $form->getController() instanceof PreferencesController
                        && $form->model instanceof PreferenceModel
                    )
                )
            )) {
                return;
            }

            // Add the additional fields provided by this plugin
            $fields = Yaml::parseFile(plugins_path('winter/tailwindui/models/brandsetting/fields.yaml'));
            if (!empty($fields['tabs'])) {
                // Remove the auth layout options from the backend user preferences form
                if ($form->model instanceof PreferenceModel) {
                    unset($fields['tabs']['fields']['auth_layout']);
                    unset($fields['tabs']['fields']['background_image']);
                }
                $form->addTabFields($fields['tabs']['fields']);
            }

            // Remove fields that are no longer relevant
            $form->removeField('menu_mode');
        });
    }

    /**
     * Extend the backend auth controller to change the layout based on the
     * brand settings provided by this plugin
     */
    protected function extendBackendAuthController(): void
    {
        AuthController::extend(function ($controller) {
            $controller->bindEvent('page.beforeDisplay', function () use ($controller) {
                $authLayout = BrandSetting::get('auth_layout');
                $controller->layout = "auth-$authLayout";
            });
        });
    }
}
