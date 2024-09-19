<?php
/**
 * DokuWiki Plugin mermaid (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Robert Weinmeister <develop@weinmeister.org>
 */

use dokuwiki\Parsing\Parser;

class syntax_plugin_mermaid extends \dokuwiki\Extension\SyntaxPlugin
{
    const DOKUWIKI_LINK_START_MERMAID = '<code>DOKUWIKILINKSTARTMERMAID</code>';
    const DOKUWIKI_LINK_END_MERMAID = '<code>DOKUWIKILINKENDMERMAID</code>';
    const DOKUWIKI_LINK_SPLITTER ='--';

    function enable_gantt_links($instructions)
    {
        $modified_instructions = $instructions;

        for ($i = 0; $i < count($modified_instructions); $i++)
        {
            if (in_array($modified_instructions[$i][0], ["externallink", "internallink"]))
            {
                //$test1 = $this->p_get_instructions("[[Dokuwiki|Dokuwiki]]");
                $test2 = $this->p_render($modified_instructions[$i]);
                 echo '<pre>' , var_dump($modified_instructions[$i]) , '</pre>';
                 echo '<pre>' , var_dump($test2) , '</pre>';
            }

            if (in_array($modified_instructions[$i][0], ["externallink2", "internallink2"]))
            {
                $link = $modified_instructions[$i][1][0];

                $test1 = $this->p_get_instructions("[[Dokuwiki|Dokuwiki]]");
                $test2 = $this->p_render($test1);
                 echo '<pre>' , var_dump($test1) , '</pre>';
                 echo '<pre>' , var_dump($test2) , '</pre>';

                // insert the click event
                if (preg_match('/(?<=:\s)\S+(?=,)/', $modified_instructions[$i+1][1][0], $output_array))
                {
                     $click_reference = $output_array[0];
                }
                array_splice($modified_instructions, $i + 2, 0, [["cdata", ["\nclick ".$click_reference." href \"".$link."\"\n"]]]);
                
                // change externallink to just the name of the link
                $modified_instructions[$i][0]= "cdata";
                if(!is_null($modified_instructions[$i][1][1]))
                {
                    unset($modified_instructions[$i][1][0]);
                }

                // encode colon
                $modified_instructions[$i][1][0] = str_replace(":", "#colon;", $modified_instructions[$i][1][0]);
            }
        }
                
        return $modified_instructions;
    }

    function protect_brackets_from_dokuwiki($text)
    {
        $splitText = explode(self::DOKUWIKI_LINK_SPLITTER, $text);
        foreach ($splitText as $key => $line)
        {
            $splitText[$key] = preg_replace('/(?<!["\[(\s])(\[\[)(.*)(\]\])/', self::DOKUWIKI_LINK_START_MERMAID . '$2' . self::DOKUWIKI_LINK_END_MERMAID, $line);
        }
        $text = implode(self::DOKUWIKI_LINK_SPLITTER, $splitText);
        return $text;
    }

    function remove_protection_of_brackets_from_dokuwiki($text)
    {
        return str_replace(self::DOKUWIKI_LINK_START_MERMAID, '[[', str_replace(self::DOKUWIKI_LINK_END_MERMAID, ']]', $text));
    }

   	/** @inheritDoc */
    function getType()
	{
		return 'container';
	}

    /** @inheritDoc */
    function getSort()
	{
		return 150;
	}

    /**
    * Connect lookup pattern to lexer.
    *
    * @param string $mode Parser mode
    */
    function connectTo($mode)
    {
        $this->Lexer->addEntryPattern('<mermaid.*?>(?=.*?</mermaid>)',$mode,'plugin_mermaid');
    }

    function postConnect()
    {
        $this->Lexer->addExitPattern('</mermaid>','plugin_mermaid');
    }

    /**
     * Handle matches of the Mermaid syntax
     */
    function handle($match, $state, $pos, Doku_Handler $handler)
    {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                return array($state, $match);
            case DOKU_LEXER_UNMATCHED:
                return array($state, $match);
            case DOKU_LEXER_EXIT:
                return array($state, '');
        }
        return false;
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
                    $values = explode(" ", $match);
                    $divwidth = count($values) < 2 ? 'auto' : $values[1];
                    $divheight = count($values) < 3 ? 'auto' : substr($values[2], 0, -1);
                    $renderer->doc .= '<div class="mermaid" style="width:'.$divwidth.'; height:'.$divheight.'">';
                break;
                case DOKU_LEXER_UNMATCHED:
                    $explodedMatch = explode("\n", $match);
                    $israwmode = isset($explodedMatch[1]) && strpos($explodedMatch[1], 'raw') !== false;
                    if($israwmode)
                    {
                        array_shift($explodedMatch);
                        array_shift($explodedMatch);
                        $actualContent = implode("\n", $explodedMatch);
                        $renderer->doc .= $actualContent;
                    }
                    else
                    {
                        $instructions = $this->p_get_instructions($this->protect_brackets_from_dokuwiki($match));

                        if (strpos($instructions[2][1][0], "gantt"))
                        {
                            $instructions = $this->enable_gantt_links($instructions);
                        }

                        echo '<pre>' , var_dump($instructions) , '</pre>';
                        $xhtml = $this->remove_protection_of_brackets_from_dokuwiki($this->p_render($instructions));
                        echo '<pre>' , var_dump($xhtml) , '</pre>';
                        $renderer->doc .= preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $xhtml);
                    }
                break;
                case DOKU_LEXER_EXIT:
                    $renderer->doc .= "\r\n</div>";
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
