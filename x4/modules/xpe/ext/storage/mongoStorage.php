<?php

class mongoStorage implements personStorageInterface
{
    public $options;
    public $keyTransform = '@';

    public function __construct($options)
    {
        $this->options = $options;
        $this->connection = new MongoClient('mongodb://' . $this->options['host']);
        $this->db = $this->connection->selectDB($this->options['db']);
        $this->collection = new MongoCollection($this->db, $this->options['collection']);

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
        $data = $this->collection->findOne(array(
            'uid' => (string)$uid
        ));

        $data = $this->convertKeys($data);
        return $data;
    }

    public function setItem($uid, $data)
    {
        $data['uid'] = (string)$uid;
        $this->collection->insert($data);
    }


    public function updateItem($uid, $data)
    {
        $filter = array('uid' => (string)$uid);
        $update = array('$set' => $data);
        $collection->update($filter, $update);
    }


}

?>
