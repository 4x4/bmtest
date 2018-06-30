<?php


use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XCache;
use X4\Classes\XPDO;
use X4\Classes\TableJsonSource;


trait _COMMENT
{

    public function onEdit_COMMENT($params)
    {

        if ($cval = XPDO::selectIN('*', 'comments', (int)$params['data']['id'])) {
            $this->result['commentData'] = $cval[0];
        }
    }

    public function onSaveEdited_COMMENT($params)
    {

        if ($cval = XPDO::updateIN('comments', (int)$params['id'], $params['data'])) {
            $this->pushMessage('comment-saved');
            XRegistry::get('EVM')->fire($this->_moduleName . '.onSaveEdited_COMMENT', array('commentId' => $params['id'], 'params' => $params['data']));
        }
    }

    public function onSaveReply_COMMENT($params)
    {

        // debugbreak();
        if ($cval = XPDO::selectIN('*', 'comments', (int)$params['id'])) {

            $params['data']['replyId'] = $params['id'];
            $params['data']['id'] = 'NULL';
            $params['data']['cid'] = $cval[0]['cid'];
            $params['data']['date'] = $params['data']['lastModified'] = time();

            XRegistry::get('EVM')->fire($this->_moduleName . '.onSaveReply_COMMENT', array('targetComment' => $cval[0], 'params' => $params['data']));

            XPDO::insertIN('comments', $params['data']);
            $this->pushMessage('reply-saved');
        }


    }


    public function notifyOnSaveReply($params, $dta)
    {
        if ($email = $params['data']['targetComment']['email']) {
            $this->loadModuleTemplate('_COMMENT_reply.html');
            $m = xCore::incModuleFactory('Mail');
            $m->From(xConfig::get('GLOBAL', 'admin_email'));
            $m->To($email);
            $m->Content_type('text/html');
            $m->Subject($this->_commonObj->translateWord('you_get_reply'));
            $this->_TMS->addMassReplace('ishop_cart_email', 'data', array('targetComment' => $params['data']['targetComment'],
                'comment' => $params['data']['params']));
            $m->Body($this->_TMS->parseSection('replyEmailMessage'));
            $m->Priority(2);
            $m->Send();

        }


    }

    public function onSave_COMMENT($params)
    {

        $params['data']['id'] = 'NULL';

        if (XPDO::insertIN('comments', $params['data'])) {
            XRegistry::get('EVM')->fire($this->_moduleName . 'onSave_COMMENT', array('id' => $params['id']));
            $this->pushMessage('new-comment-saved');

        }

    }


    public function deleteCommentsList($params)
    {

        if (is_array($params['id'])) {
            $id = implode($params['id'], "','");

            $w = 'replyId in (\'' . $id . '\') or id in (\'' . $id . '\')';
        } else {
            $w = 'replyId="' . $params['id'] . '" or id="' . $params['id'] . '"';
        }

        $query = 'delete from comments where ' . $w;

        XRegistry::get('EVM')->fire($this->_moduleName . '.deleteCommentsList:before', array('delete' => $params['id']));

        if ($this->_PDO->query($query)) {
            $this->result['deletedList'] = true;
            XRegistry::get('EVM')->fire($this->_moduleName . '.deleteCommentsList:after', array('delete' => $params['id']));
        }

    }


    public function switchComment($params)
    {


        if (XPDO::updateIN('comments', (int)$params['id'], array('active' => (int)$params['state']))) {
            $this->pushMessage('comment-state-saved');
        } else {

        }

    }


    public function resultSetChanger()
    {

        $that = $this;

        $resultSetChanger = function ($set) use (&$that) {

            if (is_array($set)) {
                $replies = XARRAY::arrToLev($set, 'id', 'data', 7);

            }

            if (isset($set)) {
                foreach ($set as $setItem) {
                    $z++;
                    if (!$setItem['data'][7]) {

                        if (in_array($setItem['data'][0], $replies)) {
                            $setItem['xmlkids'] = 1;
                        }

                        $setItem['obj_type'] = '_COMMENT';

                        $data[$z] = $setItem;

                    }

                }
            }

            return $data;
        };


        return $resultSetChanger;

    }


    public function commentsTable($params)
    {
        //debugbreak();
        //$params['onPage'] = 1000;
        $source = Common::classesFactory('TableJsonSource', array());


        if ($params['getReplies']) {
            $where = 'replyId=' . $params['id'];

        } else {

            $where = 'cid=' . $params['id'];
        }


        $opt = array
        (
            'onPage' => $params['onPage'],
            'table' => 'comments',

            'order' => array
            (
                'date',
                'desc'
            ),
            'where' => $where,
            'gridFormat' => 1,

            'columns' => array
            (
                'id' => array(),
                'date' => array
                (
                    'onAttribute' => TableJsonSource::$fromTimeStamp,
                    'onAttributeParams' => array('format' => 'd.m.y H:i:s')
                ),
                'userName' => array(),
                'email' => array(),
                'message' => array(),
                'rating' => array(),
                'active' => array(),
                'replyId' => array()
            )
        );

        if (!$params['getReplies']) {
            $opt['onResultSet'] = $this->resultSetChanger();
        }


        $source->setOptions($opt);
        if (!$params['page']) $params['page'] = 1;

        $this->result = $source->createView($params['id'], $params['page']);

    }


}

?>