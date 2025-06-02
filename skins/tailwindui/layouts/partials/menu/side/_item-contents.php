<?php $hasChildren = (bool) count($item->sideMenu); ?>
<div class="
    flex relative items-center w-full group rounded-md focus:outline-none
    focus:ring-2 focus:ring-primary transition duration-300 ease-in
    <?php if ($isActive) : ?>
        bg-primary text-white hover:bg-primary-dark
    <?php else: ?>
        text-gray-200 hover:bg-gray-700 hover:text-white focus:text-white
    <?php endif; ?>
">
    <a
        href="<?= $item->url ?>"
        class="
            flex flex-1 block items-center group text-sm font-medium group-hover:text-white focus:text-white active:text-white hover:no-underline focus:no-underline
            <?= in_array($iconLocation, ['tile', 'only']) ? 'p-3' : 'p-2'; ?>
            <?= $iconLocation === 'tile' ? 'flex-col justify-center' : ''; ?>
            <?php if ($isActive) : ?>
                text-white
            <?php else: ?>
                text-gray-200
            <?php endif; ?>
        "
    >
        <?php if ($iconLocation !== 'hidden') : ?>
            <?php if ($item->iconSvg): ?>
                <img
                    src="<?= Url::asset($item->iconSvg) ?>"
                    class="<?= implode(' ', $iconClass) ?>"
                    alt="<?= $iconLocation === 'only' ? e(trans($item->label)) : '' ?>"
                    loading="lazy"
                >
            <?php else: ?>
                <i
                    class="<?= implode(' ', $iconClass) ?>"
                    title="<?= $iconLocation === 'only' ? e(trans($item->label)) : '' ?>"
                >
                </i>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($iconLocation !== 'only'): ?>
            <span class="<?= $iconLocation === 'tile' ? 'block text-center mt-2' : ($hasChildren ? 'flex-1' : 'inline') ?>">
                <?= e(trans($item->label)) ?>
            </span>
        <?php endif; ?>
        <?php if ($item->counter): ?>
            <span
                class="counter !right-[2em]"
                data-menu-id="<?= e($item->code) ?>"
                <?php if ($item->counterLabel): ?>
                    title="<?= e(trans($item->counterLabel)) ?>"
                <?php endif ?>
            >
                <?= e($item->counter) ?>
            </span>
        <?php endif; ?>
    </a>
    <?php if ($hasChildren && $itemMode === 'inline') : ?>
        <button
            class="<?= $isActive ? 'open' : ''?>"
            data-toggle="collapse"
            data-target="#<?= $item->code ?>"
            aria-expanded="false"
            aria-controls="<?= $item->code ?>"
        >
            <svg
                viewBox="0 0 20 20"
                aria-hidden="true"
                class="w-4 h-4"
            >
                <path
                    d="M6 6L14 10L6 14V6Z"
                    fill="currentColor"
                />
            </svg>
        </button>
    <?php endif; ?>
</div>
<?php if ($hasChildren) : ?>
    <nav
        class="sidemenu-item-child
            <?php if ($itemMode === 'inline') : ?>
                mt-1 collapsible<?= $isActive ? ' show' : '' ?>
            <?php else: ?>
                hidden group-hover:!block group-hover:!visible absolute left-[100%] top-0 p-2
                bg-gray-800 z-[1000] rounded-tr-md rounded-br-md
            <?php endif; ?>
        "
        id="<?= $item->code ?>"
        data-menu-code="<?= $item->owner . '.' . $item->code; ?>"
        data-control="sidenav"
        data-active-class="active"
    >
        <ul class="sidemenu-item-child-menu list-none">
            <?php
                foreach ($item->sideMenu as $child):
                    $sideMenuIsActive = BackendMenu::isSideMenuItemActive($child);
                    $iconDefaultClass = "$child->icon mr-3 h-4 w-4";
                    $iconClass = $sideMenuIsActive ? 'text-white group-hover:text-white' : 'text-gray-200 group-hover:text-white group-hover:bg-transparent';
            ?>
                <li
                    class="<?= $sideMenuIsActive ? 'active' : '' ?> relative"
                    <?php if ($child->url === 'javascript:;'): ?>data-menu-item="<?= $child->code ?>"<?php endif; ?>
                >
                    <a
                        href="<?= $child->url === 'javascript:;' ? "$item->url" : $child->url ?>"
                        class="
                            group w-full flex items-center py-1.5
                            text-sm text-white font-medium rounded-md hover:text-white
                            hover:no-underline hover:bg-gray-700 focus:no-underline
                            focus:text-white transition duration-300 ease-in
                            px-3
                            <?= $child->counter ? 'pr-6' : ''; ?>
                            <?php if ($itemMode === 'inline') : ?>
                                pl-6
                            <?php endif; ?>
                        "
                    >
                        <?php if (!$child->iconSvg && $iconLocation !== 'hidden'): ?>
                            <i
                                class="<?= $iconDefaultClass ?> <?= $iconClass ?>"
                            >
                            </i>
                        <?php elseif ($child->iconSvg && $iconLocation !== 'hidden'): ?>
                            <img
                                src="<?= Url::asset($child->iconSvg) ?>"
                                class="svg-icon w-4 h-4"
                                loading="lazy"
                            >
                        <?php endif; ?>
                        <span class="<?= !$child->counter ? 'whitespace-nowrap' : ''; ?>">
                            <?= e(trans($child->label)) ?>
                        </span>
                        <?php if ($child->counter): ?>
                            <!-- @TODO: Fix support for icon "tile" / "only" mode -->
                            <span
                                class="counter !right-[1em]"
                                data-menu-id="<?= e($child->code) ?>"
                                <?php if ($child->counterLabel): ?>
                                    title="<?= e(trans($child->counterLabel)) ?>"
                                <?php endif ?>
                            >
                                <?= e($child->counter) ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>
<?php endif; ?>
