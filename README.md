uml-generator-php
=================

To parse your source tree for Class Interface and Traits run

```
$ mkdir tests/output
$ bin/oop2json /Users/clemens/Sites/drupal/d8/www tests/output
```

Next generate their dot files by running

```
$ bin/json2dot tests/output
```

or even generate SVG files

```
$ bin/json2dot tests/output | xargs -I {} dot -Tsvg -O {}
```

In case you want to monitor progress use the `xargs -t` switch

```
$ bin/json2dot tests/output | xargs -t -I {} dot -Tsvg -O {}
```
