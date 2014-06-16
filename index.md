---
layout: default
---

### Generate UML diagrams for your PHP code.

This tool wil generate UML diagrams with all class, interface and trait definitions in your PHP project without
depending on autoloaders.
Instead it uses the PHP Abstract Language Tree from [php-parser][php-parser] to build the class graph.

The project is divided in 2 tools. One parses all your PHP code and generates a new directory structure
containing JSON files. The other parses the JSON structure and generates .dot files for [Graphviz][graphviz].
Then you can use the Graphviz toolkit to render the diagrams to your image format of choice.

If you happen to choose the SVG output format and defined a documentation module for `json2dot`
the class and method names will be clickable and lead to the defined URLs. The repo currently contains a
documentation module for api.drupal.org.


### Example output.

This is some example output generated from the Drupal 8 master branch. Some rendering issues may occur
because of broken SVG renderers in browsers. All methods and properties are clickable and link to the Drupal 8 api
documentation website.

<div class="svgexample">
    <a href="images/output/entity.svg"><img src="images/output/entity.svg"></a><br>
    EntityFormController from Drupal
</div>
<div class="svgexample">
    <a href="images/output/diff.svg"><img src="images/output/diff.svg"></a><br>
    Diff from Drupal
</div>
<br style="clear: both;">

### Installation instructions.

Clone the git repository

```bash
$ git clone git@github.com:clemens-tolboom/uml-generator-php.git
```

Install composer dependencies

```bash
$ cd uml-generator-php
$ composer install
```

And install graphviz with your distro's package manager or from git.<br>
uml-generator-php requires graphviz versions later than 15 September 2013 (See [issue #16][issue16])

### Usage example.

To parse your source tree for Class Interface and Traits run

```bash
$ mkdir tests/output
$ bin/oop2json /Users/clemens/Sites/drupal/d8/www tests/output
```

Next generate their dot files by running

```bash
$ bin/json2dot -u drupal -d tests/output
```

Then use the graphviz tools to render SVG or image output

```bash
$ bin/json2dot -u drupal -d tests/output | xargs -I {} dot -Tsvg -O {}
```

If you want to monitor progress use the `xargs -t` option

```bash
$ bin/json2dot -u drupal -d tests/output | xargs -t -I {} dot -Tsvg -O {}
```

  [php-parser]: https://github.com/nikic/php-parser
  [graphviz]: http://graphviz.org/
  [issue16]: https://github.com/clemens-tolboom/uml-generator-php/issues/16