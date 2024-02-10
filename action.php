<?php
/**
 * DokuWiki Plugin mermaid (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert Weinmeister <develop@weinmeister.org>
 */

class action_plugin_mermaid extends \dokuwiki\Extension\ActionPlugin
{
    /** @inheritDoc */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'load');
    }

    public function load(Doku_Event $event, $param)
    {
        // Can be changed for debugging Mermaid
        // https://mermaid.js.org/config/directives.html#changing-loglevel-via-directive
        define("MERMAIDLOGLEVEL", "error");

        $theme = $this->getConf('theme');
        $init = "mermaid.initialize({startOnLoad: true, logLevel: '".MERMAIDLOGLEVEL."', theme: '".$theme."'});";
        $location = $this->getConf('location');
        $versions = array(
            'latest' => '',
            'remote108' => '@10.8.0',
            'remote106' => '@10.6.1',
            'remote104' => '@10.4.0',
            'remote103' => '@10.3.1',
            'remote102' => '@10.2.4',
            'remote101' => '@10.1.0',
            'remote100' => '@10.0.2'
        );
        $data = "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid".$versions[$location]."/dist/mermaid.esm.min.mjs';".$init;

        switch ($location) {
            case 'local':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => DOKU_BASE.'lib/plugins/mermaid/mermaid.min.js'
                );
                break;
            case 'latest':
            case 'remote106':
            case 'remote104':
            case 'remote103':
            case 'remote102':
            case 'remote101':
            case 'remote100':
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => $data
                );
                break;
            case 'remote94':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@9.4.3/dist/mermaid.min.js'
                );
                break;
            case 'remote93':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@9.3.0/dist/mermaid.min.js'
                );
                break;
            default:
        }

        $event->data['link'][] = array
        (
            'rel'     => 'stylesheet',
            'type'    => 'text/css',
            'href'    => DOKU_BASE."lib/plugins/mermaid/mermaid.css",
        );

        // remove the search highlight from DokuWiki as it interferes with the Mermaid parsing/rendering
        // window.onload = function() {
        $event->data['script'][] = array
        (
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => "                            var jq = jQuery.noConflict();
                            jq('.mermaid').each(function() {
                                var modifiedContent = jq(this).html().replace(/<span class=\"search_hit\">(.+?)<\/span>/g, '$1');
                                alert(modifiedContent);
                                jq(this).html(modifiedContent);
                            });
                        ",
            'defer' => 'defer'
        );

        switch ($location) {
            case 'local':
            case 'remote94':
            case 'remote93':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    '_data'   => $init
                );
                break;
            default:
        }
    }
}
