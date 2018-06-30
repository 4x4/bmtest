<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

interface personStorageInterface
{
    public function getItem($uid);

    public function setItem($uid, $data);

    public function updateItem($uid, $data);
}


class personStorage extends \xSingleton
{

    public function factorStorage($storageName, $options = null)
    {
        $storageFile = xConfig::get('PATH', 'MODULES') . 'xpe/ext/storage/' . $storageName . 'Storage.php';

        $this->options = $options;

        if (file_exists($storageFile)) {

            require_once($storageFile);

            $storageName = $storageName . 'Storage';

            return new $storageName($options);

        } else {

			return false;
            //throw new Exception('storage-is-not-exists');
        }

    }

}


