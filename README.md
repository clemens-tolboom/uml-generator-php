uml-generator-php
=================

[![Build Status](https://travis-ci.org/clemens-tolboom/uml-generator-php.svg?branch=master)](https://travis-ci.org/clemens-tolboom/uml-generator-php)

## Website

Visit our [website](http://clemens-tolboom.github.io/uml-generator-php/).

## Installation

Clone the git repository
```
$ git clone git@github.com:clemens-tolboom/uml-generator-php.git
```
Install composer dependencies
```
$ cd uml-generator-php
$ composer install
```
And install graphviz with your distro's package manager or from git.
uml-generator-php requires graphviz versions later than 15 September 2013 (See [issue #16](https://github.com/clemens-tolboom/uml-generator-php/issues/16))

#### Old graphviz versions

Some linux distributions (like debian and centos) have very old versions of graphviz. For these situations a `--legacy` switch is added to `generate:dot`. With
this switch the output can be rendered with graphviz 2.26.3 (2010.01.26.1600)

List of distro versions:

| Distro    | Release      | Version |
|-----------|--------------|---------|
| Debian    | wheezy       | *2.26*  |
| Debian    | jessie       | *2.26*  |
| Debian    | sid          | 2.38    |
| Ubuntu    | 12.04 LTS    | *2.26*  |
| Ubuntu    | 14.04 LTS    | 2.36    |
| Centos    | 6            | *2.26*  |
| Archlinux | rolling      | 2.38    |


## Usage

### Parse PHP into json

To parse your source tree for Classes, Interfaces and Traits run

```
$ bin/uml-generator-php generate:json /Users/clemens/Sites/drupal/d8/www tests/output
```

To exclude directories or files you can use the `--skip` parameter (use it multiple times to exclude more directories or files)
The path provided to `--skip` should be a path relative to the input directory.

To generate only the json files for a subdirectory set the `input` to your project root like normal and use the `--only` flag
to set a directory relative to the input to scan. The `--only` flag can be used multiple times to generate json for more directories
or files.

### Generate DOT files

Next generate their dot files by running

```
$ bin/uml-generator-php generate:dot --documenter drupal tests/output
```

You may notice the 'Not found: '. For more info see #50

### Generate SVG files

```bash
find tests/output -type f -name "*.dot" -exec dot -Tsvg -O {} \;
```
