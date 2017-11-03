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