<?php

/**
 * options
 *  -storagePath
 */
class fileStorage implements personStorageInterface
{
    public $options;

    public function __construct($options)
    {
        $this->options = $options;

        if (!is_writeable($this->options['storagePath'])) {
            throw new Exception('storage-path-is-not-writeable');
        }
    }


    public function getItem($uid)
    {
        $data = file_get_contents($this->options['storagePath'] . "{$uid}.json");

        if (!empty($data)) {

            return json_decode($data, true);
        }

    }

    public function setItem($uid, $data)
    {
        file_put_contents($this->options['storagePath'] . "{$uid}.json", json_encode($data));
    }


}

?>
