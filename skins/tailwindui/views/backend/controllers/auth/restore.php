<?= Form::open(['class' => 'space-y-6']) ?>
    <input type="hidden" name="postback" value="1" />

    <!-- Login -->
    <div>
        <label for="login" class="block text-sm font-medium text-gray-700">
            <?= e(trans('backend::lang.account.enter_login')) ?>
        </label>
        <div class="mt-1 relative">
            <input
                type="text"
                id="login"
                name="login"
                value="<?= e(post('login')) ?>"
                required
                autocomplete="email"
                maxlength="255" />

            <span class="group absolute right-0 inset-y-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <!-- Submit Login -->
        <div>
            <button type="submit" class="btn btn-primary w-full">
                <?= e(trans('backend::lang.account.restore')) ?>
            </button>
        </div>

        <div class="text-sm">
            <a href="<?= Backend::url('backend/auth') ?>" class="font-medium text-primary hover:text-primary-dark">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </a>
        </div>
    </div>

<?= Form::close() ?>

<?= $this->fireViewEvent('backend.auth.extendRestoreView') ?>
