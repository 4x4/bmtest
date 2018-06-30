<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use X4\Classes\XRegistry;

class catalogReindexCommand extends Command
{
    protected function configure()
    {
        XRegistry::get('EVM')->on('catalog:onObjectIndex', 'advance', $this);

        $this->setName('catalog:reindex')
            ->setDescription('Makes catalog reindex')
            ->setHelp('This command allows you reindex catalog')
            ->addOption('categoryId',null,InputOption::VALUE_REQUIRED,'category ID',0)
            ->addOption('isFullIndex',null,InputOption::VALUE_OPTIONAL,'make full index',0)
            ->addOption('indexMoveStep',null,InputOption::VALUE_OPTIONAL,'set index step ',500);
    }


    public function advance($params,$data){

        if($params['data']['z']==1){
            $this->progressBar->advance();
        }
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['Begin reindex', '', '',]);
        $output->writeln(['IndexMoveStep value = '. $input->getOption('indexMoveStep'), '']);


        $node=$this->_commonObj->_tree->getNodeInfo($input->getOption('categoryId'));

        if(!$node){
            $output->writeln('<error>undefined categoryId</error>');
            exit;
        }

        if($node)
        {

            if (!empty($node['params']['IndexParams'])) {
                $indexParams = explode(',', $node['params']['IndexParams']);
                $skuExtract = array('doNotExtractSKU' => true);

                $output->writeln(['IndexParams value = '. $node['params']['IndexParams'], '']);
            }

            if (!empty($node['params']['IndexParamsSku'])) {
                $indexParamsSku = explode(',', $node['params']['IndexParamsSku']);
                $skuExtract = array('doNotExtractSKU' => false);
                $output->writeln(['IndexParamsSku value = '. $node['params']['IndexParamsSku'], '']);
            }

            $this->progressBar = new ProgressBar($output);
            $this->progressBar->start();

            $params['start'] = 0;

            $this->result = $this->_commonObj->fastIndexing($params['id'], $indexParams, $params['start'], $input->getOption('indexMoveStep'), $skuExtract, $indexParamsSku, $input->getOption('isFullIndex'));

            $this->progressBar->finish();
        }else{
            $output->writeln(['','Index node does not exists!']);
        }


        $output->writeln(['','', 'reindexed']);
    }
}





class catalogReindexPricesCommand extends Command
{
    protected function configure()
    {

        XRegistry::get('EVM')->on('catalog:onPricesReindex', 'advance', $this);

        $this->setName('catalog:reindexPrices')
            ->setDescription('Makes catalog reindex')
            ->setHelp('This command allows you reindex catalog');
    }




    public function advance($params,$data){

            $this->progressBar->advance();

    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['Begin reindex price', '', '',]);

        $this->progressBar = new ProgressBar($output);
        $this->progressBar->start();
        $this->_commonObj->rebuildIcurrencyFields();
        $this->progressBar->finish();
        $output->writeln(['','', 'Reindexed']);
    }
}



