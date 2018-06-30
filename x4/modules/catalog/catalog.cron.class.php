<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;


class catalogCron extends catalogBack
{

    public function __construct()
    {
        parent::__construct();
    }


    public function rebuildPrices($params)
    {

        $this->_commonObj->rebuildIcurrencyFields($params);
        return true;
    }


    public function catalogIndex($params)
    {

        $this->fastIndexing($params);
        $adm = new AdminPanel();        
        $adm->clearCache(true);

        return !$this->result['ready'];
    }


    public function catalogAutoImport($params)
    {
        $autoLoadPath = xConfig::get('PATH', 'MEDIA') . 'import/' . $params['folder'] . '/';

        $shortPath = str_replace(PATH_, '', $autoLoadPath);

        if (file_exists($autoLoadPath)) {

            $files = XFILES::filesList($autoLoadPath, 'all', array('.xlsx'), 0, 1);

            if (!empty($files)) {
                foreach ($files as $file) {
                    $params['filename'] = $shortPath . $file;

                    if ($this->importData($params)) {

                        if (empty($params['doNotDeleteFile'])) {
                            $unset = $autoLoadPath . $file;
                            unlink($unset);
                            $adm = new AdminPanel();
                            $adm->clearCache(true);
                            return true;
                        }

                    }

                }
            }

        }

    }

}
