<?php
 /**
  * Language settings for the DokuWiki Mermaid plugin.
  *
  * @package DokuWiki\Plugin\Mermaid
  */
 
 declare(strict_types=1);
 
 if (!defined('DOKU_INC')) {
     die();
 }
 
 require_once __DIR__ . '/../../conf/config_keys.php';
 
 $lang = [
     // Location settings
     CONFIG_LOCATION => 'Use locally or remotely hosted mermaid file?',
     CONFIG_LOCATION . '_o_local' => 'locally hosted, version 11.9.0 (CJS)',
     CONFIG_LOCATION . '_o_latest' => 'remotely hosted, latest version (ESM)',
     CONFIG_LOCATION . '_o_remote1091' => 'remotely hosted, version 10.9.1 (ESM)',
     CONFIG_LOCATION . '_o_remote943' => 'remotely hosted, version 9.4.3 (CJS)',
     
     // Legacy location settings (no longer explicitly available)
     CONFIG_LOCATION . '_o_remote108' => 'remotely hosted, version 10.8 (ESM)',
     CONFIG_LOCATION . '_o_remote106' => 'remotely hosted, version 10.6 (ESM)',
     CONFIG_LOCATION . '_o_remote104' => 'remotely hosted, version 10.4 (ESM)',
     CONFIG_LOCATION . '_o_remote103' => 'remotely hosted, version 10.3 (ESM)',
     CONFIG_LOCATION . '_o_remote102' => 'remotely hosted, version 10.2 (ESM)',
     CONFIG_LOCATION . '_o_remote101' => 'remotely hosted, version 10.1 (ESM)',
     CONFIG_LOCATION . '_o_remote100' => 'remotely hosted, version 10.0 (ESM)',
     CONFIG_LOCATION . '_o_remote94' => 'remotely hosted, version 9.4 (CJS)',
     CONFIG_LOCATION . '_o_remote93' => 'remotely hosted, version 9.3 (CJS)',
     
     // Theme settings
     CONFIG_THEME => 'Default theme',
     CONFIG_THEME . '_o_default' => 'default',
     CONFIG_THEME . '_o_neutral' => 'neutral',
     CONFIG_THEME . '_o_dark' => 'dark',
     CONFIG_THEME . '_o_forest' => 'forest',
     CONFIG_THEME . '_o_base' => 'base',
     CONFIG_THEME . '_o_mc' => 'mc',
     CONFIG_THEME . '_o_neo' => 'neo',
     CONFIG_THEME . '_o_neo_dark' => 'neo-dark',
     
     // Look settings
     CONFIG_LOOK => 'Default look',
     CONFIG_LOOK . '_o_classic' => 'classic',
     CONFIG_LOOK . '_o_neo' => 'neo',
     CONFIG_LOOK . '_o_handDrawn' => 'handDrawn',
     
     // Log level settings
     CONFIG_LOG_LEVEL => 'Log level',
     CONFIG_LOG_LEVEL . '_o_trace' => 'trace',
     CONFIG_LOG_LEVEL . '_o_debug' => 'debug',
     CONFIG_LOG_LEVEL . '_o_info' => 'info',
     CONFIG_LOG_LEVEL . '_o_warn' => 'warn',
     CONFIG_LOG_LEVEL . '_o_error' => 'error',
     CONFIG_LOG_LEVEL . '_o_fatal' => 'fatal',
    
     // Show save button settings
     SHOW_SAVE_BUTTON => 'Show save button',
     
     // Show lock button settings
     SHOW_LOCK_BUTTON => 'Show lock button',
 ];