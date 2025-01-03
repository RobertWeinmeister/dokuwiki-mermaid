<?php
declare(strict_types=1);

if (!defined('DOKU_INC')) die();

class renderer_plugin_mermaid extends Doku_Renderer_xhtml
{
    private function formattedXhtml(string $xhtml): string
    {
        return str_replace(['"'], ['\''], htmlentities($xhtml, ENT_NOQUOTES));
    }

    public function underline_open()
    {
        $this->doc .= $this->FormattedXhtml('<em class="u">');
    }

    public function underline_close()
    {
        $this->doc .= $this->FormattedXhtml('</em>');
    }

    public function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content')
    {
        $xhtml = $this->formattedXhtml(parent::internallink($id, $name, $search, true, $linktype));

        if ($returnonly) {
            return $xhtml;
        }

        $this->doc .= $xhtml;
    }

    public function externallink($url, $name = null, $returnonly = false)
    {
        $xhtml = $this->formattedXhtml(parent::externallink($url, $name, true));

        if ($returnonly) {
            return $xhtml;
        }
        
        $this->doc .= $xhtml;
    }

    public function internalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null, $return = false)
    {
        $xhtml = parent::internalmedia($src, $title, $align, $width, $height, $cache, $linking, true);

        $xhtml = htmlentities($xhtml, ENT_NOQUOTES);
        $xhtml = str_replace(['"'], ['\''], $xhtml);

        if ($return) {
            return $xhtml;
        } else {
            $this->doc .= $xhtml;
        }
    }

    public function cdata($text)
    {
        $this->doc .= $text;
    }

    public function p_open()
    {
    }

    public function p_close()
    {
    }
}