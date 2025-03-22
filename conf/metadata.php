<?php
/**
 * Metadata configuration for the DokuWiki Mermaid plugin.
 *
 * @package DokuWiki\Plugin\Mermaid
 */

declare(strict_types=1);

if (!defined('DOKU_INC')) {
    die();
}

require_once __DIR__ . '/config_keys.php';

$meta = [
    CONFIG_LOCATION => [
        'multichoice',
        '_choices' => ['local', 'latest', 'remote1091', 'remote943']
    ],
    CONFIG_THEME => [
        'multichoice',
        '_choices' => ['default', 'neutral', 'dark', 'forest', 'base', 'mc', 'neo', 'neo-dark']
    ],
    CONFIG_LOOK => [
        'multichoice',
        '_choices' => ['classic', 'neo', 'handDrawn']
    ],
    CONFIG_LOG_LEVEL => [
        'multichoice',
        '_choices' => ['trace', 'debug', 'info', 'warn', 'error', 'fatal']
    ],
    SHOW_SAVE_BUTTON => ['onoff'],
    SHOW_LOCK_BUTTON => ['onoff'],
];