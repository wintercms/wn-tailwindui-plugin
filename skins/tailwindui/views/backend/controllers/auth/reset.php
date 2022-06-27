<?= Form::open(['class' => 'space-y-6']) ?>
    <input type="hidden" name="postback" value="1" />
    <input type="hidden" name="id" value="<?= e($id) ?>" />
    <input type="hidden" name="code" value="<?= e($code) ?>" />

    <!-- Login -->
    <div>
        <label for="password">
            <?= e(trans('backend::lang.account.enter_new_password')) ?>
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

            <span class="absolute right-0 inset-y-0 flex items-center pr-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
            </span>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <!-- Submit Login -->
        <div>
            <button type="submit" class="btn btn-primary w-full">
                <?= e(trans('backend::lang.account.reset')) ?>
            </button>
        </div>

        <div class="text-sm">
            <a href="<?= Backend::url('backend/auth') ?>" class="font-medium text-primary hover:text-primary-dark">
                <?= e(trans('backend::lang.form.cancel')) ?>
            </a>
        </div>
    </div>

<?= Form::close() ?>
