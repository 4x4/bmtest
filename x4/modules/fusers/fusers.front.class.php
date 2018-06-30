<?php

use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

class fusersFront extends xModule
{

    public function __construct()
    {
        parent::__construct(__CLASS__);

    }


    public function showAuthPanel($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $link = $this->getLink($params['params']['userPanelPage']);

        if ($_SESSION['siteuser']['authorized']) {
            $this->_TMS->addMassReplace('authorizedPanel', array
            (
                'userPanelLink' => $link . '/~userPanel',
                'logout' => $link . '/~logout',
                'siteUser' => $_SESSION['siteuser'],
                'Link' => $link
            ));

            return $this->_TMS->parseSection('authorizedPanel');
        } else {
            $this->_TMS->addMassReplace('authPanel', array
            (
                'authAction' => $link . '/~login',
                'registrationLink' => $link . '/~registration',
                'forgotPasswordLink' => $link . '/~forgotPassword'
            ));

            return $this->_TMS->parseSection('authPanel');
        }

    }


    public function showLogin($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $link = $this->getLink($params['params']['userPanelPage']);

        if ($_SESSION['siteuser']['authorized']) {
            $this->_TMS->addMassReplace('authorizedPanel', array
            (
                'userPanelLink' => $link . '/~userPanel',
                'logout' => $link . '/~logout'
            ));

            return $this->_TMS->parseSection('authorizedPanel');
        } else {
            $this->_TMS->addMassReplace('authPanel', array
            (
                'authAction' => $link . '/~login',
                'registrationLink' => $link . '/~registration',
                'forgotPasswordLink' => $link . '/~forgotPassword'
            ));

            return $this->_TMS->parseSection('authPanel');
        }

    }


    public function registration($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
       
	    $link = $this->getLink();
        $this->_TMS->addMassReplace('registration', array('actionNewUserLink' => $link . '/~submitUser'));

        if (!empty($params['errors'])) {
            $this->_TMS->addReplace('registration', 'errors', $params['errors']);
        }

        if ($userdata['extendedUserData']) {
            $userdata = array_merge($userdata, $userdata['ext_user_data']);
            unset($userdata['ext_user_data']);
        }

        if (!empty($params['data'])) {
            $this->_TMS->addMassReplace('registration', $params['data']);
        }

        return $this->_TMS->parseSection('registration');
    }


    public function validateUserData($data)
    {
        if (isset($data['captcha']) && $_SESSION['captcha']['registration'] != $data['captcha']) {
            $this->pushError('captcha-not-valid');
        }

        if (($data['login']) && ($data['password'])) {
            if ($data['passwordAgain'] != $data['password']) {
                $this->pushError('passwords-not-equal');
            }
        } else {
            $this->pushError('login-or-password-not-provided');
        }
    }


    public function saveProfile($params)
    {
        $checkedData = $this->_commonObj->checkUserLoginAndEmail(null, $_POST['email']);
        $this->loadModuleTemplate($params['params']['Template']);

        if(!empty($checkedData['isEmail'])) {
            if($checkedData['isEmail']['id'] == $_SESSION['siteuser']['id']) {
                unset($checkedData['isEmail']);
            }
        }

        if(!empty($_POST['password']) && !empty($_POST['passwordAgain'])) {
            if ($_POST['passwordAgain'] != $_POST['password']) {
                $this->pushError('passwords-not-equal');
            } else {
                $_POST['password'] = md5(strrev($_POST['password']));
                unset($_POST['passwordAgain']);
            }

            if ($this->isErrors()) {
                unset($_POST['password'], $_POST['passwordAgain'], $_POST['code']);
                return $this->editUser($params, $this->errors, $_POST);
            }
        } else {
            unset($_POST['password'], $_POST['passwordAgain']);
        }

        if(!empty($_POST['additionalFields'])) {
            foreach($this->_config['additionalFields'] as $k => $v) {
                if(!isset($_POST['additionalFields'][$v['fieldName']]) && $v['type'] == 'checkbox') {
                    $_POST['additionalFields'][$v['fieldName']] = '';
                }
            }
        }


        if ((!$checkedData['isLogin']) && (!$checkedData['isEmail'])) {
            if ($this->_tree->reInitTreeObj($_SESSION['siteuser']['id'], '%SAME%', $_POST)) {
                $node = $this->_tree->getNodeInfo($_SESSION['siteuser']['id']);
                $_SESSION['siteuser']['userdata'] = $node['params'];
                    if(!empty($_POST['additionalFields'])) {
                        $this->_commonObj->initAdditionalFields($_SESSION['siteuser']['id'],$_POST['additionalFields']);
                        $_SESSION['siteuser']['userdata']['additionalFields'] = $_POST['additionalFields'];
                    }
            } else {
                $errors['save-user-error-internal'] = 1;
            }
        } else {
            unset($_POST['password']);
            unset($_POST['passwordAgain']);

            if ($email) {
                $errors['save-user-non-uniq-email'] = 1;
                unset($_POST['email']);
            }
        }

        if (count($errors) > 0) {
            return $this->editUser($params, $errors, $_POST);
        } else {
            $params['profileSaved'] = true;
            xRegistry::get('EVM')->fire($this->_moduleName . '.profileSaved', array('user' => $_SESSION['siteuser']));
            return $this->userPanel($params);
        }
    }


    public function destroyUser($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $code = ($_GET['code']);

        if ($code) return $this->_TMS->parseSection('fuser_account_not_exists');
        if ($is = $this->_tree->JoinSearch(array(array
        (
            'VerificationCode',
            $code
        )))
        ) {
            list($id, $data) = each($is);
            $this->_tree->DelNode($id);
            return $this->_TMS->parseSection('fuser_account_deleted');
        }
    }


    public function pushError($error)
    {
        $this->errors[] = $error;
    }

    public function isErrors()
    {
        return count($this->errors);
    }


    public function _submitUser($params)
    {
        $this->validateUserData($params['data']);

        if (!$this->isErrors()) {
            $checkedData = $this->_commonObj->checkUserLoginAndEmail($params['data']['login'], $params['data']['email']);

            if ($params['params']['doNotVerifyUser']) {
                $defalutGroup = 'defaultRegisteredGroup';
                $params['data']['Active'] = 1;
            } else {
                $defalutGroup = 'defaultUnregisteredGroup';
                unset($params['data']['Active']);
            }

            if ((!$checkedData['isLogin']) && (!$checkedData['isEmail'])) {

                if ($userGroupId = $this->_tree->readNodeParam(1, $defalutGroup)) {

                    $params['data']['password'] = md5(strrev($params['data']['password']));

                    if ($uid = $this->_tree->initTreeObj($userGroupId, $params['data']['login'], '_FUSER', $params['data'])) {

                        if ($params['data']['additionalFields'])
                        {
                            $this->_commonObj->initAdditionalFields($uid, $params['data']['additionalFields']);
                        }

                        $link = $this->getLink();
                        $params['data']['id'] = $uid;

                        $data = xRegistry::get('EVM')->fire($this->_moduleName . '.userRegistered', array('link' => $link, 'user' => $params['data']));

                        if (isset($params['params']['useEmailVerify'])) {
                            $vcode = Common::generateHash('uv');
                            $m = xCore::incModuleFactory('Mail');
                            $this->_TMS->addMassReplace('registrationMailText', array
                            (
                                'name' => $params['data']['name'],
                                'surname' => $params['data']['surname'],
                                'patronymic' => $params['data']['patronymic'],
                                'login' => $params['data']['login'],
                                'password' => $params['data']['password'],
                                'HOST' => HOST,
                                'verifyUrl' => $link . '/~verifyUser/?code=' . $vcode,
                                'destroyUrl' => $link . '/~destroyUser/?code=' . $vcode,
                                'vcode' => $vcode
                            ));

                            $adminEmail = xConfig::get('GLOBAL', 'admin_email');
                            $m->From($adminEmail);
                            $m->To(array($params['data']['email'], $adminEmail));
                            $m->Content_type('text/html');
                            $m->Subject($this->_TMS->parseSection('registrationMailSubject'));
                            $m->Body($this->_TMS->parseSection('registrationMailText'), xConfig::get('GLOBAL', 'siteEncoding'));
                            $m->Priority(2);
                            $m->Send();

                            $this->_tree->writeNodeParam($uid, 'verificationCode', $vcode);
                            return array('result' => array('emailRegistrationPassed' => array('email' => $params['data']['email'])));
                        }

                        return array('result' => array('registrationPassed' => true));
                    } else {
                        $this->pushError('registration-internal-error');
                    }

                } else {
                    $this->pushError('registration-internal-error');
                }
            } else {

                unset($params['data']['password'], $params['data']['passwordAgain']);

                if ($checkedData['isLogin']) {
                    $this->pushError('non-uniq-login');
                    unset($params['data']['login']);
                }

                if ($checkedData['isEmail']) {
                    $this->pushError('non-uniq-email');
                    unset($params['data']['email']);
                }
            }
        }

        if ($this->isErrors()) {
            unset($params['data']['Password'], $params['data']['passwordAgain'], $params['data']['code']);
            $return['data'] = $params['data'];
            $return['errors'] = $this->errors;
            return $return;
        }
    }


    public function submitUser($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $params['data'] = $_POST;
        $result = $this->_submitUser($params);

        if (!$result['errors']) {
            $section = key($result['result']);
            $this->_TMS->addMassReplace($section, $result['result'][$section]);
            return $this->_TMS->parseSection($section);
        } else {
            $params['errors'] = $result['errors'];
            return $this->registration($params);
        }
    }


    public function verifyUser($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $code = $_GET['code'];

        if ((!empty($code)) && ($isUser = $this->_tree->selectStruct('*')->where(array('verificationCode', '=', $code))->run())) {
            $user = $isUser[0];
            $anc = $this->_tree->readNodeParam(1, 'defaultRegisteredGroup');
            $this->_tree->changeAncestor($user['id'], $anc);
            $this->_tree->writeNodeParam($user['id'], 'active', 1);

            return $this->_TMS->parseSection('accountConfirmed');
        } else {
            return $this->_TMS->parseSection('accountNotExists');
        }
    }


    public function login($params)
    {
        if ($_SESSION['siteuser']['authorized']) {

            if ($params['stayOnSamePage']) {
                XRegistry::get('TPA')->move301Permanent($_SERVER['HTTP_REFERER']);
            }

            if ($params['linkId']) {
                XRegistry::get('TPA')->move301Permanent($this->getLink($params['linkId']));
            }

            XRegistry::get('TPA')->move301Permanent($this->getLink() . '/~userPanel');

        } else {

            $this->loadModuleTemplate($params['params']['Template']);

            if (($_POST['login']) && ($_POST['password']) && $this->checkAndLoadUser($_POST['login'], $_POST['password'])) {

                XRegistry::get('EVM')->fire($this->_moduleName . '.userLogin', array('userData' => $_SESSION['siteuser']));
                $link = str_replace(HOST, '', $_SERVER['HTTP_REFERER']);

                if ($url = $_SESSION['siteuser']['askForUrl']) {
                    XRegistry::get('TPA')->move301Permanent(CHOST . '/' . $url);

                } elseif (($params['stayOnSamePage']) && ($TPA->request_action != 'auth')
                    && ($TPA->request_action != 'logout') && (strstr('~logout', $link) === false)
                ) {
                    XRegistry::get('TPA')->move301Permanent(CHOST . '/' . $url);
                } elseif ($params['linkId']) {
                    $link = $this->getLink($params['linkId']);
                } else {
                    return $this->userPanel($params);
                }
            } else {
                if (!empty($_POST)) {
                    $params['auth_failed'] = true;
                }

                return $this->needauth($params, true);
            }
        }
    }

    public function needAuth($params, $preventReauth = false)
    {
        $this->loadModuleTemplate($params['params']['Template']);

        if ($TPA->page_redirect_params[$this->_module_name]['reason'] == 'no_access_granted') {
            return $this->_TMS->parseSection('authNoAccessGranted');
        }

        if (!($preventReauth) && ($_POST['login']) && ($_POST['password'])) {
            return $this->auth($params);
        }

        $link = $this->getLink();

        $this->_TMS->addMassReplace('authPanel', array
        (
            'authFailed' => $params['auth_failed'],
            'authAction' => $link . '/~login',
            'registrationLink' => $link . '/~registration',
            'forgotPasswordLink' => $link . '/~forgotPassword'
        ));

        return $this->_TMS->parseSection('authPanel');
    }


    public function _proccessUserPanelMenu($params)
    {
        $link = $this->getLink();

        if ($userMenu = json_decode($params['params']['frontUserMenu'], true)) {
            foreach ($userMenu as &$menuItem) {
                if (!isset($menuItem['link'])) {
                    $menuItem['link'] = $link . '/~userPanel/?userAction=' . $menuItem['action'];
                }
            }
        }

        $links = array(
            'logoutLink' => $link . '/~logout',
            'editUserLink' => $link . '/~editUser');

        $this->_TMS->addReplace('userPanelMenu', 'user', $_SESSION['siteuser']['userdata']);
        $this->_TMS->addMassReplace('userPanelMenu', array('userMenu' => $userMenu));
        $this->_TMS->addMassReplace('userPanelMenu', $links);
        $this->_TMS->parseSection('userPanelMenu', true);
    }


    public function editUser($params, $errors = null, $userdata = null)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $link = $this->getLink();
        $this->_proccessUserPanelMenu($params);
        $this->_TMS->addMassReplace('editUser', array('errors' => $errors, 'saveEditUserLink' => $link . '/~saveProfile'));

        if (!$userdata) {
            if ($userdata = $this->_tree->getNodeInfo($_SESSION['siteuser']['id'])) {
                $additional = $this->_tree->selectParams('*')->childs($_SESSION['siteuser']['id'],1)->run();
                    if(!empty($additional[0])) {
                        $userdata['params']['additionalFields'] = $additional[0]['params'];
                    }
            }
        }

        $user = (!empty($userdata['params'])) ? $userdata['params'] : $userdata;

        if(!empty($userdata) && !empty($user)) {
            $this->_TMS->addReplace('editUser', 'userdata', $userdata);
            $this->_TMS->addReplace('editUser', 'user', $user);
            return $this->_TMS->parseSection('editUser');
        }
    }


    public function _userPanel($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);

        if ($_SESSION['siteuser']['authorized']) {
            $link = $this->getLink();
            $this->_proccessUserPanelMenu($params);
            $links = array('logoutLink' => $link . '/~logout', 'editUserLink' => $link . '/~editUser');

            if (isset($params['request']['requestData']['userAction']) && $params['params']['frontUserMenu']) {
                $userAction = $params['request']['requestData']['userAction'];
                $frontUserMenu = json_decode($params['params']['frontUserMenu'], true);
                $callArray = XARRAY::arrToKeyArr($frontUserMenu, 'action', 'call');

                if (isset($callArray[$userAction])) {
                    $callExpl = explode(':', $callArray[$userAction]);
                    XNameSpaceHolder::call($callExpl[0], $callExpl[1], $params, $this);
                }
            }

            $this->_TMS->addReplace('userPanel', 'user', $_SESSION['siteuser']['userdata']);
            $this->_TMS->addMassReplace('userPanel', $links);

            return $this->_TMS->parseSection('userPanel');
        } else {
            return $this->showLogin($params);
        }
    }


    public function userPanel($params)
    {
        return $this->_userPanel($params);
    }


    public function forgotPassword($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);

        if ($_POST['Email']) {
            $userEmail = trim($_POST['Email']);
            $user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('Email', '=', $userEmail))->run();

            if (!empty($user[0]) && is_array($user[0])) {
                $user = $user[0];
                $uid = $user['id'];
                $newPassword = substr(Common::generateHash(rand(), 12), 0, 8);
                $newPasswordHash = md5(strrev($newPassword));

                $pages = xCore::loadCommonClass('pages');
                $plink = XRegistry::get('TPA')->pageLinkHost;

                $this->_TMS->addMassReplace('forgotPasswordMailText', array(
                    'HOST' => HOST,
                    'newPassword' => $newPassword,
                    'authLink' => $plink . '/~auth'
                ));
                $mailText = $this->_TMS->parseSection('forgotPasswordMailText', true);

                $this->_TMS->addReplace('forgotPasswordMailSubject', 'HOST', HOST);
                $mailSubject = $this->_TMS->parseSection('forgotPasswordMailSubject', true);

                $m=xCore::incModuleFactory('Mail');
                $m->From(xConfig::get('GLOBAL', 'admin_email'));
                $m->To($userEmail);
                $m->Content_type('text/html');
                $m->Subject($mailSubject);
                $m->Body($mailText, xConfig::get('GLOBAL', 'siteEncoding'));
                $m->Priority(2);


                if ($this->_tree->writeNodeParam($uid, 'password', $newPasswordHash) && $m->Send()) {
                    $this->_TMS->addReplace('forgotPasswordELilSend', 'auth_link', $plink . '/~auth');
                    return $this->_TMS->parseSection('forgotPasswordEmailSend');
                } else {
                    $error = $this->_TMS->parseSection('forgotPasswordEmailNotSend', true);
                    $this->_TMS->addReplace('forgotPasswordEnterEmail', 'error', $error);
                    return $this->_TMS->parseSection('forgotPasswordEnterEmail');
                }
            } else {
                $error = $this->_TMS->parseSection('forgotPasswordEmailNotUser', true);
                $this->_TMS->addReplace('forgotPasswordEnterEmail', 'error', $error);
                $this->_TMS->addReplace('forgotPasswordEnterEmail', 'Email', $userEmail);
                return $this->_TMS->parseSection('forgotPasswordEnterEmail');
            }
        } else {
            return $this->_TMS->parseSection('forgotPasswordEnterEmail');
        }
    }

    public function logout($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);
        $userData=$_SESSION['siteuser'];
        unset($_SESSION['siteuser']['userdata']);
        unset($_SESSION['siteuser']['id']);
        $_SESSION['siteuser']['authorized'] = 0;
        unset($_SESSION['siteuser']['userGroup']);
        unset($_SESSION['siteuser']['userGroupName']);
        XRegistry::get('EVM')->fire($this->_moduleName . '.userLogout', array('userData' =>$userData));
        XRegistry::get('TPA')->move301Permanent($this->getLink());
    }

    public function passwordHash($password)
    {
        return md5(strrev($password));
    }

    public function checkAndLoadUser($login, $password)
    {
        if (!$user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('@basic', '=', $login))->singleResult()->run()) {
            $user = $this->_tree->selectStruct('*')->selectParams('*')->where(array('email', '=', $login))->singleResult()->run();
        }

        if (!$user) {
            return false;
        }

        $pass = $this->passwordHash($password);

        if (($user['params']['password'] == $pass) && ($user['params']['active'])) {
            $_SESSION['siteuser']['id'] = $user['id'];
            $_SESSION['siteuser']['userGroup'] = $user['ancestor'];
            $_SESSION['siteuser']['userGroupName'] = $this->_tree->readNodeParam($user['ancestor'], 'Name');
            $_SESSION['siteuser']['authorized'] = true;
            $_SESSION['siteuser']['userdata'] = $user['params'];
            $_SESSION['siteuser']['userdata']['login'] = $user['basic'];

      			if(!empty($user['params']['defaultPrice'])){
      				$_SESSION['userPriceCategory']=$_SESSION['siteuser']['userdata']['defaultPrice']=$user['params']['defaultPrice'];
      			}

            $additional = $this->_tree->selectParams('*')->childs($_SESSION['siteuser']['id'],1)->run();

                if(!empty($additional[0])) {
                    $_SESSION['siteuser']['userdata']['additionalFields'] = $additional[0]['params'];
                }

                if($favorites = XPDO::selectIN('*', 'favorite', 'user_id = "' . $_SESSION['siteuser']['id'].'"')) {
                    $_SESSION['siteuser']['favorites'] = array();

                    foreach($favorites as $k => $v) {
                        $_SESSION['siteuser']['favorites'][$v['id']] = $v['obj_id'];
                    }
                }

            /*if ($p=$this->_tree->GetChildsParam($node['id'], '%', true, $sp))
                {
                    $p=current($p);
                }

            $_SESSION['siteuser']['extuserdata']=$p['params'];*/

            return true;
        } else {
            return false;
        }
    }

    public function userPanelFavorites($params, $source)
    {
        if(!empty($_SESSION['siteuser']['id'])) {
            if($favorites = XPDO::selectIN('*', 'favorite', 'user_id = "' . $_SESSION['siteuser']['id'] . '"')) {
                $objids = array();

                foreach($favorites as $k => $val) {
                    $objids[] = $val['obj_id'];
                }

                $catalog = xCore::moduleFactory('catalog.front');
                $favorites = $catalog->getObjectsByFilterInner(array('f'=>array('equal'=>array('@id'=>$objids))));

                $source->_TMS->addMassReplace('userPanelFavorites', array('favorites' => $favorites));
                $source->_TMS->parseSection('userPanelFavorites', true);
            } else {
                $source->_TMS->parseSection('userPanelFavorites', true);
            }
        }
    }
}

?>
