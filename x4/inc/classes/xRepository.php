<?php
namespace X4\Classes;
/**
 *  Class created to utilize any x4 object to node change history table
 */
 
class XRepository
{

    public function __construct()
    {

    }

    /**
     * put your comment there...
     *
     * @param mixed $id
     * @param repoProvider $provider
     * @param mixed $params array('changeType'=>update|delete)
     */
    public function objectToRepo($id, repoProvider $provider, $params = false)
    {
        $source = get_class($provider);
        XPDO::insertIN('object_repository_history', array('id' => NULL, 'objectId' => $id, 'source' => $source, 'changeType' => $params['changeType']));
    }


}

?>
