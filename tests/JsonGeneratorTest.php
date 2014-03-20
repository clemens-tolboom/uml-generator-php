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
    private $OopTreeArray;

    public function setUp()
    {
        $code = file_get_contents(__DIR__ . '/data/class01.php');
        $parser = new \PhpParser\Parser(new \PhpParser\Lexer);
        $traverser = new \PhpParser\NodeTraverser;
        $visitor = new OopFilter;
        $traverser->addVisitor($visitor);
        $stmts = $parser->parse($code);
        $this->OopTreeArray = $traverser->traverse($stmts);
    }


    public function testGenerateHTML()
    {
        $array = json_decode(json_encode($this->OopTreeArray));

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
 