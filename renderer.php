<?php
if (!defined('DOKU_INC')) die();

class renderer_plugin_mermaid extends Doku_Renderer_xhtml
{
    function underline_open()
    {
        $xhtml = '<em class="u">';

        $xhtml = htmlentities($xhtml, ENT_NOQUOTES);
        $xhtml = str_replace(array('"'), array('\''), $xhtml);

        $this->doc .= $xhtml;
    }

    function underline_close()
    {
        $xhtml = '</em>';

        $xhtml = htmlentities($xhtml, ENT_NOQUOTES);
        $xhtml = str_replace(array('"'), array('\''), $xhtml);

        $this->doc .= $xhtml;
    }

    function internallink($id, $name = null, $search = null, $returnonly = false, $linktype = 'content')
    {
        $xhtml = parent::internallink($id, $name, $search, true, $linktype);

        $xhtml = htmlentities($xhtml, ENT_NOQUOTES);
        $xhtml = str_replace(array('"'), array('\''), $xhtml);

        if($returnonly)
        {
            return $xhtml;
        }
        else
        {
            $this->doc .= $xhtml;
        }
    }

    function externallink($url, $name = null, $returnonly = false)
    {
        $xhtml = parent::externallink($url, $name, true);

        $xhtml = htmlentities($xhtml,ENT_NOQUOTES);
        $xhtml = str_replace(array('"'), array('\''), $xhtml);

        //output formatted
        if($returnonly)
        {
            return $xhtml;
        }
        else
        {
            $this->doc .= $xhtml;
        }
    }

    function internalmedia($src, $title = null, $align = null, $width = null, $height = null, $cache = null, $linking = null, $return = false)
    {
        $xhtml = parent::internalmedia($src, $title, $align, $width, $height, $cache, $linking, true);

        $xhtml = htmlentities($xhtml,ENT_NOQUOTES);
        $xhtml = str_replace(array('"'), array('\''), $xhtml);

        //output formatted
        if($return)
        {
            return $xhtml;
        }
        else
        {
            $this->doc .= $xhtml;
        }
    }

    function cdata($text)
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