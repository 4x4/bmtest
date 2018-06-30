<?php

class mongoDriverStorage implements personStorageInterface
{
    public $options;
    public $keyTransform = '@';

    public function __construct($options)
    {
        $this->options = $options;
        try {

            $this->connection = new MongoDB\Driver\Manager('mongodb://' . $this->options['host']);

        } catch (MongoDB\Driver\Exception\Exception $e) {
            echo "Exception:", $e->getMessage(), "\n";
        }

        $this->accessPoint = $this->options['db'] . '.' . $this->options['collection'];
    }

    public function convertKeys($data)
    {
        $output = array();
        foreach ($data as $key => $val) {
            $newKey = str_replace($this->keyTransform, '.', $key);
            $output[$newKey] = $val;
        }

        return $output;

    }

    public function getItem($uid)
    {
        $filter = ['uid' => "$uid"];

        try {

            $query = new MongoDB\Driver\Query($filter);
            $rows = $this->connection->executeQuery($this->accessPoint, $query);
            $rows = $rows->toArray();
            if (!empty($rows[0])) {
                $data = $rows[0];
                $data = $this->convertKeys($data);
                return $data;
            }

        } catch (MongoDB\Driver\Exception\Exception $e) {
            //echo "Exception:", $e->getMessage(), "\n";
        }
        return false;
    }

    public function setItem($uid, $data)
    {
        $data['uid'] = (string)$uid;
        //  $this->collection->insert($data);
    }


    public function updateItem($uid, $data)
    {
        $filter = array('uid' => (string)$uid);
        $update = array('$set' => $data);
        //$collection->update($filter, $update);
    }


}

?>
