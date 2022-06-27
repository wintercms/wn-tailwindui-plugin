<?php $flyoutContent = Block::placeholder('sidepanel-flyout') ?>

<?php if ($flyoutContent): ?>
    data-control="flyout"
    data-flyout-width="400"
    data-flyout-toggle="#layout-sidenav"
<?php endif ?>
>
<?php if ($flyoutContent): ?>
    <div class="layout-cell flyout"> <?= $flyoutContent ?></div>
<?php endif ?>
