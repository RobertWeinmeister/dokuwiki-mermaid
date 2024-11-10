<?php
if (!defined('DOKU_INC')) {
    die();
}

$meta = [
    'location' => [
        'multichoice',
        '_choices' => ['local', 'latest', 'remote1091', 'remote943']
    ],
    'theme' => [
        'multichoice',
        '_choices' => ['default', 'neutral', 'dark', 'forest', 'base', 'mc', 'neo', 'neo-dark']
    ],
    'look' => [
        'multichoice',
        '_choices' => ['classic', 'neo', 'handDrawn']
    ],
    'logLevel' => [
        'multichoice',
        '_choices' => ['trace', 'debug', 'info', 'warn', 'error', 'fatal']
    ]
];