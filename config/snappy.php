<?php

return [

    'pdf' => [
        'enabled' => true,
        'binary' => '/usr/bin/wkhtmltopdf', // Path confirmed by 'which wkhtmltopdf'
        'options' => [], // Ensure this is an empty array or a valid options array
        'env'     => [],
    ],

    'image' => [
        'enabled' => true,
        'binary'  => '/usr/bin/wkhtmltoimage',
        'options' => [],
        'env'     => [],
    ],

];
