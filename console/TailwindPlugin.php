<?php

namespace Winter\TailwindUI\Console;

use File;
use Illuminate\Console\Command;
use System\Classes\PluginManager;
use Illuminate\Support\Facades\Artisan;

class TailwindPlugin extends Command
{
    /**
     * @var string|null The default command name for lazy loading.
     */
    protected static $defaultName = 'tailwind:plugin';

    /**
     * @var string The name and signature of this command.
     */
    protected $signature = 'tailwind:plugin
        {plugin : Defines plugin to compile tailwind assets}
        {webpackArgs?* : Arguments to pass through to the Webpack CLI}';

    /**
     * @var string The console command description.
     */
    protected $description = 'Compile plugin tailwind css';

    protected ?string $config = null;
    protected ?string $original = null;
    protected ?string $backendCss = null;

    public function __construct()
    {
        parent::__construct();

        $dir = plugins_path('winter/tailwindui');

        $this->config = $dir . '/tailwind.config.js';
        $this->original = $dir . '/tailwind.original.config.js';
        $this->backendCss = $dir . '/assets/css/dist/backend.css';
    }

    public function __destruct()
    {
        if (File::exists($this->config) && File::exists($this->original)) {
            File::delete($this->config);
            File::move($this->original, $this->config);
        }

        if (File::exists($this->backendCss) && File::exists($this->backendCss . '.original')) {
            File::delete($this->backendCss);
            File::move($this->backendCss . '.original', $this->backendCss);
        }
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function handle(): int
    {
        $plugin = PluginManager::instance()->findByIdentifier($this->argument('plugin'));

        if (!$plugin) {
            throw new \InvalidArgumentException(sprintf('Plugin `%s` not found', $this->argument('plugin')));
        }

        $pluginConfig = $plugin->getPluginPath() . '/tailwind.config.js';

        if (!File::exists($pluginConfig)) {
            throw new \RuntimeException('Unable to locate plugin tailwind config');
        }

        File::move($this->config, $this->original);

        $this->copyFileWithRelativePathResolution($pluginConfig, $this->config);

        File::copy($this->backendCss, $this->backendCss . '.original');

        $this->mix();

        // find all css rules in the original
        $data = $this->diff(File::get($this->backendCss . '.original'), File::get($this->backendCss));

        $out = $plugin->getPluginPath() . '/assets/dist/css';

        if (!File::exists($out)) {
            File::makeDirectory($out);
        }

        File::put($out . '/backend.css', $data);

        return 0;
    }

    public function copyFileWithRelativePathResolution(string $file, string $path): bool
    {
        $data = File::get($file);

        // replace ./ paths
        $data = preg_replace('/(\s|\(|\[)?("|\')?\.\//', '$1$2' . dirname($file) . '/', $data);
        // replace root config import
        $data = preg_replace(
            '/(\'|")(.*?)\/winter\/tailwindui\/tailwind\.config\.js(\'|")/',
            '"./tailwind.original.config.js"',
            $data
        );

        return File::put($path, $data);
    }

    public function diff(string $a, string $b): string
    {
        preg_match_all('/((.*?){(.*?)})/', $a, $matches);
        $matches = $matches[1];

        foreach ($matches as $match) {
            $b = str_replace($match, '', $b);
        }

        return $b;
    }

    public function mix(): int
    {
        $webpackArgs = $this->argument('webpackArgs')
            ? ' -- ' . implode(' ', $this->argument('webpackArgs'))
            : '';

        return Artisan::call('mix:compile --package winter.tailwindui --production' . $webpackArgs);
    }
}
