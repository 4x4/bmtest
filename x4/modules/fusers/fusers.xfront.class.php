<?php

use X4\Classes\XPDO;

class fusersXfront extends fusersFront
{

    public function isAuthorized($params)
    {
        return $_SESSION['siteuser']['authorized'];
    }

    public function addFavorite($params)
    {
        if(!empty($_SESSION['siteuser']['id']) && !empty($params['obj_id']) && is_numeric($params['obj_id'])) {
            $favorite = array(
                'id' => 'null',
                'user_id' => (int)$_SESSION['siteuser']['id'],
                'obj_id' => (int)$params['obj_id']
            );

            XPDO::insertIN('favorite', $favorite);
            $favid = XPDO::getLastInserted();

            if(!isset($_SESSION['siteuser']['favorites'])) {
                $_SESSION['siteuser']['favorites'] = array();
            }

            $_SESSION['siteuser']['favorites'][$favid] = $params['obj_id'];
        }

    }

    public function delFavorite($params)
    {
        if(!empty($params['id']) && is_numeric($params['id'])) {
            XPDO::deleteIN('favorite', (int)$params['id']);
            unset($_SESSION['siteuser']['favorites'][$params['id']]);
        } else if(!empty($params['obj_id']) && is_numeric($params['obj_id']) && !empty($_SESSION['siteuser']['id'])) {
            XPDO::deleteIN('favorite', 'obj_id = "'.$params['obj_id'].'" AND user_id = "'.$_SESSION['siteuser']['id'].'"');
            $favid = array_search($params['obj_id'], $_SESSION['siteuser']['favorites']);
            unset($_SESSION['siteuser']['favorites'][$favid]);
        }
    }

    public function delAllFavorites()
    {
        if(!empty($_SESSION['siteuser']['id'])) {
            XPDO::deleteIN('favorite', 'user_id = "'.$_SESSION['siteuser']['id'].'"');
            unset($_SESSION['siteuser']['favorites']);
        }
    }
}
