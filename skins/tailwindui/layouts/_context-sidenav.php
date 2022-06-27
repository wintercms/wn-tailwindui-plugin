<?php
    $context = BackendMenu::getContext();
    $contextSidenav = BackendMenu::getContextSidenavPartial($context->owner, $context->mainMenuCode);

    if ($contextSidenav) {
        echo $this->makePartial($contextSidenav);
    }
?>
