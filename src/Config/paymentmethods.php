<?php

return [
    'monobank'  => [
        'code'        => 'monobank',
        'title'       => 'Monobank',
        'description' => 'Monobank',
        'destination' => 'Оплата за замовлення #{{orderId}}',
        'class'       => 'Vareted\Monobank\Payment\Monobank',
        'active'      => true,
        'sort'        => 1,
    ],
];
