<?php

namespace UmlGeneratorPhp\Command;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UmlGeneratorPhp;

class JsonCommand extends Command
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
          );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputDirectory = realpath($input->getArgument('input'));
        $outputDirectory = realpath($input->getArgument('output'));

        if(!is_dir($inputDirectory)){
            $output->writeln('<error>Input is not a directory</error>');
            die;
        }
        if(!is_dir($outputDirectory)){
            $output->writeln('<comment>' . $input->getArgument('output') . ' not found</comment>');
            $output->writeln('<comment>Creating output directory</comment>');
            mkdir($input->getArgument('output'), 0777, true);
            $outputDirectory = realpath($input->getArgument('output'));
        }


// Scan only for .php files
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($inputDirectory));
        $files = new RegexIterator($files, '/\.php$/');
        $visitor = new UmlGeneratorPhp\OopFilter;
        foreach($files as $file){
            // Parse file for OOP concepts
            $code = file_get_contents($file);
            $parser = new \PhpParser\Parser(new \PhpParser\Lexer);
            $traverser = new \PhpParser\NodeTraverser;

            $pinfo=pathinfo($file);
            $outputfiledir = str_replace($inputDirectory, $outputDirectory, $pinfo['dirname']);
            $outputfile = $outputfiledir . '/' . $pinfo['filename'] . '.json';

            $meta = array(
              'file' => $file->getPathName(),
              'output' => $outputfile
            );

            try {
                $visitor->setMeta($meta);
                $traverser->addVisitor($visitor);
                $stmts = $parser->parse($code);
                $tree = $traverser->traverse($stmts);

                $output->writeln($file);
            }
            catch (Exception $e) {
                file_put_contents('php://stderr', "ERROR " . $e->getMessage() . PHP_EOL, FILE_APPEND);
                file_put_contents('php://stderr', "  SKIPPING " . $file . PHP_EOL, FILE_APPEND);
                continue;
            }


            $json = json_encode($tree);
            if($json != '[]'){
                if(!is_dir($outputfiledir)){
                    mkdir($outputfiledir,0777,true);
                }
                file_put_contents($outputfile, $json);
            }
            $indexdata = json_encode($visitor->getIndex());
            $indexfile = $outputDirectory . '/uml-generator-php.index';
            file_put_contents($indexfile, $indexdata);
        }

    }

} 