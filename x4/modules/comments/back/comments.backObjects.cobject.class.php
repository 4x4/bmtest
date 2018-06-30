<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;


trait _COBJECT
{

    public function onEdit_COBJECT($params)
    {
        if ($node = $this->_tree->getNodeInfo($params['id'])) {
            $this->result['cobjectData'] = $node['params'];
        }


    }

    public function deleteComments($data)
    {

        $query = 'delete from comments where cid in ' . '(\'' . implode($data, "','") . '\')';
        $this->_PDO->exec($query);

    }

    public function deleteCobject($data)
    {
        $this->deleteComments($data['id']);
        $this->_tree->delete()->where(array('@id', '=', $data['id']))->run();
        $this->result['deletedList'][] = $data['id'];

    }


    public function cobjectsTable($params)
    {
        $source = Common::classesFactory('TreeJsonSource', array(
            $this->_tree
        ));

        $opt = array(
            'showNodesWithObjType' => array(
                '_COBJECT'
            ),
            'onPage' => $params['onPage'],
            'columns' => array(
                'id' => array(),
                '>module' => array(),
                '>marker' => array(),
                '>active' => array(),
                '>closed' => array()
            )
        );

        if (!$params['page']) $params['page'] = 1;
        $source->setOptions($opt);

        $this->result = $source->createView($params['id'], $params['page']);

    }


    public function switchCobjectActive($params)
    {
        $this->_tree->writeNodeParam($params['id'], 'active', $params['state']);
        $this->pushMessage('cobject-state-changed');

    }

    public function switchCobjectClosed($params)
    {
        $this->_tree->writeNodeParam($params['id'], 'closed', $params['state']);
        $this->pushMessage('cobject-state-changed');
    }

}

?>