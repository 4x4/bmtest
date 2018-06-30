<?php
//'catalog.property.currencyIshopProperty:afterHandleTypeFront'
/**
 * options
 *  -storagePath
 */

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;

class urlChanger
{
    public $options;

    public function __construct()
    {
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function changer($k, $v = null)
    {
        $data=$k['data'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location:'.$this->options['url']);
        die();

    }

    public function run()
    {
        XRegistry::get('EVM')->on('agregator:start', 'changer', $this);
    }


}

?>
