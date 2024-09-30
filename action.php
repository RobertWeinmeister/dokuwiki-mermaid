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
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxRequest');
    }

    public function handleAjaxRequest(Doku_Event $event, $param) {
        if ($event->data !== 'plugin_mermaid')
        {
            return;
        }

        $event->stopPropagation();
        $event->preventDefault();

        $ID = cleanID(urldecode($_REQUEST['pageid']));

        if(auth_quickaclcheck($ID) < AUTH_EDIT)
        {
            echo json_encode(['status' => 'failure', 'data' => ['You do not have permission to edit this file.\nAccess was denied.']]);
            exit();
        }

        if(checklock($ID))
        {
            echo json_encode(['status' => 'failure', 'data' => ['The page is currently locked.\nTry again later.']]);
            exit();
        }

        $wikitext = rawWiki($ID);
        $newWikitext = $wikitext;

        if($_REQUEST['mode'] == 'lock')
        {
            preg_match_all('/<mermaid.*?>(.*?)<\/mermaid>/s', $wikitext, $matches, PREG_OFFSET_CAPTURE);

            if(is_array($matches) && count($matches[0]) > $_REQUEST['mermaidindex'])
            {
                $whereToInsert = $matches[1][$_REQUEST['mermaidindex']][1];
                $newWikitext = substr($wikitext, 0, $whereToInsert) . "\n%%" . urldecode($_REQUEST['svg']) . "\n" . substr($wikitext, $whereToInsert);
            }
            else
            {
                echo json_encode(['status' => 'failure', 'data' => ['Could not lock the Mermaid diagram as the request could not be matched.']]);
                exit();
            }
        }

        if($_REQUEST['mode'] == 'unlock')
        {
            $newWikitext = str_replace("\n%%" . urldecode($_REQUEST['svg']) . "\n", '', $wikitext, $count);
            if($count != 1)
            {
                echo json_encode(['status' => 'failure', 'data' => ['Could not unlock the Mermaid diagram as the request could not be matched.']]);
                exit();
            }
        }

        if(strlen($newWikitext) > 0 && $newWikitext != $wikitext)
        {
            lock($ID);
            saveWikiText($ID, $newWikitext, $_REQUEST['mode'] . ' Mermaid diagram', $minoredit = true);
            unlock($ID);

            echo json_encode(['status' => 'success', 'data' => []]);
            exit();
        }

        echo json_encode(['status' => 'failure', 'data' => ['Could not '.$_REQUEST['mode'].' the Mermaid diagram.']]);
        exit();
    }

    public function load(Doku_Event $event, $param)
    {
        // Can be changed for debugging Mermaid
        // https://mermaid.js.org/config/directives.html#changing-loglevel-via-directive
        define("MERMAIDLOGLEVEL", "error");

        $theme = $this->getConf('theme');
        $init = "mermaid.initialize({startOnLoad: true, logLevel: '".MERMAIDLOGLEVEL."', theme: '".$theme."'});";
        $location = $this->getConf('location');

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
            case 'remote1091':
            // options remote108, remote106, remote104, remote103, remote102, remote101, remote100 are depreciated and only included for backward compatibility
            case 'remote108':
            case 'remote106':
            case 'remote104':
            case 'remote103':
            case 'remote102':
            case 'remote101':
            case 'remote100':
                $versions = array(
                    'latest' => '',
                    'remote1091' => '@10.9.1',
                    'remote108' => '@10.8.0',
                    'remote106' => '@10.6.1',
                    'remote104' => '@10.4.0',
                    'remote103' => '@10.3.1',
                    'remote102' => '@10.2.4',
                    'remote101' => '@10.1.0',
                    'remote100' => '@10.0.2'
                );
                $data = "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid".$versions[$location]."/dist/mermaid.esm.min.mjs';".$init;
                $event->data['script'][] = array
                (
                    'type'    => 'module',
                    'charset' => 'utf-8',
                    '_data' => $data
                );
                break;
            // option remote94 is depreciated and only included for backward compatibility
            case 'remote94':
            case 'remote943':
                $event->data['script'][] = array
                (
                    'type'    => 'text/javascript',
                    'charset' => 'utf-8',
                    'src' => 'https://cdn.jsdelivr.net/npm/mermaid@9.4.3/dist/mermaid.min.js'
                );
                break;
            // option remote93 is depreciated and only included for backward compatibility
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

        switch ($location) {
            case 'local':
            case 'remote943':
            // options remote94 and remote93 are depreciated and only included for backward compatibility
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
    
        $event->data['script'][] = array
        (
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => ""
        );

        // remove the search highlight from DokuWiki as it interferes with the Mermaid parsing/rendering
        $event->data['script'][] = array
        (
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => "document.addEventListener('DOMContentLoaded', function() { 
                            jQuery('.mermaid').each(function() {
                                var modifiedContent = jQuery(this).html().replace(/<span class=\"search_hit\">(.+?)<\/span>/g, '$1');
                                jQuery(this).html(modifiedContent);
                             })
                        });"
        );

        // adds image-save capability
        // First: Wait until the DOM content is fully loaded
        // Second: Wait until Mermaid has changed the dokuwiki content to an svg
        $event->data['script'][] = array
        (
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => "
document.addEventListener('DOMContentLoaded', function() {
     var config = {
        childList: true,
        subtree: true,
        characterData: true
    };

    function callDokuWikiPHP(mode, index, mermaidRaw, mermaidSvg) {
    jQuery.post(
        DOKU_BASE + 'lib/exe/ajax.php',
        {
            call: 'plugin_mermaid',
            mode: mode,
            mermaidindex: index,
            pageid: '".getID()."',
            svg: encodeURIComponent(mermaidSvg)
        },
        function(response) {
            if(response.status == 'success') {
                location.reload(true);
            }
            else {
                alert(response.data[0]);
            }
        },
        'json'
    )};

    jQuery('.mermaidlocked, .mermaid').each(function(index, element) {
        document.getElementById('mermaidContainer' + index).addEventListener('mouseenter', function() {
             document.getElementById('mermaidFieldset' + index).style.display = 'flex';
        });
        document.getElementById('mermaidContainer' + index).addEventListener('mouseleave', function() {
            document.getElementById('mermaidFieldset' + index).style.display = 'none';
        });

        if(jQuery(element).hasClass('mermaidlocked')) {
            document.getElementById('mermaidButtonSave' + index).addEventListener('click', () => {
                var svgContent = element.innerHTML.trim();
                var blob = new Blob([svgContent], { type: 'image/svg+xml' });
                var link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'mermaid' + index + '.svg';
                link.click();
                URL.revokeObjectURL(link.href);
            });

            document.getElementById('mermaidButtonPermanent' + index).addEventListener('click', () => {
                if(confirm('Unlock Mermaid diagram?')) {
                    callDokuWikiPHP('unlock', index, originalMermaidContent, element.innerHTML.trim());
                }
            });
        }

        if(jQuery(element).hasClass('mermaid')) {
            var originalMermaidContent = element.innerHTML;
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && element.innerHTML.startsWith('<svg')) {
                        document.getElementById('mermaidButtonSave' + index).addEventListener('click', () => {
                            var svgContent = element.innerHTML.trim();
                            var blob = new Blob([svgContent], { type: 'image/svg+xml' });
                            var link = document.createElement('a');
                            link.href = URL.createObjectURL(blob);
                            link.download = 'mermaid' + index + '.svg';
                            link.click();
                            URL.revokeObjectURL(link.href);
                        });

                       document.getElementById('mermaidButtonPermanent' + index).addEventListener('click', () => {
                            if(confirm('Lock Mermaid diagram? [experimental]')) {
                                callDokuWikiPHP('lock', index, originalMermaidContent, element.innerHTML.trim());
                            }
                       });
                    }
                });
            });
            observer.observe(element, config);
        }
    });
});"
        );
    }
}

