<?php
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

class templatesBack extends xModuleBack
{

    function __construct()
    {
        parent::__construct(__CLASS__);
        $this->_commonObj->refreshMainTpls();
    }


    public function treeDynamicXLS($params)
    {

        $source = Common::classesFactory('FileJsonSource', array($this->_tree));
        $opt = array
        (
            'imagesIcon' => array('_FOLDER' => 'folder.gif', '_FILE' => 'leaf.gif'),
            'gridFormat' => true

        );

        $source->setOptions($opt);
        
        if (!$params['id']) $params['id'] = base64_encode(xConfig::get('PATH', 'TEMPLATES'));
        $this->result = $source->createView($params['id']);
    }

   public function onSaveEdited_FILE($params)
    {

        if (file_exists($filePath = xConfig::get('PATH', 'TEMPLATES') . $params['data']['path'])) {

            $handle = fopen($filePath, "w");

            if (!fwrite($handle, $params['data']['filebody'])) {

                return new badResult('error-writing-file');

            } else {


                fclose($handle);
                return new okResult('file-write-success');
            }

        }
    }

    public function onEdit_FILE($params)
    {

        $file = base64_decode($params['id']);

        if (file_exists($file)) {
            $this->result['data']['path'] = str_replace(xConfig::get('PATH', 'TEMPLATES'), '', $file);

            $this->result['data']['filebody'] = file_get_contents($file);

        }
    }


    public function onSearchInModule($params)
    {
        $it = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'] . '/templates');
        $notDisplay = Array('jpeg', 'png');
        foreach (new RecursiveIteratorIterator($it) as $file) {
            if (strpos($file->getFilename(), $params['word']) !== false && !in_array(pathinfo($file->getPathname())['extension'], $notDisplay)) {
                $paths['data'][0] = substr($file->getPathname(), strlen($_SERVER['DOCUMENT_ROOT'] . '/templates') + 1);
                $paths['data'][1] = round(filesize($file->getPathname()) / 1024, 2) . ' Kb';
                $this->result['searchResult']['rows'][base64_encode($file->getPathname())] = $paths;
            }
        }
    }


}
