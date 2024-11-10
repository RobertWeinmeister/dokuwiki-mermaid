<?php
if (!defined('DOKU_INC')) die();

$meta['location'] = array('multichoice', '_choices' => array('local', 'latest', 'remote1091', 'remote943'));
$meta['theme'] = array('multichoice', '_choices' => array('default', 'neutral', 'dark', 'forest', 'base', 'mc', 'neo', 'neo-dark'));
$meta['look'] = array('multichoice', '_choices' => array('classic', 'neo', 'handDrawn'));
$meta['logLevel'] = array('multichoice', '_choices' => array('trace', 'debug', 'info', 'warn', 'error', 'fatal'));