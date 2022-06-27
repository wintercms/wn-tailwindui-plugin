<?php
    $authLayout = \Backend\Models\BrandSetting::get('auth_layout');;
    include \Str::before(__FILE__, '.php') . "-{$authLayout}.php";
