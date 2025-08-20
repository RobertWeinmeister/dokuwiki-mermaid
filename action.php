<?php
/**
 * DokuWiki Plugin mermaid (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert Weinmeister <develop@weinmeister.org>
 */

declare(strict_types=1);

if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_mermaid extends \dokuwiki\Extension\ActionPlugin
{
    /** @inheritDoc */
    public function register(Doku_Event_Handler $controller): void {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'load');
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, 'handleAjaxRequest');
    }

    private function hasPermissionToEdit(string $ID): bool {
        return auth_quickaclcheck($ID) >= AUTH_EDIT;
    }

    private function isPageLocked(string $ID): bool {
        return checklock($ID);
    }

    private function lockMermaidDiagram(string $wikitext): string {
        preg_match_all('/<mermaid.*?>(.*?)<\/mermaid>/s', $wikitext, $matches, PREG_OFFSET_CAPTURE);
    
        if (is_array($matches) && count($matches[0]) > (int)$_REQUEST['mermaidindex']) {
            $whereToInsert = $matches[1][(int)$_REQUEST['mermaidindex']][1];
            return substr($wikitext, 0, $whereToInsert) . "\n%%" . urldecode($_REQUEST['svg']) . "\n" . substr($wikitext, $whereToInsert);
        }
    
        echo json_encode(['status' => 'failure', 'data' => ['Could not lock the Mermaid diagram as the request could not be matched.']]);
        exit();
    }
    
    private function unlockMermaidDiagram(string $wikitext): string {
        $newWikitext = str_replace("\n%%" . urldecode($_REQUEST['svg']) . "\n", '', $wikitext, $count);
    
        if ($count !== 1) {
            echo json_encode(['status' => 'failure', 'data' => ['Could not unlock the Mermaid diagram as the request could not be matched.']]);
            exit();
        }
    
        return $newWikitext;
    }
    
    private function isWikiTextChanged(string $wikitext, string $newWikitext): bool {
        return strlen($newWikitext) > 0 && $newWikitext !== $wikitext;
    }
    
    private function saveWikiChanges(string $ID, string $newWikitext, string $mode): void {
        lock($ID);
        saveWikiText($ID, $newWikitext, "{$mode} Mermaid diagram", true);
        unlock($ID);
    }

    public function handleAjaxRequest(Doku_Event $event, $param): void {
        if ($event->data !== 'plugin_mermaid') {
            return;
        }
        $event->stopPropagation();
        $event->preventDefault();

        if (!isset($_REQUEST['mermaidindex']) || !isset($_REQUEST['svg'])) {
            echo json_encode(['status' => 'failure', 'data' => ['Missing required parameters.']]);
            exit();
        }

        $ID = cleanID(urldecode($_REQUEST['pageid']));

        if(!$this->hasPermissionToEdit($ID)) {
            echo json_encode(['status' => 'failure', 'data' => ['You do not have permission to edit this file.\nAccess was denied.']]);
            exit();
        }

        if($this->isPageLocked($ID)) {
            echo json_encode(['status' => 'failure', 'data' => ['The page is currently locked.\nTry again later.']]);
            exit();
        }

        $wikitext = rawWiki($ID);
        $newWikitext = $wikitext;

        if($_REQUEST['mode'] === 'lock') {
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

        if($this->isWikiTextChanged($wikitext, $newWikitext)) {
            $this->saveWikiChanges($ID, $newWikitext, $_REQUEST['mode']);
            echo json_encode(['status' => 'success', 'data' => []]);
        } else{
            echo json_encode(['status' => 'failure', 'data' => ['Could not ' . $_REQUEST['mode'] . ' the Mermaid diagram.']]);
        }
        
        exit();
    }

    private function addLocalScript(Doku_Event $event): void {
        $event->data['script'][] = [
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            'src'     => DOKU_BASE . 'lib/plugins/mermaid/mermaid.min.js',
        ];
    }

    private function addEsmScript(Doku_Event $event, string $version, string $init): void {
        $data = "import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid{$version}/dist/mermaid.esm.min.mjs';{$init}";
        $event->data['script'][] = [
            'type'    => 'module',
            'charset' => 'utf-8',
            '_data'   => $data,
        ];
    }

    private function addScript(Doku_Event $event, string $version, string $init): void {
        $event->data['script'][] = [
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            'src'     => "https://cdn.jsdelivr.net/npm/mermaid{$version}/dist/mermaid.min.js",
        ];

        $event->data['script'][] = [
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data'   => $init,
        ];
    }

    private function pageIncludesMermaid(): bool {
        // true if the mermaid tag is used
        // the include plugin can hide this fact, so we need a separate check for it
        $wikiText = rawWiki(getID());
        if (str_contains($wikiText, '<mermaid') || str_contains($wikiText, '{{page>') || str_contains($wikiText, '{{section>') || str_contains($wikiText, '{{namespace>') || str_contains($wikiText, '{{tagtopic>')) {
            return true;
        }

        return false;
    }

    /**
     * Load the Mermaid library and configuration into the page.
     *
     * @param Doku_Event $event DokuWiki event object
     * @param mixed $param Unused parameter.
     */
    public function load(Doku_Event $event, $param): void {
         // only load mermaid if it is needed
        if (!$this->pageIncludesMermaid()) {
            return;
        }

        $theme = $this->getConf('theme');
        $look = $this->getConf('look');
        $logLevel = $this->getConf('logLevel');
        $init = "mermaid.initialize({startOnLoad: true, logLevel: '$logLevel', theme: '$theme', look: '$look'});";

        $location = $this->getConf('location');
        $versions = [
            'latest'     => '',
            'remote1091' => '@10.9.1',
            'remote108'  => '@10.8.0',
            'remote106'  => '@10.6.1',
            'remote104'  => '@10.4.0',
            'remote103'  => '@10.3.1',
            'remote102'  => '@10.2.4',
            'remote101'  => '@10.1.0',
            'remote100'  => '@10.0.2',
            'remote94'   => '@9.4.3',
            'remote943'  => '@9.4.3',
            'remote93'   => '@9.3.0',
        ];

        // add the appropriate Mermaid script based on the location configuration
        match ($location) {
            'local' => $this->addLocalScript($event),
            'latest', 'remote1091', 'remote108', 'remote106', 'remote104', 'remote103', 'remote102', 'remote101', 'remote100' 
                => $this->addEsmScript($event, $versions[$location], $init),
            'remote94', 'remote943', 'remote93' 
                => $this->addScript($event, $versions[$location], $init),
            default => null,
        };

        $event->data['link'][] = [
            'rel'     => 'stylesheet',
            'type'    => 'text/css',
            'href'    => DOKU_BASE . "lib/plugins/mermaid/mermaid.css",
        ];

        // remove the search highlight from DokuWiki as it interferes with the Mermaid parsing/rendering
        $event->data['script'][] = [
            'type'    => 'text/javascript',
            'charset' => 'utf-8',
            '_data' => "document.addEventListener('DOMContentLoaded', function() { 
                            jQuery('.mermaid').each(function() {
                                var modifiedContent = jQuery(this).html().replace(/<span class=\"search_hit\">(.+?)<\/span>/g, '$1');
                                jQuery(this).html(modifiedContent);
                             })
                        });"
        ];

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
             var fieldset = document.getElementById('mermaidFieldset' + index);
             if (fieldset) {
                    fieldset.style.display = 'flex';
             }
        });
        document.getElementById('mermaidContainer' + index).addEventListener('mouseleave', function() {
             var fieldset = document.getElementById('mermaidFieldset' + index);
             if (fieldset) {
                    fieldset.style.display = 'none';
             }
        });

        if(jQuery(element).hasClass('mermaidlocked')) {
            var buttonSave = document.getElementById('mermaidButtonSave' + index);
            if (buttonSave) {
                buttonSave.addEventListener('click', () => {
                    var svgContent = element.innerHTML.trim();
                    var blob = new Blob([svgContent], { type: 'image/svg+xml' });
                    var link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'mermaid' + index + '.svg';
                    link.click();
                    URL.revokeObjectURL(link.href);
                });
            }
            var buttonPermanent = document.getElementById('mermaidButtonPermanent' + index);
            if (buttonPermanent) {
                buttonPermanent.addEventListener('click', () => {
                    if(confirm('Unlock Mermaid diagram?')) {
                        callDokuWikiPHP('unlock', index, originalMermaidContent, element.innerHTML.trim());
                    }
                });
            }
        }

        if(jQuery(element).hasClass('mermaid')) {
            var originalMermaidContent = element.innerHTML;
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList' && element.innerHTML.startsWith('<svg')) {
                        var saveButton = document.getElementById('mermaidButtonSave' + index);
                        if (saveButton) {
                            saveButton.addEventListener('click', () => {
                                var svgContent = element.innerHTML.trim();
                                var blob = new Blob([svgContent], { type: 'image/svg+xml' });
                                var link = document.createElement('a');
                                link.href = URL.createObjectURL(blob);
                                link.download = 'mermaid' + index + '.svg';
                                link.click();
                                URL.revokeObjectURL(link.href);
                            });
                        }
                        var buttonPermanent = document.getElementById('mermaidButtonPermanent' + index);
                        if (buttonPermanent) {
                            buttonPermanent.addEventListener('click', () => {
                                    if(confirm('Lock Mermaid diagram? [experimental]')) {
                                        callDokuWikiPHP('lock', index, originalMermaidContent, element.innerHTML.trim());
                                    }
                            });
                        }
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