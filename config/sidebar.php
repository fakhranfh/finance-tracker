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
];
