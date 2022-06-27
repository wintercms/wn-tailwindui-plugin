<?php namespace Winter\TailwindUI\Skins;

use Backend\Skins\Standard as BackendSkin;

/**
 * TailwindUI custom backend skin
 */
class TailwindUI extends BackendSkin
{
    /**
     * {@inheritDoc}
     */
    public function getLayoutPaths()
    {
        return [
            plugins_path('/winter/tailwindui/skins/tailwindui/layouts'),
            $this->skinPath . '/layouts'
        ];
    }
}