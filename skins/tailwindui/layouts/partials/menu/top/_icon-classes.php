<?php if ($item->iconSvg): ?>
    svg-icon
<?php else: ?>
    <?= $item->icon ?>
<?php endif; ?>
<?php if ($iconLocation === 'inline'): ?>
    inline-block mr-2
    <?php if ($item->iconSvg): ?>
        w-7.5 h-7.5
    <?php else: ?>
        icon-inline
    <?php endif; ?>
<?php endif; ?>
<?php if ($iconLocation === 'tile'): ?>
    block mx-auto mb-1
    <?php if ($item->iconSvg): ?>
        w-7.5 h-7.5
    <?php else: ?>
        icon-tile h-7.5
    <?php endif; ?>
<?php endif; ?>
<?php if ($iconLocation === 'only'): ?>
    block mx-auto
    <?php if ($item->iconSvg): ?>
        w-7.5 h-7.5
    <?php else: ?>
        icon-only
    <?php endif; ?>
<?php endif; ?>
