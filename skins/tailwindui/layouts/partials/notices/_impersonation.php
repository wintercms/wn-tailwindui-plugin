<?php if (\BackendAuth::isImpersonator()) : ?>
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
