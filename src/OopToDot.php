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
        foreach ($array as $index => $values) {
            $meta = $values['meta'];

            $fileUrl = isset($meta['fileUrl']) ? ' href="' . $meta['fileUrl'] . '"' : '';
            $propertyUrl = isset($meta['propertyUrl']) ? ' href="' . $meta['propertyUrl'] . '"' : '';

            $result[] = "  node_$index [";
            $result[] = "    label=<";
            $result[] = '<table border="1" cellpadding="2" cellspacing="0" cellborder="0">';
            $result[] = '<tr><td align="center"' .  $fileUrl . '>' . $values['name'] . '</td></tr><hr />';

            $scope = array(
                'classifier' => '<u>%s</u>',
                'instance' => '%s',
            );
            $visibility = array(
                'public' => '+ %s',
                'protected' => '# %s',
                'private' => '- %s',
            );

            $properties = array_filter($values['children'], function ($item) {
                return $item['type'] == 'attribute';
            });
            uasort($properties, function ($a, $b) {
                if ($a['visibility'] <> $b['visibility']) {
                    // public before protected before private
                    return ($a['visibility'] > $b['visibility']) ? -1 : 1;
                }
                if ($a['scope'] <> $b['scope']) {
                    // classifiers before instance
                    return ($a['scope'] < $b['scope']) ? -1 : 1;
                }
                return 0;
            });

            foreach ($properties as $property) {
                $s = sprintf($visibility[$property['visibility']], $property['name']);
                $s = sprintf($scope[$property['scope']], $s);
                $result[] = '<tr><td align="left">' . $s . '</td></tr>';

            }
            if(count($properties)>0){
                $result[] = '<hr />';
            }

            $methods = array_filter($values['children'], function ($item) {
                return $item['type'] == 'method';
            });
            uasort($methods, function ($a, $b) {
                if ($a['visibility'] <> $b['visibility']) {
                    // public before protected before private
                    return ($a['visibility'] > $b['visibility']) ? -1 : 1;
                }
                if ($a['scope'] <> $b['scope']) {
                    // classifiers before instance
                    return ($a['scope'] < $b['scope']) ? -1 : 1;
                }
                return 0;
            });
            //var_dump($methods);

            foreach ($methods as $method) {
                $s = sprintf($visibility[$method['visibility']], $method['name']);
                $s = sprintf($scope[$method['scope']], $s);
                $result[] = '<tr><td align="left">' . $s . '()</td></tr>';

            }
            $result[] = '</table>';
            $result[] = "  >";

            $result[] = "  ];";
        }
        $result[] = "}";

        return join(PHP_EOL, $result);

    }

}
