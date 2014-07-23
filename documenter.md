---
layout: default
---

# Create your own documenter

By default only the [DrupalDocumentation][drupaldocumentation] class is provided. This documenter generates the documentation urls for a Drupal 8 module.

The first step for creating a new documenter is adding a *Documentation.php file in `src/` and add a *Documentation class inside that extends `Documentation`.
Make sure the namespace for this file is `UmlGeneratorPhp`

### The constructor

The constructor has one argument; the $meta variable. This variable contains settings that are [set in the command line runner][metadeclaration].

For most documenters the defaults can be used:

```php
<?php
namespace UmlGeneratorPhp;
use UmlGeneratorPhp\Documentation;

class ExampleDocumentation extends Documentation
{
    protected $meta;

    function __construct($meta)
    {
        $this->meta = $meta;
    }
```

### GetMethodURL (array: $data, array: $classdata) : string

This method generates the documentation url for a class method.

Variables:

- $data This contains the information about the current method.
  - ['name'] the method name.
  - ['scope'] `classifier` for static methods, otherwise `instance`.
  - ['visibility'] Is either `public`, `protected` or `private`.
- $classdata This contains the information about the enclosing class, interface or trait.
  - ['name'] The name of the class, interface or trait.
  - ['type'] Is either `class`, `interface` or `trait`.
  - ['namespace'] This is the current namespace in the format `\Example\namespace`
  - ['meta']
    - ['file'] The file that contains the current method.

### GetPropertyURL (array: $data, array: $classdata) : string

This method generates the documentation url for a class property or constant.

Variables:

- $data This contains the information about the current property or constant.
  - ['name'] the property/constant name.
  - ['scope'] `classifier` for static properties and constants, otherwise `instance`.
  - ['visibility'] Is either `public`, `protected` or `private`.
- $classdata This contains the information about the enclosing class, interface or trait.
  - ['name'] The name of the class, interface or trait.
  - ['type'] Is either `class`, `interface` or `trait`.
  - ['namespace'] This is the current namespace in the format `\Example\namespace`
  - ['meta']
    - ['file'] The file that contains the current property.

### GetObjectURL (array: $data, array: $classdata) : string

This method generates the documentation url for a class, interface or trait.

Variables:

- $data This contains the information about the enclosing class, interface or trait.
  - ['name'] The name of the class, interface or trait.
  - ['type'] Is either `class`, `interface` or `trait`.
  - ['namespace'] This is the current namespace in the format `\Example\namespace`
  - ['meta']
    - ['file'] The file that contains the class.

### The settings

The runner for `generate:dot` contains the settings needed for a documenter to work.
For the Drupal documenter this contains the base url for the documentation site, the base path
for your project (because Drupal uses the relative file path in it's documentation url), The
component (also Drupal documentation specific) and the api version.

To add your own documenter you need to create a new case block. The name of the case is
the name used on the command line for your documenter. Your documenter can be defined multiple times
here with different names and parameters.

[drupaldocumentation]: https://github.com/clemens-tolboom/uml-generator-php/blob/1a23bb1bbb0a3d5cb5a7d62f17c9b6ac4d76d3ca/src/DrupalDocumentation.php
[metadeclaration]: https://github.com/clemens-tolboom/uml-generator-php/blob/1a23bb1bbb0a3d5cb5a7d62f17c9b6ac4d76d3ca/src/Command/DotCommand.php#L57-L64
