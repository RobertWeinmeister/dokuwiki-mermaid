<?php
/**
 * Default configuration for the DokuWiki Mermaid plugin.
 *
 * @package DokuWiki\Plugin\Mermaid
 */

declare(strict_types=1);

if (!defined('DOKU_INC')) {
    die();
}

require_once __DIR__ . '/config_keys.php';

$conf = [
    CONFIG_LOCATION  => 'latest',
    CONFIG_THEME     => 'default',
    CONFIG_LOOK      => 'classic',
    CONFIG_LOG_LEVEL => 'error',
    SHOW_SAVE_BUTTON => 0,
    SHOW_LOCK_BUTTON => 0,
];