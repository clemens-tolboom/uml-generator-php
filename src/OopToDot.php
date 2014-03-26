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
    protected $documenter = null;

    function __construct(Documentation $documenter = null)
    {
        if (empty($documenter)) {
            $documenter = new Documentation();
        }
        $this->documenter = $documenter;
    }

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
        if(!is_array($array)) return;
        $result = array();

        $result[] = 'graph "Class Diagram" {';
        $result[] = "  node [shape=plaintext]";
        foreach ($array as $index => $values) {
            $meta = $values['meta'];

            $fileUrl = $this->documenter->getObjectURL($values);
            if (!empty($fileUrl)) {
                $fileUrl = ' href="' . $fileUrl . '"';
            }

            $result[] = "  node_$index [";
            $result[] = "    label=<";
            $result[] = '<table border="1" cellpadding="2" cellspacing="0" cellborder="0">';
            $result[] = '<tr><td align="center"' . $fileUrl . ' title="' . $values['type'] . ' ' . $values['name'] . '">' . $values['name'] . '</td></tr><hr />';

            $scope = array(
                'classifier' => '<u>%s</u>',
                'instance' => '%s',
            );
            $scope_tooltip = array(
                // TODO: fix for entity: '&laquo; static &raquo; %s',
                'classifier' => '&lt;&lt; static &gt;&gt; %s',
                'instance' => '%s',
            );

            $visibility = array(
                'public' => '+ %s',
                'protected' => '# %s',
                'private' => '- %s',
            );
            $visibility_tooltip = array(
                'public' => 'public %s',
                'protected' => 'protected %s',
                'private' => 'private %s',
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
                $t = sprintf($visibility_tooltip[$property['visibility']], $property['name']);
                $t = sprintf($scope_tooltip[$property['scope']], $t);

                $propertyUrl = $this->documenter->getPropertyURL($property, $values);
                if (!empty($propertyUrl)) {
                    $propertyUrl = ' href="' . $propertyUrl . '"';
                }
                $result[] = '<tr><td align="left"' . $propertyUrl . ' title="' . $t . '">' . $s . '</td></tr>';
            }
            if (count($properties) == 0) {
                $result[] = '<tr><td></td></tr>';
            }
            $result[] = '<hr />';
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

            foreach ($methods as $method) {
                $s = sprintf($visibility[$method['visibility']], $method['name']);
                $s = sprintf($scope[$method['scope']], $s);
                $t = sprintf($visibility_tooltip[$method['visibility']], $method['name']);
                $t = sprintf($scope_tooltip[$method['scope']], $t);

                $methodUrl = $this->documenter->getMethodURL($method, $values);
                if (!empty($methodUrl)) {
                    $methodUrl = ' href="' . $methodUrl . '"';

                }

                $result[] = '<tr><td align="left"' . $methodUrl . ' title="' . $t . '">' . $s . '()</td></tr>';

            }
            if (count($methods) == 0) {
                $result[] = '<tr><td>&nbsp;</td></tr>';
            }
            $result[] = '</table>';
            $result[] = "  >";

            $result[] = "  ];";
        }
        $result[] = "}";

        return join(PHP_EOL, $result);

    }

}
