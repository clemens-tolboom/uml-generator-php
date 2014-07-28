<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 27-07-14
 * Time: 11:23
 */

namespace UmlGeneratorPhp\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class BaseCommand extends Command {

    /* @var OutputInterface */
    var $output;

    /* @var String $projectRoot */
    var $projectRoot;

    /**
     * @return String
     */
    public function getProjectRoot() {
        return $this->projectRoot;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput() {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput($output) {
        $this->output = $output;
    }

    /**
     * @param String $projectRoot
     */
    private function setProjectRoot($projectRoot) {
        $this->projectRoot = $projectRoot;
    }

    public function writeln($text) {
        $this->getOutput()->writeln($text);
    }

    protected function findConfig() {
        // Find the project root containing .uml-generator-php.yml
        $projectRoot = getcwd();
        $projectRoot = realpath($projectRoot);
        $this->writeln("Scanning for .uml-generator-php.yml");
        while ($projectRoot !== '/') {
            $this->writeln("  scanning dir: " . $projectRoot);
            if (file_exists($projectRoot . '/.uml-generator-php.yml')) {
                break;
            }
            else {
                $projectRoot = realpath($projectRoot . '/..');
            }
        }
        if ($projectRoot == '/') {
            $this->writeln('<error>.uml-generator-php.yml not found.</error> Please run : cp ' . realpath(__DIR__ . '/../../uml-generator-php.yml.dist') . ' ' . getcwd() . '/.uml-generator-php.yml');
            $this->setProjectRoot(NULL);
            return;
        }
        $this->setProjectRoot($projectRoot);

    }

    protected function overrideConfig($config, $key, $value)
    {
        // TODO allow for overriding path
    }

    private function validateConfig($config)
    {
        // TODO: validate the config
        // TODO: fill in the defaults
        // TODO: provide child to override the config

    }

    public function getConfig() {
        $projectRoot = $this->getProjectRoot();
        if (is_null($projectRoot)) {
            throw new Exception("No config file found. This needs a '.uml-generator-php.yml' file somewhere");
        }
        $config = Yaml::parse(file_get_contents($projectRoot . '/.uml-generator-php.yml'));
        return $config;
    }

}
