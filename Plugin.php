<?php namespace Winter\TailwindUI;

use Backend\Classes\BackendController as CoreBackendController;
use Backend\Classes\Controller as BaseBackendController;
use Backend\Classes\WidgetBase;
use Backend\Controllers\Auth as AuthController;
use Backend\Controllers\Preferences as PreferencesController;
use Backend\Models\BrandSetting;
use Backend\Models\Preference as PreferenceModel;
use Backend\Models\UserRole;
use BackendAuth;
use Config;
use Event;
use Request;
use System\Classes\PluginBase;
use System\Controllers\Settings as SettingsController;
use Url;
use Winter\Storm\Support\Str;
use Yaml;

/**
 * TailwindUI Plugin Information File
 */
class Plugin extends PluginBase
{
    public $elevated = true;

    /**
     * Returns information about this plugin.
     */
    public function pluginDetails(): array
    {
        return [
            'name'        => 'winter.tailwindui::lang.plugin.name',
            'description' => 'winter.tailwindui::lang.plugin.description',
            'author'      => 'Winter CMS',
            'icon'        => 'icon-leaf',
        ];
    }

    /**
     * Returns the permissions provided by this plugin
     */
    public function registerPermissions(): array
    {
        return [
            'winter.tailwindui.manage_own_appearance.dark_mode' => [
                'label' => 'winter.tailwindui::lang.permissions.manage_appearance.dark_mode',
                'tab' => 'winter.tailwindui::lang.plugin.name',
                'roles' => [UserRole::CODE_PUBLISHER, UserRole::CODE_DEVELOPER],
            ],
            'winter.tailwindui.manage_own_appearance.menu_location' => [
                'label' => 'winter.tailwindui::lang.permissions.manage_appearance.menu_location',
                'tab' => 'winter.tailwindui::lang.plugin.name',
                'roles' => [UserRole::CODE_PUBLISHER, UserRole::CODE_DEVELOPER],
            ],
            'winter.tailwindui.manage_own_appearance.item_location' => [
                'label' => 'winter.tailwindui::lang.permissions.manage_appearance.item_location',
                'tab' => 'winter.tailwindui::lang.plugin.name',
                'roles' => [UserRole::CODE_PUBLISHER, UserRole::CODE_DEVELOPER],
            ],
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
            $this->extendBackendWidgets();
            $this->extendBrandSettingsForm();
            $this->extendBackendAuthController();
        }
    }

    /**
     * Guess the override view path for the provided object
     */
    protected function guessOverrideViewPath(object $object): string
    {
        $path = strtolower(get_class($object));
        $cleanPath = str_replace("\\", "/", $path);
        if (!in_array(Str::before($cleanPath, '/'), ['backend', 'cms', 'system'])) {
            $cleanPath = 'plugins/' . $cleanPath;
        }
        return '$/winter/tailwindui/skins/tailwindui/views/' . $cleanPath;
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
            $controller->addViewPath($this->guessOverrideViewPath($controller));

            // @TODO: Handle cache busting through some other method
            $cssLastModified = filemtime(plugins_path('winter/tailwindui/assets/css/dist/backend.css'));

            $controller->addCss(Url::asset('/plugins/winter/tailwindui/assets/css/dist/backend.css'), (string) $cssLastModified);

            $this->extendBrandSettingsData();

            $controller->addDynamicMethod('onTailwindUISetTheme', function () {
                $user = BackendAuth::user();
                if (!$user || !$user->hasAccess('winter.tailwindui.manage_own_appearance.dark_mode')) {
                    abort(403, "You do not have permission to do that.");
                }

                $darkMode = post('dark_mode');
                $prefs = PreferenceModel::instance();
                $prefs->set('dark_mode', $darkMode);
                return [
                    'dark_mode' => $darkMode,
                ];
            });
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
     * Extends backend widgets to use the custom views provided by this plugin
     */
    protected function extendBackendWidgets(): void
    {
        WidgetBase::extend(function ($widget) {
            // @TODO: BlogMarkdown and all ML overrides break this
            $widget->addViewPath($this->guessOverrideViewPath($widget) . '/partials');
        });
    }

    /**
     * Populate brand settings data with initial values if not set
     */
    protected function extendBrandSettingsData(): void
    {
        $settings = BrandSetting::instance();
        $userSettings = (!is_null(BackendAuth::user()))
            ? PreferenceModel::instance()
            : null;

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

        // Set the default values for the User Preferences
        if ($userSettings) {
            $applyDefaults = [
                'dark_mode',
            ];
            $preferenceDefaults = [];
            $preferenceFields = Yaml::parseFile(plugins_path('winter/tailwindui/models/preference/fields.yaml'));
            if (!empty($preferenceFields['tabs'])) {
                foreach ($preferenceFields['tabs']['fields'] as $name => $config) {
                    if (isset($config['default']) && in_array($name, $applyDefaults)) {
                        $preferenceDefaults[$name] = $config['default'];
                    }
                }
            }

            if (!empty($preferenceDefaults)) {
                foreach ($preferenceDefaults as $name => $default) {
                    // Check the current user for an overridden preference
                    $userValue = $userSettings->get($name);

                    if (empty($userSettings->get($name))) {
                        $userSettings->setSettingsValue($name, $this->app->config->get("brand.$name", $default));
                        $userSettings->attributes[$name] = $this->app->config->get("brand.$name", $default);
                    }
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

            $fields = [
                BrandSetting::class => Yaml::parseFile(plugins_path('winter/tailwindui/models/brandsetting/fields.yaml')),
                PreferenceModel::class => Yaml::parseFile(plugins_path('winter/tailwindui/models/preference/fields.yaml')),
            ];

            if (!empty($fields[get_class($form->model)]['tabs'])) {
                $form->addTabFields($fields[get_class($form->model)]['tabs']['fields']);
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
