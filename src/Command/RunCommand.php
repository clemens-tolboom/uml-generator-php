<?php

namespace UmlGeneratorPhp\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UmlGeneratorPhp;

class RunCommand extends BaseCommand
{
    protected function configure()
    {
        $this
          ->setName('run')
          ->setDescription('Read .uml-generator-php.yml in project root and runs all tools');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->setOutput($output);

        $this->findConfig();
        $projectRoot = $this->getProjectRoot();
        if (is_null($projectRoot)) {
            $this->writeln('<error>.uml-generator-php.yml not found.</error> Please run : cp ' . realpath(__DIR__ . '/../../uml-generator-php.yml.dist') . ' ' . getcwd() . '/.uml-generator-php.yml');
            exit(1);
        }
        $config = $this->getConfig();
        chdir($projectRoot);

        $outputDirectory = $config['output-dir'];

        // Run generate:json
        $command = $this->getApplication()->find('generate:json');
        $arguments = array(
          'input' => $projectRoot,
          'command' => 'generate:json',
          'input' => $config['input-dir'],
          'output' => $outputDirectory,
        );
        if (isset($config['skip'])) {
            $arguments['--skip'] = $config['skip'];
        }
        if (isset($config['only'])) {
            $arguments['--only'] = $config['only'];
        }
        $inputArguments = new ArrayInput($arguments);
        $this->writeln("  arguments: " . $inputArguments);
        $result = $command->run($inputArguments, $output);


        // Run generate:dot
        $command = $this->getApplication()->find('generate:dot');
        $arguments = array(
          'command' => 'generate:dot',
          'directory' => $config['output-dir'],
        );
        if (isset($config['parents'])) {
            if ($config['parents']['enabled']) {
                $arguments['--with-parents'] = FALSE;
            }
            if (isset($config['parents']['depth'])) {
                $arguments['--parents-depth'] = $config['parents']['depth'];
            }
        }
        if (isset($config['legacy'])) {
            $arguments['--legacy'] = TRUE;
        }
        $inputArguments = new ArrayInput($arguments);
        $command->run($inputArguments, $output);
    }

}
