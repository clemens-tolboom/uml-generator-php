<?php
/**
 * Created by PhpStorm.
 * User: clemens
 * Date: 21-03-14
 * Time: 10:49
 */

namespace UmlGeneratorPhp;

class OopToDot
{
    /**
     * Generate an UML CLass Digram dot file based on the given objects
     *
     * TODO: alignments
     * TODO: links to Name, vars and methods
     * TODO: add stereotype: <<class>>, <<interface>>, <<trait>>
     *
     * @param $array
     * @return string
     */
    function getClassDiagram($array)
    {
        $result = array();
        $result[] = 'graph "Class Diagram" {';
        $result[] = "  node [shape=plaintext]";
        foreach ($array as $index => $object) {
            $result[] = "  node_$index [";
            $result[] = "    label=<";
            $result[] = '<table >';
            $result[] = '<tr><td align="center" href="http://api.drupal.org/">' . $object->name . '</td></tr>';

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
            $result[] = "  >";

            $result[] = "  ];";
        }
        $result[] = "}";

        return join(PHP_EOL, $result);

    }

}
