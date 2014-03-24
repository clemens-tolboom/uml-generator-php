uml-generator-php
=================

[![Build Status](https://travis-ci.org/clemens-tolboom/uml-generator-php.svg?branch=master)](https://travis-ci.org/clemens-tolboom/uml-generator-php)

Installation
------------
Clone the git repository
```
$ git clone git@github.com:clemens-tolboom/uml-generator-php.git
```
Install composer dependencies
```
$ cd uml-generator-php
$ composer install
```

Usage
-----
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
