<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use X4\Classes\XRegistry;



class iceCatLoaderDeleteCommand extends Command
{
    
    public $categories=array();
    public $installCategory=60902;
    public $categoryPropertySet=3911;

    protected function configure()
    {

        $this->setName('catload:clean')
            ->setDescription('cleans catalog')
            ->setHelp('This command allows you to clean categories');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
           
        $this->catalog=xCore::moduleFactory('catalog.front');
        $this->catalog->_tree->delete()->childs($this->installCategory)->run();
		$output->writeln("cleared");

    }

}

class iceCatLoaderDeleteSkuCommand extends Command
{

    public $categories=array();
    public $skuFolder=3593580;

    protected function configure()
    {

        $this->setName('catload:cleansku')
            ->setDescription('cleans sku catalog')
            ->setHelp('This command allows you to clean categories');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->catalog=xCore::moduleFactory('catalog.front');
        $this->catalog->_commonObj->_sku->delete()->childs($this->skuFolder,1)->run();

    }

}


class iceCatLoaderCommand extends Command
{
    
    public $categories=array();
    public $installCategory=60902;
    public $categoryPropertySet=3911;

    protected function configure()
    {

        $this->setName('catload:categories')
            ->setDescription('loads categories')
            ->setHelp('This command allows you to load categories');
    }

    public function recursiveCatCreate($point,$ancestor)
    {

        if(!empty($this->categories['nest'][$point])) {
            foreach ($this->categories['nest'][$point] as $category) {
                $id=$category['@attributes']['ID'];
                $basic=XCODE::translit($category['Name']['@attributes']['Value']);

                $basic=str_replace(array('&','/','\\'),'-',$basic);
                $basic = preg_replace('/\-+/', '-', $basic);

                $paramSet=array(
                    'Name'=>$category['Name']['@attributes']['Value'],
                    'category.innerID'=>$id,
                    'category.image'=>$category['@attributes']['LowPic'],
                    'PropertySetGroup'=>$this->categoryPropertySet
                );

                if ($realObjId = $this->catalog->_tree->initTreeObj($ancestor, $basic, '_CATGROUP', $paramSet))
                {
                    if($this->categories['nest'][$id]){
                        $this->recursiveCatCreate($id,$realObjId);
                    }
                }
            }
        }

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->path=PATH_.'db/';   
        $this->categories=file_get_contents($this->path.'categories/RebuildSelected.json');
        $this->categories=json_decode($this->categories,true);
        $this->catalog=xCore::moduleFactory('catalog.front');
        $this->recursiveCatCreate(2833,$this->installCategory);


    }

}




class iceObjLoaderCommand extends Command
{
    
    public $categoryMapping=array();
    public $installCategory=60902;
    public $objectPropertySet=3916;
    public $uncat=1083599;
    public $categoryPropertySet=3911;

    protected function configure()
    {

        $this->setName('catload:objects')
            ->setDescription('loads objects')
            ->setHelp('This command allows you to load objects');
    }

    private function createCategory($category)
    {
        $basic=XCODE::translit($category['Name']['@attributes']['Value']);

        $basic=str_replace(array('&','/','\\'),'-',$basic);
        $basic = preg_replace('/\-+/', '-', $basic);


        $paramSet=array(
            'Name'=>$category['Name']['@attributes']['Value'],
            'category.innerID'=>$category['@attributes']['ID'],
            'PropertySetGroup'=>$this->categoryPropertySet
        );

        $realId=$this->catalog->_tree->initTreeObj($this->uncat, $basic, '_CATGROUP', $paramSet);
        $this->categoryMapping[$category['@attributes']['ID']]=$realId;
    }

    private function productCreate($object,$ean,$mfp,$vendor)
    {


        $paramSet=array(
            'Name'=>$object['Product']['@attributes']['Title'],
            'tovarbase.code'=>$object['Product']['@attributes']['ID'],
            'tovarbase.image'=>$object['Product']['@attributes']['HighPic'],
            'tovarbase.vendor'=>$vendor,
            'tovarbase.mfp'=>$mfp,
            'tovarbase.brand'=>$object['Product']['Supplier']['@attributes']['Name'],
            'tovarbase.PID'=>$object['Product']['@attributes']['Prod_id'],
            'tovarbase.thumb'=> $object['Product']['@attributes']['ThumbPic'],
            'tovarbase.ean'=>$ean,
            'PropertySetGroup'=>$this->objectPropertySet

        );

        $paramSet['tovarbase.vnum']=md5($vendor.$mfp);

         $basic=XCODE::translit($object['Product']['@attributes']['Title']);
         
         $basic=str_replace('/','',$basic);

         $ancestor=$this->categoryMapping[$object['Product']['Category']['@attributes']['ID']];

        if(empty($ancestor)){


            if(!$this->categoryMapping[$object['Product']['Category']['@attributes']['ID']]){
                $this->createCategory($object['Product']['Category']);
            }

            $ancestor=$this->categoryMapping[$object['Product']['Category']['@attributes']['ID']];
        }

           $this->catalog->_tree->initTreeObj($ancestor, $basic, '_CATOBJ', $paramSet);

        //file_put_contents('bad.log', print_r($object['Product']['Category'],true), FILE_APPEND | LOCK_EX);

        unset($paramSet);

    }

    private function removeCategories()
    {

        foreach($this->categoryMapping as $category)
        {
            $childs=$this->catalog->_tree->selectStruct('*')->childs($category)->where(array('@obj_type','=','_CATOBJ'))->run();
            if(!$childs)$this->catalog->_tree->delete()->where(array('@id','=',$category))->run();
        }
    }

    private function fetchByEan($ean,$output){

        $eanPart=substr($ean,0,4);
        $path=$this->path.'/data/EAN/'.$eanPart.'/'.$ean.'.json';

        if(file_exists($path)) {
            $file = file_get_contents($path);
            $d=json_decode($file,true);
            unset($file);
            return $d;
        }

        return;
    }


    private function fetchByMfp($mfp,$vendor,$output){

        $path=$this->path.'data/MFP/'.$vendor.'/'.$mfp.'.json';

        if(file_exists($path)) {
            $handle=fopen($path, "r");
            $file=fread($handle, filesize($path));
            fclose($handle);
            $d=json_decode($file,true);
            unset($file);
            return $d;
        }

        return;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->path=PATH_.'db/';  

        $this->catalog=xCore::moduleFactory('catalog.front');

        $this->categoryMapping=$this->catalog->_tree->selectAll()->where(array('@obj_type','=','_CATGROUP'))->format('valparams','id','category.innerID')->run();

        if(!empty($this->categoryMapping)) {

            $this->categoryMapping = array_flip($this->categoryMapping);

            $progressBar = new ProgressBar($output);
            $progressBar->start();

            $result = xRegistry::get('XPDO')->query('select * from productBase');

            $z=0;

            while ($data = $result->fetch(\PDO::FETCH_ASSOC))
            {
                $z++;
                if(($z % 500)==0){
                    $progressBar->advance();
                }

                if($data['ean']) {

                    $object = $this->fetchByEan($data['ean'],$output);

                }else{

                    $object = $this->fetchByMfp($data['num'],$data['vendor'],$output);
                }

                if(!empty($object))
                {
                    $this->productCreate($object, $data['ean'], $data['num'], $data['vendor']);
                }

                unset($object);
                unset($data);
            }

            $this->removeCategories();
        }

    }

}