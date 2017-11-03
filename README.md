[![Build Status](https://travis-ci.org/jjulien/php-cli-helper.png)](https://travis-ci.org/jjulien/php-cli-helper)

# PHP CLI Helper
PHP CLI Helper was created to make developing PHP command line tools easy.  It currently supports command line option parsing and validation.

## Usage Summary
The main class is the `CLIHelper\Helper`.  You will tell this class all of the options you want to support and how you want that option to behave.  After you tell the helper to parse the options, it will validate that the user has not violated any of the rules you defined for how your options should behave.  If violations are found, the user receives a help message showing them how the option are suppose to work and which option rule they violated.  If no violations are found, you then user the helper object to retrieve values for options and determine which options the user specified.

## Usage Example
**Code**
```
#!/usr/bin/php
<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');

use CLIHelper\Helper;

$helper = new Helper();
$helper->newOption()
    ->withName("file-in")
    ->withHelp("Input file to be processed")
    ->withShort("-i")
    ->withLong("--file-in")
    ->required()
    ->build();
$helper->newOption()
    ->withName("file-out")
    ->withHelp("Output file for processing result")
    ->withShort("-o")
    ->withLong("--file-out")
    ->required()
    ->build();
$helper->newOption()
    ->withName("verbose")
    ->boolean()
    ->withHelp("Display verbose output")
    ->withShort("v")
    ->withLong("verbose")
    ->build();

$helper->parse();
```

**Invocation**
```
$./sample-script.php

Option -i or --file-in is required
Option -o or --file-out is required

Usage: sample-script.php <-i VALUE | --file-in VALUE> <-o VALUE | --file-out VALUE> [-h | --help] [-v | --verbose]
         -h, --help : Displays this message
      -i, --file-in : Input file to be processed
     -o, --file-out : Output file for processing result
      -v, --verbose : Display verbose output
```


## Adding Options
There are two ways to add options.  You can use the `CLIHelper\OptionBuilder` class, which can get using the convenience method `->newOption()` on `CLIHelper\Helper.  You can also create your own `CLIHelper\Option` object and call `->addOption($option)` on `CLIHelper\Helper`.

Options can either by of type `Option::TYPE_BOOLEAN` or `Option::TYPE_VALUE`, the default is `Option::TYPE_VALUE`.  `Option::TYPE_BOOLEAN` options are options that are either on or off, such as the common "verbose" option.  It takes no value, it's either provided on the command line or it is not.  `Option::TYPE_VALUE` options read a value in that comes on the comman line right after the option.  An example would be `--file-in filename` where `--file-in` must have a value provided along with it.

Example using `->addOption($option)`
```
#!/usr/bin/php
<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');

use CLIHelper\Helper;
use CLIHelper\Option;

$helper = new Helper();

$option = new Option();
$option->setName("verbose");
$option->setShortOpt("-v");
$option->setLongOpt("--verbose");
$option->setType(Option::TYPE_BOOLEAN);
$option->setHelp("Display verbose output");
$helper->addOption($option);

$helper->parse();
```

Example using `->newOption()`
```
#!/usr/bin/php
<?php

require(dirname(__FILE__) . '/../vendor/autoload.php');

use CLIHelper\Helper;

$helper = new Helper();
$helper->newOption()
    ->withName("verbose")
    ->boolean()
    ->withHelp("Display verbose output")
    ->withShort("v")
    ->withLong("verbose")
    ->build();

$helper->parse();
```


