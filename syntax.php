<?php

/**
 * DokuWiki Plugin Mermaid (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert Weinmeister <develop@weinmeister.org>
 */

declare(strict_types=1);

if (!defined('DOKU_INC'))
{
    die();
}

use dokuwiki\Parsing\Parser;

class syntax_plugin_mermaid extends \dokuwiki\Extension\SyntaxPlugin
{
    // Constants for DokuWiki link handling
    private const DOKUWIKI_LINK_START_MERMAID = '<code>DOKUWIKILINKSTARTMERMAID</code>';
    private const DOKUWIKI_LINK_END_MERMAID = '<code>DOKUWIKILINKENDMERMAID</code>';
    private const DOKUWIKI_LINK_SPLITTER ='--';

    // SVG icons as constants
    private const DOKUWIKI_SVG_SAVE = '<svg fill="#000000" viewBox="0 0 52 52" enable-background="new 0 0 52 52" xml:space="preserve" style="width: 24px; height: 24px;"><path d="M37.1,4v13.6c0,1-0.8,1.9-1.9,1.9H13.9c-1,0-1.9-0.8-1.9-1.9V4H8C5.8,4,4,5.8,4,8v36c0,2.2,1.8,4,4,4h36  c2.2,0,4-1.8,4-4V11.2L40.8,4H37.1z M44.1,42.1c0,1-0.8,1.9-1.9,1.9H9.9c-1,0-1.9-0.8-1.9-1.9V25.4c0-1,0.8-1.9,1.9-1.9h32.3  c1,0,1.9,0.8,1.9,1.9V42.1z"/><path d="M24.8,13.6c0,1,0.8,1.9,1.9,1.9h4.6c1,0,1.9-0.8,1.9-1.9V4h-8.3L24.8,13.6L24.8,13.6z"/></svg>';
    private const DOKUWIKI_SVG_LOCKED = '<svg viewBox="0 0 16 16" fill="none" style="width: 24px; height: 24px;"><path fill-rule="evenodd" clip-rule="evenodd" d="M4 6V4C4 1.79086 5.79086 0 8 0C10.2091 0 12 1.79086 12 4V6H14V16H2V6H4ZM6 4C6 2.89543 6.89543 2 8 2C9.10457 2 10 2.89543 10 4V6H6V4ZM7 13V9H9V13H7Z" fill="#000000"/></svg>';
    private const DOKUWIKI_SVG_UNLOCKED = '<svg style="width: 24px; height: 24px;" viewBox="0 0 16 16" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M11.5 2C10.6716 2 10 2.67157 10 3.5V6H13V16H1V6H8V3.5C8 1.567 9.567 0 11.5 0C13.433 0 15 1.567 15 3.5V4H13V3.5C13 2.67157 12.3284 2 11.5 2ZM9 10H5V12H9V10Z" fill="#000000"/></svg>';

    private int $mermaidCounter = -1;
    private string $mermaidContent = '';
    private bool $currentMermaidIsLocked = false;
    private string $mermaidContentIfLocked = '';

    /**
     * Processes Gantt chart links in Mermaid diagrams
     * 
     * Converts DokuWiki internal and external links into Mermaid-compatible
     * click events for Gantt chart tasks.
     * 
     * @param array $instructions The parser instructions to process
     * @return array Modified instructions with properly formatted Gantt links
     */
    private function processGanttLinks($instructions): array {
        $modified_instructions = $instructions;

        for ($i = 0; $i < count($modified_instructions); $i++)
        {
            if (!in_array($modified_instructions[$i][0], ['externallink', 'internallink'])) {
                continue;
            }
        
            // use the appropriate link
            $link = $modified_instructions[$i][0] === "externallink"
                ? $modified_instructions[$i][1][0]
                : wl($modified_instructions[$i][1][0], '', true);
        
            // change link here to just the name
            $modified_instructions[$i][0] = "cdata";
            if($modified_instructions[$i][1][1] !== null) {
                unset($modified_instructions[$i][1][0]);
            }
        
            // insert the click event
            $click_reference = '';
            if (preg_match('/(?<=:\s)\S+(?=,)/', $modified_instructions[$i+1][1][0], $output_array)) {
                $click_reference = $output_array[0];
            }

            array_splice(
                $modified_instructions, 
                $i + 2, 
                0, 
                [["cdata", ["\nclick {$click_reference} href \"{$link}\"\n"]]]
            );
            
            // encode colons
            if (isset($modified_instructions[$i][1][0]) && is_string($modified_instructions[$i][1][0])) {
                $modified_instructions[$i][1][0] = str_replace(":", "#colon;", $modified_instructions[$i][1][0]);
            }
        }
        
        return $modified_instructions;
    }

    /**
     * Protects DokuWiki link brackets from being processed
     * 
     * @param string $text Text containing DokuWiki links
     * @return string Text with protected DokuWiki link brackets
     */
    private function protectBracketsFromDokuWiki(string $text): string {
        $splitText = explode(self::DOKUWIKI_LINK_SPLITTER, $text);
        foreach ($splitText as $key => $line) {
            $splitText[$key] = preg_replace(
                '/(?<!["\[(\s])(\[\[)(.*)(\]\])/',
                self::DOKUWIKI_LINK_START_MERMAID . '$2' . self::DOKUWIKI_LINK_END_MERMAID,
                $line
            ) ?? $line;
        }
        return implode(self::DOKUWIKI_LINK_SPLITTER, $splitText);
    }

    /**
    * Reverts the protection of DokuWiki link brackets
    *
    * @param string $text Text containing protected DokuWiki links
    * @return string Text with restored DokuWiki link brackets
    */
    private function removeProtectionOfBracketsFromDokuWiki(string $text): string {
        return str_replace(
            [self::DOKUWIKI_LINK_START_MERMAID, self::DOKUWIKI_LINK_END_MERMAID],
            ['[[',  ']]'],
            $text
        );
    }

    /** @inheritDoc */
    function getType(): string {
        return 'container';
    }

    /** @inheritDoc */
    function getSort(): int {
        return 150;
    }

    /**
    * Entry pattern for Mermaid
    *
    * @param string $mode Parser mode
    * @return void
    */
    public function connectTo($mode): void {
        $this->Lexer->addEntryPattern(
            '<mermaid.*?>(?=.*?</mermaid>)',
            $mode,
            'plugin_mermaid');
    }

    /**
     * Exit pattern for Mermaid
     *
     * @return void
     */
    function postConnect(): void {
        $this->Lexer->addExitPattern(
            '</mermaid>',
            'plugin_mermaid'
        );
    }

    /**
     * Handles the matched text based on the lexer state
     *
     * @param string $match Matched text from the lexer
     * @param int $state Current lexer state (DOKU_LEXER_ENTER, DOKU_LEXER_UNMATCHED, DOKU_LEXER_EXIT)
     * @param Doku_Handler $handler DokuWiki handler instance
     * @return array Array with the state and processed match
     */
    function handle($match, $state, $pos, Doku_Handler $handler): array {
        return [$state, $match];
    }

    /**
    * Render xhtml output or metadata
    */
    function render($mode, Doku_Renderer $renderer, $indata)
    {
        if($mode == 'xhtml'){
            list($state, $match) = $indata;
            switch ($state) {
                case DOKU_LEXER_ENTER:
                    $this->mermaidCounter++;
                    $values = explode(" ", $match);
                    $divwidth = count($values) < 2 ? 'auto' : $values[1];
                    $divheight = count($values) < 3 ? 'auto' : substr($values[2], 0, -1);
                    $this->mermaidContent .= '<div id="mermaidContainer'.$this->mermaidCounter.'" style="position: relative; width:'.$divwidth.'; height:'.$divheight.'">';
                    $this->mermaidContentIfLocked = $this->mermaidContent . '<span class="mermaidlocked" id=mermaidContent'.$this->mermaidCounter.' style="width:'.$divwidth.'; height:'.$divheight.'">';
                    $this->mermaidContent .= '<span class="mermaid" id=mermaidContent'.$this->mermaidCounter.' style="width:'.$divwidth.'; height:'.$divheight.'">';
                break;
                case DOKU_LEXER_UNMATCHED:
                    $explodedMatch = explode("\n", $match);

                    if(str_starts_with($explodedMatch[1], '%%<svg'))
                    {
                        $this->currentMermaidIsLocked = true;
                        $this->mermaidContent = $this->mermaidContentIfLocked . substr($explodedMatch[1], 2);
                        break;
                    }
                    else
                    {
                        $this->currentMermaidIsLocked = false;
                    }

                    $israwmode = isset($explodedMatch[1]) && strpos($explodedMatch[1], 'raw') !== false;
                    if($israwmode)
                    {
                        array_shift($explodedMatch);
                        array_shift($explodedMatch);
                        $actualContent = implode("\n", $explodedMatch);
                        $this->mermaidContent .= $actualContent;
                    }
                    else
                    {
                        $instructions = $this->p_get_instructions($this->protectBracketsFromDokuWiki($match));
                        if (strpos($instructions[2][1][0], "gantt"))
                        {
                            $instructions = $this->processGanttLinks($instructions);
                        }
                        $xhtml = $this->removeProtectionOfBracketsFromDokuWiki($this->p_render($instructions));
                        $this->mermaidContent .= preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $xhtml);
                    }
                break;
                case DOKU_LEXER_EXIT:
                    $this->mermaidContent .= "\r\n</span>";
                    if($this->getConf('showSaveButton') or $this->getConf('showLockButton'))
                    {
                        $this->mermaidContent .= '<fieldset id="mermaidFieldset'.$this->mermaidCounter.'" style="position: absolute; top: 0; left: 0; display: none; width:auto; border: none">';
                        if($this->getConf('showSaveButton'))
                        {
                            $this->mermaidContent .= '<button id="mermaidButtonSave'.$this->mermaidCounter.'" style="z-index: 10; display: block; padding: 0; margin: 0; border: none; background: none; width: 24px; height: 24px;">'.self::DOKUWIKI_SVG_SAVE.'</button>';
                        }
                        if($this->getConf('showLockButton'))
                        {
                            $this->mermaidContent .= '<button id="mermaidButtonPermanent'.$this->mermaidCounter.'" style="z-index: 10; display: block; padding: 0; margin: 0; border: none; background: none; width: 24px; height: 24px;">'.($this->currentMermaidIsLocked ? self::DOKUWIKI_SVG_LOCKED : self::DOKUWIKI_SVG_UNLOCKED).'</button>';
                        }
                        $this->mermaidContent .= '</fieldset>';
                    }
                    $this->mermaidContent .= '</div>';
                    
                    $renderer->doc .= $this->mermaidContent;
                    $this->mermaidContent = '';
                break;
            }
            return true;
        }
        return false;
    }

    /*
    * Get the parser instructions suitable for the mermaid
    *
    */
    function p_get_instructions($text)
    {
        //import parser classes and mode definitions
        require_once DOKU_INC . 'inc/parser/parser.php';

        // https://www.dokuwiki.org/devel:parser
        // https://www.dokuwiki.org/devel:parser#basic_invocation
        // Create the parser and the handler
        $Parser = new Parser(new Doku_Handler());

        $modes = array();

        // add default modes
        $std_modes = array( 'internallink', 'media', 'externallink');

        foreach($std_modes as $m)
        {
            $class = 'dokuwiki\\Parsing\\ParserMode\\'.ucfirst($m);
            $obj   = new $class();
            $modes[] = array(
            'sort' => $obj->getSort(),
            'mode' => $m,
            'obj'  => $obj
            );
        }

        // add formatting modes
        $fmt_modes = array( 'strong', 'emphasis', 'underline', 'monospace', 'subscript', 'superscript', 'deleted');
        foreach($fmt_modes as $m)
        {
          $obj   = new \dokuwiki\Parsing\ParserMode\Formatting($m);
          $modes[] = array(
            'sort' => $obj->getSort(),
            'mode' => $m,
            'obj'  => $obj
          );
        }

        //add modes to parser
        foreach($modes as $mode)
        {
            $Parser->addMode($mode['mode'],$mode['obj']);
        }

        // Do the parsing
        $p = $Parser->parse($text);

        return $p;
    }

    public function p_render($instructions)
    {
        
        $Renderer = p_get_renderer('mermaid');

        // Loop through the instructions
        foreach ($instructions as $instruction) {
            if(method_exists($Renderer, $instruction[0])){
            call_user_func_array(array(&$Renderer, $instruction[0]), $instruction[1] ? $instruction[1] : array());
            }
        }
        return $Renderer->doc;
    }
}