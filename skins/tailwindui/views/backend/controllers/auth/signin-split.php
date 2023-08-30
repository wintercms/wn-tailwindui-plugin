<!-- Split   -->
<?= Form::open(['class' => 'space-y-6']) ?>
    <input type="hidden" name="postback" value="1" />

    <!-- Login -->
    <div>
        <label for="login">
            <?= e(trans('backend::lang.account.login')) ?>
        </label>
        <div class="mt-1 relative">
            <input
                type="text"
                id="login"
                name="login"
                value="<?= e(post('login')) ?>"
                required
                maxlength="255" />

            <span class="group absolute right-0 inset-y-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
        </div>
    </div>

    <!-- Password -->
    <div class="space-y-1">
        <label for="password">
            <?= e(trans('backend::lang.user.password')) ?>
        </label>
        <div class="mt-1 relative">
            <input
                type="password"
                id="password"
                name="password"
                value=""
                required
                autocomplete="current-password"
                maxlength="255" />

            <span class="group absolute right-0 inset-y-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <!-- Remember checkbox -->
        <?php if (is_null(config('cms.backendForceRemember', true))) : ?>
            <div class="flex items-center">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    style="border-radius: .375rem;"
                />

                <label for="remember" style="margin: 0 0 0 0.5em;">
                    <?= e(trans('backend::lang.account.remember_me')) ?>
                </label>
            </div>
        <?php endif; ?>

        <!-- Forgot your password? -->
        <div class="text-sm">
            <a href="<?= Backend::url('backend/auth/restore') ?>" class="font-medium text-primary hover:text-primary-dark">
                <?= e(trans('backend::lang.account.forgot_password')) ?>
            </a>
        </div>
    </div>

    <!-- Submit Login -->
    <div>
        <button type="submit" class="btn btn-primary w-full">
            <?= e(trans('backend::lang.account.login')) ?>
        </button>
    </div>
<?= Form::close() ?>

<?php $this->fireViewEvent('backend.auth.extendSigninView') ?>
