<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XPDO;

class commentsTpl
    extends xTpl
    implements xModuleTpl
{

    public function showComments($params)
    {

        if (isset($params['Template'])) {
            $this->loadModuleTemplate($params['Template']);
        }
        $dataComment = json_encode(array('tread' => $params['tread'], 'route' => $params['route'], 'marker' => $params['marker'], 'cobjectId' => $params['cobjectId']));

        $this->_TMS->addReplace('sendCommentsForm', 'data-comment', $dataComment);

        $result['commentsForm'] = $this->_TMS->parseSection('sendCommentsForm');


        if ($comments = $this->_getComments(array('tread' => $params['tread'], 'cobjectId' => $params['cobjectId']))) {
            $this->_TMS->addMassReplace('comments', array('tread' => $params['tread'], 'cobjectId' => $params['cobjectId']));

            if (is_array($comments)) {
                foreach ($comments as $comment) {
                    $this->_TMS->addMassReplace('comment', $comment);
                    $this->_TMS->parseSection('comment', true);
                }

                $result['comments'] = $this->_TMS->parseSection('comments');

            } elseif ($comments == 'tread-non-active') {
                $result['error'] = $this->_TMS->parseSection('tread_not_active');

            } elseif ($comments == 'cobject-non-active-or-closed') {
                $result['error'] = $this->_TMS->parseSection('object_not_active');
            }
        } else {

            $result['comments'] = $this->_TMS->parseSection('no-comments');
        }

        return $result;
    }


    public function getCommentedObject($params)
    {
        if ($params['id']) {
            $node = $this->_tree->getNodeInfo($params['id']);

            return $node['params'];
        }
    }


    public function countComments($params)
    {

        if ($cobj = $this->_commonObj->getCobjectByTread($params['id'], null, $params['tread'])) {
            $groupBy = '';

            if (isset($params['unique']) && $params['unique']) {
                $groupBy = 'group by userId';
            }

            if ($result = XRegistry::get('XPDO')->query('select count(id) as ccount from comments where replyId is NULL and active=1 and cid=' . $cobj['id'] . ' ' . $groupBy)) {
                $row = $result->fetch(PDO::FETCH_ASSOC);

                return $row['ccount'];

            }

        }

        return 0;

    }


}
