<?php

use X4\Classes\XTreeEngine;
use X4\Classes\XRegistry;
use X4\Classes\MultiSection;
use X4\Classes\XPDO;

class commentsFront extends xModule
{

    public function __construct()
    {
        parent::__construct(__CLASS__);

        if (xConfig::get('GLOBAL', 'currentMode') == 'front') {
            $this->_tree->cacheState($this->_config['cacheTree']);
        }
    }




    //xfront compat
    /*
    *   1-tread not active 
    *   2-cobject not active or closed
    */

    public function _getComments($params)
    {
        if ($tread = $this->_commonObj->getTreadByName($params['tread'])) {
            if ($tread['params']['active']) {

                if ($cobj = $this->_commonObj->getCobjectByTread($params['cobjectId'], $tread['id'])) {
                    if ($cobj['params']['active'] && !($cobj['params']['closed'])) {
                        ($params["count"]) ? $limit = ' limit 0, ' . $params['count'] : $limit = '';
                        ($params["asc"] == "desc") ? $order = '-' : $order = '';

                        $comments = XPDO::selectIN('*', 'comments', 'cid=' . $cobj['id'] . ' and active=1',
                            'order by ' . $order . 'date ' . $tread['params']['treadSort'] . $limit);

                        if ($comments) {

                            foreach ($comments as $key => $comment) {

                                $numToId[$comment['id']] = $key;
                                $comment['replies'] = false;
                                if ($comment['replyId']) {
                                    $replies[$comment['replyId']][] = $comment;

                                } else {

                                    $commentsOnly[$key] = $comment;
                                }

                            }

                            if ($replies) {
                                foreach ($replies as $commentId => $reply) {
                                    if (isset($commentsOnly[$numToId[$commentId]])) {
                                        $commentsOnly[$numToId[$commentId]]['replies'] = $reply;
                                    }
                                }
                            }

                        }


                        return $commentsOnly;


                    } else {
                        return 'cobject-non-active-or-closed';
                    }
                }
            } else {
                return 'tread-non-active';
            }
        }
    }

    public function checkCaptchaCode($params)
    {
        if ($_SESSION['captcha'][$params['tread']] == $params['captcha']) {
            $this->result['captcha'] = true;
            return true;
        } else {
            $this->result['captcha'] = false;
            return false;
        }
    }


    /*
    * Tread индефицируется по basic'у
    * ('_COBJECT',array('LastModified','Module','Marker','Active','Closed','CobjectId'),'_TREAD');
    *  return 
    *  1 - tread  do not exists       
    *  2 -tread closed
    *  3 - comment success
    */

    public function _addComment($cobjectData, $commentData)
    {

        if ($tread = $this->_commonObj->getTreadByName($cobjectData['tread'])) {
            if ($tread["params"]["captcha"]) {
                if (isset($commentData["captcha_" . $tread["id"]])) {
                    if ($this->checkCaptchaCode(array
                    (
                        "tread" => (int)$tread["id"],
                        "captcha" => $commentData["captcha_" . $tread["id"]]
                    ))
                    ) {
                        unset ($commentData["captcha_" . $tread["id"]]);
                        unset ($_SESSION["captcha"][$tread["id"]]);
                    } else
                        return 3;
                }
            } else {

                unset ($comment["captcha_" . $tread["id"]]);
            }


            if ($tread['params']['active']) {
                if (!$cobject = $this->_commonObj->getCobjectByTread($cobjectData['cobjectId'], $tread['id'])) {

                    $cobjectData['active'] = 1;
                    $cid = $cobjectData['cid'] = $this->initCobject($tread['id'], $cobjectData);
                } else {
                    $cobjectData['cid'] = $cid = $cobject['id'];
                }

                if ($tread['params']['moderation']) {
                    $commentData['active'] = 0;
                } else {
                    $commentData['active'] = 1;
                }


                //if (!isset($commentData["header"]))$commentData["header"]='No subject';

                if ($_SESSION["siteuser"]["authorized"]) {
                    $commentData["userName"] = $_SESSION["siteuser"]["userdata"]["Name"]; //!! CHECK FUSERS TROUBLE
                }


                $object = XRegistry::get('EVM')->fire($this->_moduleName . '.addComment:before', array('tread' => $tread, 'cobject' => $cobjectData, 'comment' => $commentData));


                if ($this->lastAddedComment = $this->initComment($cid, $commentData)) {

                    $_SESSION['comments']['lastComment'] = array
                    (
                        'tread' => $cobjectData['tread'],
                        'commentid' => $this->lastAddedComment
                    );

                    $this->loadModuleTemplate('commentToMail.html');

                    $m = xCore::incModuleFactory('Mail');
                    $m->From(xConfig::get('GLOBAL', 'admin_email'));
                    $m->To(xConfig::get('GLOBAL', 'admin_email'));
                    $m->Content_type('text/html');
                    $m->Subject('website comment');
                    $this->_TMS->addMassReplace('commentToMail', array('tread' => $tread, 'cobject' => $cobjectData, 'comment' => $commentData));
                    $m->Body($this->_TMS->parseSection('commentToMail'), xConfig::get('GLOBAL', 'siteEncoding'));
                    $m->Priority(2);
                    $m->Send();
                    $object = XRegistry::get('EVM')->fire($this->_moduleName . '.addComment:after', array('tread' => $tread, 'cobject' => $cobjectData, 'comment' => $commentData));
                    $this->result["commentAdded"] = true;

                    return 0;
                }
            } else {
                return 2;
            }
        }

        return 1;
    }


    public function initComment($id, $data)
    {
        // проверка на авторизацию. сессия после логаута почему-то не вычищается

        if ($_SESSION["siteuser"]["authorized"]) {
            if (!$data['userId']) $data['userId'] = $_SESSION['siteuser']['id'];
            if (!$data['userName']) $data['userName'] = $_SESSION['siteuser']['Name'];
        }


        if (!$data['userId']) $data['userId'] = 'NULL';
        if (!$data['replyId']) $data['replyId'] = 'NULL';

        $data['date'] = $data['lastModified'] = time();
        $data['cid'] = $id;
        $data['message'] = XHTML::xssClean($data['message']);
        $data['userName'] = XHTML::xssClean($data['userName']);
        $data['email'] = XHTML::xssClean($data['email']);
        $data['header'] = XHTML::xssClean($data['header']);


        $last = XPDO::insertIN('comments', $data);
        return XPDO::getLastInserted();

    }


    public function initCobject($id, $data)
    {
        $data['closed'] = '';
        return $this->_tree->initTreeObj($id, '%SAMEASID%', '_COBJECT', $data, true);
    }
}