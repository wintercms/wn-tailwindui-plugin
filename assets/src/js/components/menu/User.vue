<template>
<!--    <a
        href="javascript:;" onclick="$.wn.layout.toggleAccountMenu(this)"
        title="<?= e(trans('backend::lang.account.signed_in_as', ['full_name' => $this->user->full_name])) ?>">
        <img
            src="<?= $this->user->getAvatarThumb(90, ['mode' => 'crop', 'extension' => 'png']) ?>"
            class="account-avatar" loading="lazy" width="90" height="90" />
    </a>
    <div class="mainmenu-accountmenu">
        <ul>
            <?php foreach ($mySettings as $category => $items): ?>
            <?php foreach ($items as $item): ?>
            <li>
                <a href="<?= $item->url ?>">
                    <?= e(trans($item->label)) ?>
                </a>
            </li>
            <?php endforeach ?>
            <li class="divider"></li>
            <?php endforeach ?>

            <li>
                <a href="<?= Backend::url('backend/auth/signout') ?>">
                    <?php if (\BackendAuth::isImpersonator()): ?>
                    <?= e(trans('backend::lang.account.stop_impersonating')) ?>
                    <?php else: ?>
                    <?= e(trans('backend::lang.account.sign_out')) ?>
                    <?php endif; ?>
                </a>
            </li>
        </ul>-->

        <a href="javascript:void(0);">
            <img :src="user.icon" class="rounded-full w-[45px]">
        </a>
</template>
<script>
export default {
    props: ['user'],
}
</script>
