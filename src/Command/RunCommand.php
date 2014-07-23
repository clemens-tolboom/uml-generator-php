<?php

namespace UmlGeneratorPhp\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use UmlGeneratorPhp;

class RunCommand extends Command
{
    protected function configure()
    {
        $this
          ->setName('run')
          ->setDescription('Read .uml-generator-php.yml in project root and runs all tools');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Find the project root containing .uml-generator-php.yml
        $projectRoot = getcwd();
        $projectRoot = realpath($projectRoot) . '/';
        while ($projectRoot !== '/') {
            if (file_exists($projectRoot . '.uml-generator-php.yml')) {
                break;
            } else {
                $projectRoot = realpath($projectRoot . '..') . '/';
            }
        }
        if ($projectRoot == '/') {
            $output->writeln('<error>.uml-generator-php.yml not found.</error>');
            exit(1);
        }
        $config = Yaml::parse(file_get_contents($projectRoot . '.uml-generator-php.yml'));
        chdir($projectRoot);

        // Run generate:json
        $command = $this->getApplication()->find('generate:json');
        $arguments = array(
          'input' => $projectRoot,
          'command' => 'generate:json',
          'output' => $config['outputdir']
        );
        if (isset($config['skip'])) {
            $arguments['--skip'] = $config['skip'];
        }
        if (isset($config['only'])) {
            $arguments['--only'] = $config['only'];
        }
        $inputArguments = new ArrayInput($arguments);
        $command->run($inputArguments, $output);


        // Run generate:dot
        $command = $this->getApplication()->find('generate:dot');
        $arguments = array(
          'directory' => $config['outputdir'],
          'command' => 'generate:dot',
        );
        if (isset($config['parents'])) {
            if ($config['parents']['enabled']) {
                $arguments['--parents'] = true;
            }
            if (isset($config['parents']['depth'])) {
                $arguments['--parent-depth'] = $config['parents']['depth'];
            }
        }
        if (isset($config['legacy'])) {
            $arguments['--legacy'] = true;
        }
        $inputArguments = new ArrayInput($arguments);
        $command->run($inputArguments, $output);
    }

}