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

        switch ($this->getConf('location')) {
            case 'local':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => DOKU_BASE.'lib/plugins/mermaid/mermaid.min.js'
                );
                break;
            case 'latest':
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.esm.min.mjs';".$init
                );
                break;
            case 'remote103':
                $event->data['script'][] = array
                (
                    'type' => 'module',
                    'charset' => 'utf-8',
                    '_data' => "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10.3.1/dist/mermaid.esm.min.mjs';".$init
                );
                break;
            case 'remote102':
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10.2.4/dist/mermaid.esm.min.mjs';".$init
                );
                break;
            case 'remote101':
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10.1.0/dist/mermaid.esm.min.mjs';".$init
                );
                break;
            case 'remote100':
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10.0.2/dist/mermaid.esm.min.mjs';".$init
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

        switch ($this->getConf('location')) {
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