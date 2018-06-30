<?php

class showLastCommentsAction extends xAction
{

    public function run($params)
    {
        global $TMS;

        if ($comments = $this->get_last_comments($params)) {
            $TMS->addFileSection(Common::get_site_tpl($this->_module_name, $params['Template']));

            foreach ($comments as $comment) {
                $TMS->addMassReplace('_last_comment', $comment);
                $TMS->parseSection('_last_comment', true);
            }

            return $TMS->parseSection('_last_comments');
        }
    }


    public function getLastComments($params)
    {
        global $TDB;

        if ($params['count']) {
            if ($params["cobj"]) {
                $cobjects = $params["cobj"];
            } else {
                if ($params['treads']) {
                    if ($treads = explode(',', $params['treads'])) {
                        $cobjects = array();

                        foreach ($treads as $tread_id) {
                            if ($tchilds = $this->_tree->getChilds($tread_id)) {
                                $cobjects = array_merge($cobjects, XARRAY::askeyval($tchilds, 'id'));
                            }
                        }
                    }
                }
            }

            if ($cobjects) {
                ($params["sl"]) ? $offset = strval($params["sl"]) : $offset = "0";
                ($params["asc"] == "DSC") ? $order = '-' : $order = '';

                $query = 'SELECT * FROM comments WHERE Active=1 AND cid in(' . implode(',',
                        $cobjects) . ') ORDER BY ' . $order
                    . 'date desc LIMIT ' . $offset . ', ' . $params['count'] . ';';

                if ($r = $TDB->get_results($query)) {
                    return $r;
                }
            } else
                return array();
        }
    }

}

?>
