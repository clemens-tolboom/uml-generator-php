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
            exit(1);
        }
        $config = $this->getConfig();
        chdir($projectRoot);

        $outputDirectory = $config['output-dir'];

        // Run generate:json
        $command = $this->getApplication()->find('generate:json');
        $arguments = array(
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
        // TODO: don't do next line as it breaks $inputArguments ... is it consuming it's config?
        //$this->writeln("  arguments: " . $inputArguments);
        $result = $command->run($inputArguments, $output);
        $this->writeln("Run result: " . $result, 3);


        // Run generate:dot
        $command = $this->getApplication()->find('generate:dot');
        $arguments = array(
          'command' => 'generate:dot',
          'directory' => $outputDirectory,
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
        // TODO: (see above for similar) don't do next line as it breaks $inputArguments ... is it consuming it's config?
        //$this->writeln("  arguments: " . $inputArguments);
        $command->run($inputArguments, $output);

        $this->writeln("<comment>Writing web files to '$outputDirectory'</comment>");
        $this->copyWeb($outputDirectory);
        $this->writeln($this->runWebserver($outputDirectory));

    }

    protected function runWebserver($outputDirectory) {
        return "<comment>Refresh your browser or run:</comment> php -S 0.0.0.0:1337 -t '$outputDirectory'";
    }

    protected function copyWeb($outputDirectory) {
        $finder = new Finder();
        $finder->files()->in(__DIR__ . '/../../web/');
        foreach ($finder as $file) {
            $sourceFile = $file->getRealpath();

            $destinationFile = $outputDirectory . '/' . $file->getRelativePathname();
            $path = dirname($destinationFile);
            if (!is_dir($path)) {
                mkdir(dirname($destinationFile), 0777, TRUE);
            }

            $this->getOutput()->writeln("Copied: $sourceFile to $destinationFile");
            file_put_contents($destinationFile, file_get_contents($sourceFile));

        }
    }

}
