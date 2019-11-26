# huringa

Change for code.

## Installation

```
composer require sunnysideup/huringa
```

## Running

Run the following script via the terminal. The tool recursively checks folders.

./vendor/sunnysideup/huringa/huringa.php ./app [--dry-run] [--help] [--disable-constructor-rewrite] [--disable-class-file-create]

## Supported operations

This repository handles the following transformations:

### Constructor rewrite

**MyClass.php**
```
class MyClass
{
    - public function MyClass()
    + public function __constructor()
}
```

### Split files

**MyClass.php**
```
class MyClass { }

class MyClass_Controller { }
```

**MyClass.php**
```
class MyClass { }
```

**MyClass_Controller.php**
```
class MyClass_Controller { }
```


## Testing

Test inputs, and samples are located in `tests/samples`

```
./vendor/bin/phpunit tests/UnitTests.php
```