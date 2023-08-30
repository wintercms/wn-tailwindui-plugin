<!-- Simple   -->
<?= Form::open(['class' => 'space-y-6']) ?>
    <input type="hidden" name="postback" value="1" />

    <!-- Login -->
    <div class="rounded-md shadow-sm -space-y-px">
        <div>
            <label for="login" class="sr-only"><?= e(trans('backend::lang.account.login')) ?></label>
            <div class="relative">
                <input
                    type="text"
                    id="login"
                    name="login"
                    class="rounded-none rounded-t-md"
                    value="<?= e(post('login')) ?>"
                    required
                    autocomplete="email"
                    maxlength="255"
                    placeholder="Username" />

                <span class="group absolute right-0 inset-y-0 flex items-center pr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 group-focus:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </span>
            </div>
        </div>

        <div>
            <!-- Password -->
            <label for="password" class="sr-only"><?= e(trans('backend::lang.user.password')) ?></label>
            <div class="relative">
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="rounded-none rounded-b-md"
                    value=""
                    required
                    autocomplete="current-password"
                    maxlength="255"
                    placeholder="Password" />

                <span class="group absolute right-0 inset-y-0 flex items-center pr-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500 group-focus:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </span>
            </div>
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
        <button type="submit" class="btn btn-primary group relative w-full">
            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                <svg class="h-5 w-5 text-primary-lighter group-hover:text-primary-light" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                </svg>
            </span>
            <?= e(trans('backend::lang.account.login')) ?>
        </button>
    </div>
<?= Form::close() ?>

<?php $this->fireViewEvent('backend.auth.extendSigninView') ?>
