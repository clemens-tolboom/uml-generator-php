<?php

namespace UmlGeneratorPhp\Command;

use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UmlGeneratorPhp;

class JsonCommand extends BaseCommand
{
    protected function configure()
    {
        $this
          ->setName('generate:json')
          ->setDescription('Generate json files from the php source')
          ->addArgument(
            'input',
            InputArgument::REQUIRED,
            'The directory containing your PHP project.'
          )
          ->addArgument(
            'output',
            InputArgument::REQUIRED,
            'The directory to write the JSON files to.'
          )
          ->addOption(
            'skip',
            's',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'A directory or file to skip (relative to input directory)'
          )
          ->addOption(
            'only',
            'o',
            InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
            'Run only on files that match this path (relative to input directory)'
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setOutput($output);

        $inputDirectory = realpath($input->getArgument('input'));
        if (!is_dir($inputDirectory)) {
            $this->writeln('<error>Input is not a directory</error>');
            die;
        }

        $outputDirectory = $input->getArgument('output');
        if (!is_dir($outputDirectory) && !is_link($outputDirectory)) {
            $this->writeln('<comment>' . $outputDirectory . ' not found</comment>');
            $this->writeln('<comment>Creating output directory</comment>');
            mkdir($input->getArgument('output'), 0777, true);
            $outputDirectory = realpath($outputDirectory);
        }


        // Scan only for .php files
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($inputDirectory);
        $skipped = $input->getOption('skip');
        if (is_array($skipped) && !empty($skipped)) {
            $this->writeln("  skipping: " . join(", ", $skipped));
            foreach ($skipped as $skip) {
                $finder = $finder->notPath($skip);
            }
        }
        $only = $input->getOption('only');
        if (is_array($only) && !empty($only)) {
            $this->writeln("  only: " . join(", ", $only));
            foreach ($only as $filter) {
                $finder = $finder->Path($filter);
            }
        }
        $files = $finder->name('*.php');

        $indexFile = $outputDirectory . '/uml-generator-php.index';
        if (file_exists($indexFile)) {
            $this->writeln('Found index file: ' . $indexFile);
            $lastRunTimestamp = filemtime($indexFile);
        } else {
            $lastRunTimestamp = 0;
        }

        $files->date('since @' . $lastRunTimestamp);

        $visitor = new UmlGeneratorPhp\OopFilter;
        foreach ($files as $file) {
            // Parse file for OOP concepts
            $code = file_get_contents($file);
            $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP5);
            $traverser = new \PhpParser\NodeTraverser;

            $pinfo = pathinfo($file);
            $outputFileDir = str_replace($inputDirectory, $outputDirectory, $pinfo['dirname']);
            $outputFile = $outputFileDir . '/' . $pinfo['filename'] . '.json';

            $meta = array(
              'file' => $file->getPathName(),
              'output' => realpath($outputFile),
              'base' => realpath($outputDirectory)
            );
            $this->writeln($file->getPathName());
            try {
                $visitor->setMeta($meta);
                $traverser->addVisitor($visitor);
                $stmts = $parser->parse($code);
                $tree = $traverser->traverse($stmts);
            } catch (\Exception $e) {
                file_put_contents('php://stderr', "ERROR " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                file_put_contents('php://stderr', "  SKIPPING " . $file . PHP_EOL, FILE_APPEND);
                continue;
            }


            $json = json_encode($tree);
            if ($json != '[]') {
                if (!is_dir($outputFileDir)) {
                    mkdir($outputFileDir, 0777, true);
                }
                file_put_contents($outputFile, $json);
            }
            $indexData = json_encode($visitor->getIndex());
            file_put_contents($indexFile, $indexData);
        }
    }

}
