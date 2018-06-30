<?php
use X4\Classes\XNameSpaceHolder;
use X4\Classes\XRegistry;
use X4\Classes\XPDO;

class fusersCommon
    extends xModuleCommon
    implements xCommonInterface
{
    public $_useTree = true;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->_tree->setLevels(5);
        $this->_tree->setObject('_ROOT', array
        (
            'defaultUnregisteredGroup',
            'defaultRegisteredGroup'
        ));

        $this->_tree->setObject('_FUSERSGROUP', array
        (
            'Name',
            'discountScheme',
            'comments'
        ), array('_ROOT'));

        $this->_tree->setObject('_FUSER', array
        (
            'lastVisit',
            'password',
            'name',
            'surname',
            'patronymic',
            'email',
            'company',
            'address',
            'discountScheme',
            'defaultPrice',
            'accessiblePrices',
            'site',
            'phone',
            'active',
			'birthDate',
            'userType',
            'verificationCode',
            'avatar',
            'comment'
        ), array('_FUSERSGROUP'));

        $this->_tree->setObject('_FUSEREXTDATA', null, array('_FUSER'));
    }


    public function isUserAuthorized()
    {

        return $_SESSION['siteuser']['authorized'];

    }


    public function checkUserLoginAndEmail($login, $email)
    {
        if ($login) {
            $isLogin = $this->_tree->selectStruct('*')->where(array
            (
                'login',
                '=',
                $login
            ))->singleResult()->run();
        }

        if ($email) {
            $isEmail = $this->_tree->selectStruct('*')->where(array
            (
                'email',
                '=',
                $email
            ))->singleResult()->run();
        }

        return array
        (
            'isLogin' => $isLogin,
            'isEmail' => $isEmail
        );
    }


    public function initAdditionalFields($id,$params)
    {
        $this->_tree->delete()->childs($id)->run();
        $this->_tree->initTreeObj($id, '%SAMEASID%', '_FUSEREXTDATA', $params);
    }

 
    public function defineFrontActions()
    {
        $this->defineAction('showAuthPanel');        
        $this->defineAction('showUserPriceCategory');
		$this->defineAction('showUsersFromGroup');
        $this->defineAction('userPanel', array('serverActions' => array
        (
            'needAuth',
            'destroyUser',
            'submitUser',
            'registration',
            'forgotPassword',
            'logout',
            'editUser',
            'login',
            'saveProfile',
            'userPanel',
            'submitUser',
            'verifyUser'
        )));
    }
}


