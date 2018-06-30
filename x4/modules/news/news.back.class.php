<?php
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;
use X4\Classes\TableJsonSource;
use X4\Classes\TagManager;


class newsBack
    extends xModuleBack
{
    function __construct()
    {
        parent::__construct(__CLASS__);
    }

    public function onCreate_NEWS($params)
    {
        $this->result['categories'] = $this->getNewsCategories();
        $this->result['data']['tags'] = TagManager::getTagsChosen();
    }

    private function getNewsCategories()
    {
        if ($categories = $this->_commonObj->getCategories()) {
            return XARRAY::arrToLev($categories, 'id', 'params', 'Name');
        }
    }

    public function onSave_NEWS($params)
    {

        $data = $params['data'];
        $data['categories'] = $this->keysToStr($data['categories']);

        $data['news_date'] = XDATE::rusDateToTimeStamp($data['news_date']);

        if(empty($data['author']))
        {
            $data['author_id']='NULL';
            $data['author_type']='users';
        }

        if ($data['news_end']) {
            $data['news_end'] = XDATE::rusDateToTimeStamp($data['news_end']);
        }else{
            $data['news_end']='NULL';
        }


        if (!empty($data['tags'])) {
            $data['tags'] = TagManager::tagsToLine($data['tags']);
        }else{
            $data['tags']='';
        }

        $data['active'] = 1;
        $data['id'] = 'NULL';

        if (XPDO::insertIN('news', $data)) {
            return new okResult();
        } else {
            return new badResult('news-do-not-written');
        }
    }

    /**
     * 2 типа аттрибутов
     * группы и поля
     * группы могут содержать в себе поля, которые можно реплицировать на фронте
     */

    private function keysToStr($categories)
    {
        if (isset($categories)) {
            foreach ($categories as $key => $category) {
                if ($category)
                    $keys[] = $key;
            }

            return implode(',', $keys);
        }
    }

    public function onEdit_NEWS($params)
    {
        if ($news = XPDO::selectIN('*', 'news', (int)$params['id'])) {
            $news = $news[0];


            $news['news_date'] = date('d/m/Y h:m:s', $news['news_date']);

            if ($news['news_end']) {
                $news['news_end'] = date('d/m/Y h:m:s', $news['news_end']);
            } else {
                $news['news_end'] = '';
            }
            if ($news['author_id']) {
                $fusers = xCore::moduleFactory('fusers.back');
                $path = $fusers->_tree->selectStruct(array('id'))->getParamPath('Name')->where(array
                ('@id', '=', $news['author_id']))->run();

                $author = $fusers->_tree->getNodeInfo($news['author_id']);

                $news['author'] = $path['paramPathValue'] . $author['params']['name'] . ' ' . $author['params']['surname'] . ' ' . $author['params']['email'];
            }


            $news['tags'] = TagManager::getTagsChosen(json_decode($news['tags']));


            $this->result['selectedCategories'] = explode(',', $news['categories']);
            $this->result['categories'] = $this->getNewsCategories();

            $this->result['data'] = $news;
        }
    }

    public function onSaveEdited_NEWS($params)
    {


        $data = $params['data'];
        $data['categories'] = $this->keysToStr($data['categories']);
        $data['news_date'] = XDATE::rusDateToTimeStamp($data['news_date']);

        if ($data['news_end'])
            $data['news_end'] = XDATE::rusDateToTimeStamp($data['news_end']);


        if ($data['tags'])
            $data['tags'] = TagManager::tagsToLine($data['tags']);


        if ($x = XPDO::updateIN('news', (int)$params['id'], $data)) {
            return new okResult('edited-news-saved');
        } else {
            return new badResult('news-do-not-written');
        }
    }

    public function onSave_NEWSGROUP($params)
    {
        if ($id = $this->_tree->initTreeObj(1, '%SAMEASID%', '_NEWSGROUP', $params['data'])) {
            return new okResult('news-saved');
        }
    }

    public function onSaveEdited_NEWSGROUP($params)
    {
        if ($id = $this->_tree->reInitTreeObj($params['id'], '%SAME%', $params['data']))
            ;

        {
            return new okResult('news-edited-saved');
        }
    }

    public function onEdit_NEWSGROUP($params)
    {
        $node = $this->_tree->getNodeInfo($params['id']);
        $this->result['data'] = $node['params'];
    }

    public function treeDynamicXLS($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array($this->_tree));

        $opt = array
        (
            'imagesIcon' => array('_NEWSGROUP' => 'folder.gif'),
            'gridFormat' => true,
            'showNodesWithObjType' => array
            (
                '_ROOT',
                '_NEWSGROUP'
            ),
            'columns' => array('>Name' => array())
        );

        $source->setOptions($opt);
        $this->result = $source->createView($params['id']);
    }

    public function setNewsActive($params)
    {
        $state = ($params['state']) ? 1 : 0;

        if (XPDO::updateIN('news', (int)$params['id'], array('active' => $state))) {
            return new okResult('news-state-changed');
        }
    }

    public function newsTable($params)
    {


        $source = Common::classesFactory('TableJsonSource', array());

        $params['onPage'] = $this->_config['showNewsPerPageAdmin'];

        if (!empty($params['id'])) {
            $where = ' FIND_IN_SET(' . $params['id'] . ',categories)';
        }


        $opt = array
        (
            'onPage' => $params['onPage'],
            'table' => 'news',
            'order' => array
            (
                'news_date',
                'desc'
            ),
            'where' => $where,
            'idAsNumerator' => 'id',

            'columns' => array
            (
                'id' => array(),
                'news_date' => array
                (
                    'onAttribute' => TableJsonSource::$fromTimeStamp,
                    'onAttributeParams' => array('format' => 'd.m.y H:i:s')
                ),
                'header' => array(),
                'author_id' => array(),
                'basic' => array(),
                'active' => array()
            )
        );

        $source->setOptions($opt);


        if (!$params['page']) $params['page'] = 1;

        $this->result = $source->createView($params['id'], $params['page']);

    }

    public function deleteNews($params)
    {

        if (is_array($params['id'])) {
            $id = implode($params['id'], "','");
            $w = 'id in (\'' . $id . '\')';
        } else {
            $w = 'id="' . $params['id'] . '"';
        }

        $query = 'delete from news where ' . $w;

        if ($this->_PDO->query($query)) {
            $this->result['deleted'] = true;
        }
    }

    public function onAction_newsServer($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName, $params['data']['params']['Template'], array('.newsServer.html'));


        $this->result['actionDataForm']['TemplateInterval'] = Common::getModuleTemplateListAsSelector($this->_moduleName,
            $params['data']['params']['TemplateInterval'],
            array('.showNewsInterval.html'));
        $this->result['actionDataForm']['secondaryAction'] = XHTML::arrayToXoadSelectOptions(
            $this->_commonObj->getServerActionsFull($params['action']), $selected, true);
    }

    public function onAction_showNewsInterval($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName,
            $params['data']['params']['Template'],
            array('.showNewsInterval.html'));

        $this->result['actionDataForm']['Categories'] = XHTML::arrayToXoadSelectOptions($this->getNewsCategories(),
            $selected);

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('newsServer');
    }


    public function onAction_showCorrespodingTags($params)
    {
        if (isset($params['data']['params'])) {
            $this->result['actionDataForm'] = $params['data']['params'];
        }

        $this->result['actionDataForm']['Template'] = Common::getModuleTemplateListAsSelector($this->_moduleName,
            $params['data']['params']['Template'],
            array('.showCorrespodingTags.html'));

        $pages = xCore::loadCommonClass('pages');
        $this->result['actionDataForm']['DestinationPage'] = $pages->getPagesByModuleServerSelector('newsServer');
    }


}


?>