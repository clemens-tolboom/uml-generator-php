<?php
/**
 * Created by PhpStorm.
 * User: Martijn Braam
 * Date: 3/19/14
 * Time: 12:18 PM
 */

namespace UmlGeneratorPhp;


class JsonGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $jsonGenerator;

    public function setUp()
    {
        $this->jsonGenerator = new JsonGenerator(file_get_contents(__DIR__ . '/data/class01.php'));
    }

    public function testGetJson(){
        $json = $this->jsongenerator->getJson();
        $output = json_decode($json, true);
        $expected = json_decode('[{"type":"class","name":"ExampleClass","children":[{"type":"method","name":"__construct","scope":"instance","parameters":[{"name":"exampleargument","type":null}],"visibility":"public"},{"type":"method","name":"publicFunction","scope":"instance","parameters":[],"visibility":"public"},{"type":"method","name":"publicByDefaultFunction","scope":"instance","parameters":[],"visibility":"public"},{"type":"method","name":"staticFunction","scope":"classifier","parameters":[],"visibility":"public"},{"type":"method","name":"publicStaticFunction","scope":"classifier","parameters":[],"visibility":"public"},{"type":"method","name":"privateStaticFunction","scope":"classifier","parameters":[],"visibility":"private"},{"type":"method","name":"protectedStaticFunction","scope":"classifier","parameters":[],"visibility":"protected"},{"type":"method","name":"privateFunction","scope":"instance","parameters":[],"visibility":"private"},{"type":"method","name":"protectedFunction","scope":"instance","parameters":[],"visibility":"protected"}]}]',true);
        $this->assertEquals($expected, $output);
    }

    public function testGenerateHTML()
    {
        $json = $this->jsonGenerator->getJson();
        $array = json_decode($json);

        print_r($array);

        foreach ($array as $index => $object) {
            echo "$object->type\n";
            $result = array();
            $result[] = '<table >';
            $result[]= '<tr><td align="center">' . $object->name . '</td></tr>';

            $methods = array_filter($object->children, function ($item) {
                return $item->type == 'method';
            });
            uasort($methods, function ($a, $b) {
                if ($a->visibility <> $b->visibility) {
                    // public before protected before private
                    return ($a->visibility > $b->visibility) ? -1 : 1;
                }
                if ($a->scope <> $b->scope) {
                    // classifiers before instance
                    return ($a->scope < $b->scope) ? -1 : 1;
                }
                return 0;
            });
            //var_dump($methods);
            $scope = array(
                'classifier' => '<u>%s</u>',
                'instance' => '%s',
            );
            $visibility = array(
                'public' => '+ %s',
                'protected' => '# %s',
                'private' => '- %s',
            );
            foreach ($methods as $method) {
                $s = sprintf($visibility[$method->visibility], $method->name);
                $s = sprintf($scope[$method->scope], $s);
                $result[] = "<tr><td>$s</td></tr>";

            }
            $result[] = '</table>';

            $label = join(PHP_EOL, $result);
            echo "node [label=<\n$label\n>];";
        }
    }

}
 