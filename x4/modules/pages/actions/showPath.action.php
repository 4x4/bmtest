<?php

class showPathAction
    extends xAction
{
    
    public function runHeadless($params)
    {
        
         $preBones = $this->bones;
                
               
        if (is_array($bonesFull = $this->bones)) {

            $i = 0;


            if ($this->additionalBones) {
                foreach ($this->additionalBones as $bone) {
                    $bonesFull[] = $bone;
                }
            }

            $bonesLength = count($bonesFull);
             
            $i = 0;
             
            while (list(, $bone) = each($bonesFull)) {
                $i++;
                
                if (!$bone['link']) {
                    $link = $this->_commonObj->linkCreator($bone['basicPath']);

                } else {
                    $link = $bone['link'];
                }


                if (!$bone['params']['DisablePath'] and ($bonesLength != $i))
                {
                    $bonesItems[]=array(
                        'name' => $bone['params']['Name'],
                        'link' => $link
                    );

                }

            }

            $bone = array_pop($bonesFull);
            $bonesItems[]=array(
                'name' => $bone['params']['Name'],
                'link' => $link
            );

        }

             
        return $bonesItems;
    }
    
    public function run($params)
    {
        $this->loadModuleTemplate($params['params']['Template']);

        if (is_array($bonesFull = $this->bones)) {

            $i = 0;
        
            $delimiteSymbol = $this->_TMS->parseSection('_bones_delimiter');


            if ($this->additionalBones) {
                foreach ($this->additionalBones as $bone) {
                    $bonesFull[] = $bone;
                }
            }

            $bonesLength = count($bonesFull);

            while (list(, $bone) = each($bonesFull)) {
                $i++;
                if (!$bone['link']) {
                    $link = $this->_commonObj->linkCreator($bone['basicPath']);

                } else {
                    $link = $bone['link'];
                }


                if (!$bone['params']['DisablePath'] and ($bonesLength != $i)) {
                    $this->_TMS->addMassReplace('_bones_item', array(
                        'name' => $bone['params']['Name'],
                        'link' => $link
                    ));

                    $this->_TMS->parseSection('_bones_item', true);
                }

            }

            $bone = array_pop($bonesFull);
            $this->_TMS->addMassReplace('_bones_item_no_link', array(
                'name' => $bone['params']['Name'],
                'link' => $link
            ));

            $this->_TMS->parseSection('_bones_item_no_link', true);
        }

        $bones = $this->_TMS->parseSection('_bones');
        return $bones;

    }
}

