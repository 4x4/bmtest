<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

class pagesXfront extends pagesFront
{
    public function sourceBuster($params)
    {
        $_SESSION['sourceBuster'] = $params;
    }

    public function renderSlot($params)
    {
        
        if (isset($params['slot']) and ($params['url'])) {
            if (!is_array($params['slot'])) $params['slot'] = array($params['slot']);
            XRegistry::set('TPA', $TPA = new pageAgregator(null));
            $TPA->setRenderMode(RENDERMODE);
            $params['url'] = urldecode($params['url']);
            $parsedUrl = parse_url($params['url']);

            $_GET = null;
            $_REQUEST = null;

            if ($parsedUrl['query']) {
                parse_str($parsedUrl['query'], $_GET);
                parse_str($parsedUrl['query'], $_REQUEST);
            }

            $TPA->buildPage($params['url'], false, $params['slot']);
            $this->result['slots'] = $TPA->slotzOut;

        }

    }

}


