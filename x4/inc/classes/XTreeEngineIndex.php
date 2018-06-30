<?php

class XTreeEngineIndex extends XTreeEngine
{
    private $treeIndexName;

    public function createIndex()
    {
        $this->treeIndexName = strtolower("_tree_" . $this->treeName . "_index");
        $columns = $this->getParametersList();
        $this->createIndexTable($columns);
        $this->pushIndexData();
    }

    public function getParametersList()
    {
        $query = "select parameter from `{$this->treeParamName}` group by parameter";
        $pdoResult = $this->PDO->query($query);
        while ($row = $pdoResult->fetch(\PDO::FETCH_ASSOC)) {
            $columns[] = $this->sanitizeFieldName($row['parameter']);
        }
        return $columns;
    }

    private function sanitizeFieldName($row)
    {
        return str_replace('.', '___', $row);
    }

    public function createIndexTable($columns)
    {
        $fields = array_reduce($columns, function ($str, $v) {
            $str .= "`$v` TEXT, ";
            return $str;
        });
        $this->PDO->query('DROP TABLE IF EXISTS `' . $this->treeIndexName . '`');
        $query = "CREATE TABLE `{$this->treeIndexName}` (`__id__` INT( 14 ) UNSIGNED NOT NULL AUTO_INCREMENT, {$fields} PRIMARY KEY (`__id__`)) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $result = $this->PDO->query($query);
    }

    public function pushIndexData()
    {
        $treeData = $this->selectStruct('*')->selectParams('*')->run();
        if (!empty($treeData)) {
            foreach ($treeData as $data) {
                $dataRow = array();
                $dataRow['__id__'] = 'NULL';
                while (list($key, $val) = each($data['params'])) {
                    if (strlen($val) < 250) {
                        $dataRow[$this->sanitizeFieldName($key)] = $val;
                    }
                }
                XPDO::insertIN($this->treeIndexName, $dataRow);
            }
        }
    }
}