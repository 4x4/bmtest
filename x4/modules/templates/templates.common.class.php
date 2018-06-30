<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;

/**
 * !Внимание для корректной работы папка /cache/templates  - должна быть достпна для записи
 * Порядок подключения шаблонов главный шаблон->шаблон домена->обычный шаблон либо
 * главный шаблон->шаблон домена для языковой версии -> обычный шаблон
 * _index.html->/mydomain.com/_index.html/mycustomtemplate.html
 * языковой вариант:
 * _index.html->/mydomain.ru/_index@ru.html/mycustomtemplate@ru.html
 *
 */
class templatesCommon extends xModuleCommon implements xCommonInterface
{
    var $registeredFields;
    var $changedMainTpls;
    private $nonChangedTpls;
    private $mainTemlateChanged;

    public function __construct()
    {
        parent::__construct(__CLASS__);
        Common::loadDriver('XCache', 'XCacheFileDriver');
    }

    public function defineFrontActions()
    {
    }

    /**
     * записать данные о шаблоне
     *
     * @param mixed $tplPath
     * @param mixed $data array(time=>time,slotz=>('name'=>'alias'))
     */
    public function setTplData($tplPath, $data)
    {

        XCacheFileDriver::serializedWrite($data, $this->_moduleName, $tplPath, false);
    }

    public function getTplData($tplPath)
    {
        return XCacheFileDriver::serializedRead($this->_moduleName, $tplPath, false);
    }

    public function getTpl($tpl, $domain)
    {
        return XCacheFileDriver::serializedRead($this->_moduleName, $domain . '/' . $tpl, false);
    }


    public function getTemplatesForDomain($domain)
    {
        if ($allTemplates = XFILES::filesList(xConfig::get('PATH', 'TEMPLATES') . $domain.'/_common/', 'files', array('.html'))) {
            foreach ($allTemplates as $tpl) {

                $tpls[] = $this->getTpl(basename($tpl), $domain);
            }

            return $tpls;
        }
    }

    public function getSlotzForDomain($domain, $lang = '')
    {


        if ($allTemplates = $this->getTemplatesForDomain($domain)) {

            $mSlotz = array();

            foreach ($allTemplates as $tpl) {
                $mSlotz = array_merge($tpl['slotz'], $mSlotz);
            }

            return array_unique($mSlotz);
        }
    }


    /**
     * Индексация шаблонов у которых прозошли изменения
     *
     * @param mixed $startdir
     */
    public function indexChangedMainTpls($startdir,$domain)
    {
        $changedMainTpls = null;


        if ($allTemplates = XFILES::filesList($startdir, 'all', array('.html'), true)) {
            foreach ($allTemplates as $file) {
                if (strstr($file, '.html')) {
                    $fmtime = filemtime($file);
                    $template = str_replace($startdir . '/', '', $file);

                    $tplData = $this->getTplData($template);


                    if (isset($template)) {
                        preg_match("/(.+)@(.+)\.html/", $template, $tplExp);


                        if (strstr($template, '_index')) {
                            $isMain = true;
                        } else {
                            $isMain = false;
                        }

                        if ($template == '_index.html') {
                            $this->mainTemlateChanged = true;
                        }

                        if (($fmtime > $tplData['lastModified']) or !$tplData) {
                            $changedMainTpls[$domain][] = array(
                                'tpl' => $template,
                                'main' => $isMain
                            );
                        } else {
                            $this->nonChangedTpls[$domain][] = array(
                                'tpl' => $template,
                                'main' => $isMain
                            );
                        }
                    }
                }
            }
            if ($changedMainTpls) {
                foreach ($changedMainTpls as $domain => $tpls) {
                    foreach ($tpls as $tpl) {
                        if ($tpl['main']) {
                            if ($this->nonChangedTpls[$domain]) {
                                foreach ($this->nonChangedTpls[$domain] as $itpl) {
                                    if (!$itpl['main']) {
                                        $changedMainTpls[$domain][] = $itpl;
                                    }
                                }
                            }
                        }
                    }
                }
            }



            return $changedMainTpls;
        }
    }

    public function refreshMainTpls()
    {

        $domains=XFILES::directoryList(xConfig::get('PATH', 'TEMPLATES'));

        if(!empty($domains)){
        foreach($domains as $domain) {
            if ($this->changedMainTpls = $this->indexChangedMainTpls(xConfig::get('PATH', 'TEMPLATES') . $domain . '/_common/', $domain)) {

                if ($this->mainTemlateChanged) {
                    $this->processMainTemplate();
                    return;
                }

                if (!empty($this->changedMainTpls)) {
                    foreach ($this->changedMainTpls as $tplDomain => $tpl) {
                        $this->processTemplate($tplDomain, $tpl);
                    }
                }
            }
        }
        }
    }

    /**
     * переиндексация всех шаблонов в случае изменения глобального
     */
    public function processMainTemplate()
    {

        foreach ($this->changedMainTpls as $tplDomain => $tpl) {
            $this->processTemplate($tplDomain, $tpl);
        }

        if ($this->nonChangedTpls) {
            foreach ($this->nonChangedTpls as $tplDomain => $tpl) {
                $this->processTemplate($tplDomain, $tpl);
            }
        }
    }

    /**
     *  переиндексация шаблонов согласно домену
     */

    public function processTemplate($tplDomain, $tpls)
    {
        foreach ($tpls as $tpl) {

            $TMS = new MultiSection();

            $tplFullPath = xConfig::get('PATH', 'TEMPLATES') . $tplDomain . '/_common/' . $tpl['tpl'];
            //слоты шаблона
            $name = $TMS->addFileSection($tplFullPath);

            //если не относиться к главным шаблонам                
            if (!$tpl['main']) {
                if ($tpl['lang']) {
                    $lang = '@' . $tpl['lang'];
                }
                $tplMainForDomain = xConfig::get('PATH', 'TEMPLATES') . $tplDomain . '/_common/_index' . $lang . '.html';
                $TMS->addFileSection($tplMainForDomain);
            }

            $tplData = array(
                'lastModified' => time(),
                'slotz' => $TMS->MainFields,
                'name' => $name,
                'lang' => $tpl['lang'],
                'path' => $tplDomain . '/' . $tpl['tpl']
            );

            $this->setTplData($tplDomain . '/' . $tpl['tpl'], $tplData);
        }
    }
}