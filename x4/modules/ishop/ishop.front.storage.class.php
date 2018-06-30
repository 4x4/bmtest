<?php


class cartSessionStorage implements ArrayAccess, Iterator, Countable
{

    public $_container = array();
    public $_position = 0;

    public function __construct()
    {

        $this->_container =& $_SESSION['siteuser']['cart'];

    }


    public function clear()
    {
        unset($this->_container);
        unset($_SESSION['siteuser']['cart']);
        $this->_container =& $_SESSION['siteuser']['cart'];
    }

    public function count()
    {
        return count($this->_container);
    }

    public function get()
    {
        return $this->_container;
    }


    public function offsetExists($offset)
    {
        return isset($this->_container[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->_container[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_container[] = $value;
        } else {
            $this->_container[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->_container[$offset]);
    }


    public function rewind()
    {
        reset($this->_container);
    }

    public function current()
    {
        return current($this->_container);
    }

    public function key()
    {
        return key($this->_container);
    }

    public function next()
    {
        next($this->_container);
    }

    public function valid()
    {
        return key($this->_container) !== null;
    }

}


class cartDataBaseStorage implements ArrayAccess, Iterator, Countable
{

    public $_table = array();
    public $_container = array();
    public $_position = 0;
    public $_PDO = null;

    public function __construct()
    {
        $this->_table = 'ishop_user_cart_storage';
        $this->_PDO = XRegistry::get('XPDO');
        $this->initiateContainer($_SESSION['userCartID']);
    }

    public function initiateContainer($sessionId = null)
    {
        if (!$sessionId) {
            $sessionId = $_SESSION['userCartID'] = $sessionId = Common::generateHash('userCart');
        }


        $fuser = xCore::loadCommonClass('fusers');

        if ($fuser->isUserAuthorized()) {

            $pdoResult = $this->_PDO->query("select * from `ishop_user_cart_storage` where user_id='{$_SESSION['siteuser']['id']}'");

        } else {

            $pdoResult = $this->_PDO->query("select * from `ishop_user_cart_storage` where session_id='{$sessionId}'");
        }


        if ($row = $pdoResult->fetch(PDO::FETCH_ASSOC)) {

            if ($row['cart_data']) {


                $this->_container = unserialize($row['cart_data']);

            } else {
                $this->_container = array();
            }

        } else {

            $userId = $this->getCurrentUserId();

            $query = 'INSERT INTO `ishop_user_cart_storage` (`id`, `session_id`, `cart_data`, `user_id`,`domain`,`updated`) VALUES (NULL, "' . $sessionId . '", "' . serialize(array()) . '",' . $userId . ',"' . HTTP_HOST . '","' . time() . '")';
            $this->_PDO->query($query);
        }

    }

    public function getCurrentUserId()
    {
        $fuser = xCore::loadCommonClass('fusers');

        if (!$fuser->isUserAuthorized()) {
            $userId = "NULL";

        } else {

            $userId = $_SESSION['siteuser']['id'];
        }

        return $userId;
    }

    public function syncContainer()
    {
        $containerSerialized = serialize($this->_container);

        $userId = $this->getCurrentUserId();
        $query = "update `ishop_user_cart_storage`  set cart_data='{$containerSerialized}', user_id='{$userId}' , updated='" . time() . "'  where session_id='{$_SESSION['userCartID']}'";
        $this->_PDO->exec($query);

    }

    public function clear()
    {

        if ($count = $this->_PDO->exec("delete from `ishop_user_cart_storage` where session_id='{$_SESSION['userCartID']}'")) {
            unset($this->_container);
            unset($_SESSION['userCartID']);
        }

    }

    public function count()
    {
        return count($this->_container);
    }

    public function get()
    {
        return $this->_container;
    }


    public function offsetExists($offset)
    {
        return isset($this->_container[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->_container[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {

        if (is_null($offset)) {
            $this->_container[] = $value;
        } else {
            $this->_container[$offset] = $value;
        }

        $this->syncContainer();
    }

    public function offsetUnset($offset)
    {
        unset($this->_container[$offset]);
        $this->syncContainer();
    }


    public function rewind()
    {
        reset($this->_container);
    }

    public function current()
    {
        return current($this->_container);
    }

    public function key()
    {
        return key($this->_container);
    }

    public function next()
    {
        next($this->_container);
    }

    public function valid()
    {
        return key($this->_container) !== null;
    }

}

