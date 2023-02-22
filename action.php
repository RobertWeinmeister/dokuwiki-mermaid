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
        switch ($this->getConf('location')) {
            case 'local':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    // not updated
                    'src' => DOKU_BASE.'lib/plugins/mermaid/mermaid.min.js'
                );
                break;
            case 'latest':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    // still 9.4
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js'
                );
                break;
            case 'remote100':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    // not yet available
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@10.0/dist/mermaid.min.js'
                );
                break;
            case 'remote94':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@9.4/dist/mermaid.min.js'
                );
                break;
            case 'remote93':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@9.3/dist/mermaid.min.js'
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

        // Can be changed for debugging
        // https://mermaid.js.org/config/directives.html#changing-loglevel-via-directive
        $event->data['script'][] = array
        (
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data'   => 'mermaid.initialize({logLevel: "error"});'
        );
    }
}
