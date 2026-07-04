<?php

return [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon' => 'dashboard',
        'active_pattern' => 'dashboard',
    ],
    [
        'label' => 'Wallets',
        'route' => 'wallets.index',
        'icon' => 'account_balance_wallet',
        'active_pattern' => 'wallets.*',
    ],
    [
        'label' => 'Categories',
        'route' => 'categories.index',
        'icon' => 'category',
        'active_pattern' => 'categories.*',
    ],
    [
        'label' => 'Transactions',
        'route' => 'transactions.index',
        'icon' => 'receipt_long',
        'active_pattern' => 'transactions.*',
    ],
    [
        'label' => 'Roles',
        'route' => 'roles.index',
        'icon' => 'admin_panel_settings',
        'active_pattern' => 'roles.*',
        'permission' => 'view-role',
    ],
    [
        'label' => 'Users',
        'route' => 'users.index',
        'icon' => 'group',
        'active_pattern' => 'users.*',
        'permission' => 'view-user',
    ],
];
