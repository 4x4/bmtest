<?php

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class commanderCommand extends Command
{
    protected function configure()
    {
        $this->setName('commander:clearCache')
            ->setDescription('Clears all the cache')
            ->setHelp('This command allows you remove cache');
			
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(['Begin cache clear', '========================', '',]);

        $cachePath=xConfig::get('PATH', 'CACHE');
        $folders = array_diff(scandir($cachePath), array('.', '..'));
        $foldersDoNotClear = array('imagecache', 'treewarm');

        if ($folders) {
            foreach($folders as $path)
            {
                if(!in_array($path,$foldersDoNotClear)) {
                    $output->writeln([$cachePath.$path]);
                    if (strstr(php_uname(),'Windows')) {
                        exec(sprintf("rd /s /q %s", escapeshellarg($cachePath.$path)));



                    } else {
                        exec(sprintf("rm -rf %s", escapeshellarg($path)));
                    }
                }

            }
        }

        $output->writeln(['Remove bak files', '========================', '',]);

        if (strstr(php_uname(),'Windows')) {
            exec(escapeshellarg('del /s /f /q '.$_SERVER['DOCUMENT_ROOT'].'\*.bak'));
        }

        X4\Classes\XRegistry::get('EVM')->fire('AdminPanel:afterCacheClear',array('instance'=>$this));

        $output->writeln(['','========================', 'Cleared']);
    }
}



