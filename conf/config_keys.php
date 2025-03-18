<?php
/**
 * Configuration keys for the DokuWiki Mermaid plugin.
 *
 * @package DokuWiki\Plugin\Mermaid
 */

declare(strict_types=1);

if (!defined('DOKU_INC')) {
    die();
}

const CONFIG_LOCATION = 'location';
const CONFIG_THEME = 'theme';
const CONFIG_LOOK = 'look';
const CONFIG_LOG_LEVEL = 'logLevel';
const SHOW_SAVE_BUTTON = 'showSaveButton';
const SHOW_LOCK_BUTTON = 'showLockButton';