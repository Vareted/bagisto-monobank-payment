<?php

return [
    [
        'key'    => 'sales.payment_methods.monobank',
        'name'   => 'Monobank',
        'sort'   => 1,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'monobank::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'monobank::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'token',
                'title'         => 'monobank::app.admin.system.token',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],[
                'name'          => 'destination',
                'title'         => 'monobank::app.admin.system.destination',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
                'nullable'      => true,
            ], [
                'name'          => 'logo',
                'title'         => 'monobank::app.admin.system.logo',
                'type'          => 'image',
                'channel_based' => false,
                'locale_based'  => true,
                'validation'    => 'mimes:jpeg,png,jpg,svg',
                'nullable'      => true,
            ], [
                'name'          => 'active',
                'title'         => 'monobank::app.admin.system.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
        ]
    ]
];
