<?php if (\BackendAuth::isImpersonator()) : ?>
    <style>
        /**
            - @TODO:
            - Luke fix this, needs to go into the winter.css source LESS file and be recompiled
            - https://github.com/wintercms/winter/commit/857d42101e1955cd0cd8933360796bd4a802a5c0
            */
        .global-notice{position:sticky;top:0;display:flex;align-items:center;flex-wrap:wrap;gap:0.5em;justify-content:space-between;z-index:10500;background:#ab2a1c;color:#FFF;padding:0.5em 0.75em}
        .global-notice .notice-icon{font-size:1.5em;vertical-align:bottom;display:inline-block;margin-right:.25em}
        .global-notice .notice-text{display:inline-block;vertical-align:middle}
    </style>
    <div class="global-notice">
        <div class="notice-content">
            <span class="notice-text">
                <span class="notice-icon wn-icon icon-exclamation-triangle"></span>
                <?= e(trans('backend::lang.account.impersonating', [
                    'impersonator' => \BackendAuth::getImpersonator()->email,
                    'impersonatee' => \BackendAuth::getUser()->email,
                ])); ?>
            </span>
        </div>
        <a href="<?= Backend::url('backend/auth/signout') ?>" class="notice-action btn btn-secondary">
            <?= e(trans('backend::lang.account.stop_impersonating')) ?>
        </a>
    </div>
<?php endif; ?>
