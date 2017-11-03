<?php

use \Mockery as m;
use \CLIHelper\Option;
use \CLIHelper\Helper;

class HelperTest extends \PHPUnit_Framework_TestCase {

    /**
     * @return Option
     */
    public function getOption() {
        return m::mock('\CLIHelper\Option');
    }

    public function testAddOption_AddsOptionToArrayIfValid() {
        $opt = m::mock('\CLIHelper\Option');
        $opt->shouldReceive('isComplete')->andReturn(true);
        $opt->shouldReceive('getName')->andReturn('name');
        $helper = new Helper();
        $helper->addOption($opt);
        $this->assertTrue(in_array($opt, $helper->getOptions()));
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testAddOption_ThrowsExceptionIfOptionInvalid() {
        $opt = $this->getOption();
        $opt->shouldReceive('isComplete')->andReturn(false);
        $opt->shouldReceive('getOptionErrors')->andReturn(array('option error'));
        $helper = new Helper();
        $helper->addOption($opt);
    }

    public function testGetLongestOptionLength_returnsTwoIfOnlyShortOpts() {
        $helper = m::mock('\CLIHelper\Helper')->makePartial();
        $opt = new Option();
        $opt->setName("test");
        $opt->setShortOpt("t");
        $helper->addOption($opt);
        $this->assertEquals(2, $helper->getLongestOptionLength());
    }

    public function testGetLongestOptionLength_returnsLengthOfLongestLongOptPlus2() {
        $helper = m::mock('\CLIHelper\Helper')->makePartial();
        $opt = new Option();
        $opt->setName("test");
        $opt->setLongOpt("test");
        $helper->addOption($opt);
        $this->assertEquals(6, $helper->getLongestOptionLength());

    }

    public function testGetHelp_startsWithAUsageLine() {
        $helper = new Helper();
        $opt = new Option();
        $opt->setName("test");
        $opt->setLongOpt("test");
        $helper->addOption($opt);
        $this->assertStringStartsWith("Usage:", trim($helper->getHelp()));

    }

    public function testGetHelp_ProvidesDetailsForEveryOption() {
        $helper = new Helper();
        $opt = new Option();
        $opt->setName("test");
        $opt->setLongOpt("test");
        $helper->addOption($opt);

        $opt = new Option();
        $opt->setName("another");
        $opt->setShortOpt("a");
        $helper->addOption($opt);

        $this->assertRegExp('/\\n\s+--test /', $helper->getHelp());
        $this->assertRegExp('/\\n\s+-a /', $helper->getHelp());

    }

    public function testGetHelpOptionSummary_listsRequiredOptionsFirst() {
        $helper = new Helper();
        $opt = new Option();
        $opt->setName("test");
        $opt->setLongOpt("test");
        $helper->addOption($opt);

        $opt = new Option();
        $opt->setName("another");
        $opt->setShortOpt("a");
        $opt->setRequired(true);
        $helper->addOption($opt);
        $this->assertRegExp('/\s+Usage:.*-a.*--test/', $helper->getHelp());
    }

    public function testDisplayHelpMessageForOption_wrapsLongWordsWithoutTruncatingWords() {
        $helper = new Helper();
        $opt = new Option();
        $opt->setName("test");
        $opt->setLongOpt("test");
        $opt->setHelp("Messages wrap at 50 chars, so thislongwordshouldwraptothenextlinenotbetruncated");
        $helper->addOption($opt);
        $this->assertRegExp("/thislongwordshouldwraptothenextlinenotbetruncated/", $helper->getHelp());
    }


    /**
     * @expectedException \CLIHelper\OptionNotFoundException
     */
    public function testGetValue_throwsExceptionIfOptionNotFound() {
        $helper = new Helper();
        $helper->getValue("unknown");
    }

    public function testGetValue_returnsTrueForBooleanOptionWhenShortOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("booleantest");
        $opt->setShortOpt("-b");
        $opt->setType(Option::TYPE_BOOLEAN);
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array("b" => ""));
        $helper->parse();
        $this->assertTrue($helper->getValue("booleantest"));
    }

    public function testGetValue_returnsTrueForBooleanOptionWhenLongOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("booleantest");
        $opt->setLongOpt("--boolean");
        $opt->setType(Option::TYPE_BOOLEAN);
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array("boolean" => ""));
        $helper->parse();
        $this->assertTrue($helper->getValue("booleantest"));
    }

    public function testGetValue_returnsFalseForBooleanOptionWhenShortOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("booleantest");
        $opt->setShortOpt("-b");
        $opt->setType(Option::TYPE_BOOLEAN);
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array());
        $helper->parse();
        $this->assertFalse($helper->getValue("booleantest"));
    }

    public function testGetValue_returnsFalseForBooleanOptionWhenLongOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("booleantest");
        $opt->setLongOpt("--boolean");
        $opt->setType(Option::TYPE_BOOLEAN);
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array());
        $helper->parse();
        $this->assertFalse($helper->getValue("booleantest"));
    }

    public function testGetValue_returnsAValueWhenShortOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("valuetest");
        $opt->setShortOpt("-s");
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array("s" => "value"));
        $helper->parse();
        $this->assertEquals("value", $helper->getValue("valuetest"));
    }


    public function testGetValue_returnsAValueWhenLongOptIsUsed() {
        $helper = m::mock('\CLIHelper\Helper[getOptParse]', array())->makePartial();
        $opt = new Option();
        $opt->setName("valuetest");
        $opt->setLongOpt("--long");
        $helper->addOption($opt);
        $helper->shouldReceive('getOptParse')->andReturn(array("long" => "value"));
        $helper->parse();
        $this->assertEquals("value", $helper->getValue("valuetest"));
    }

    public function testParse_callsPrintHelpIfHelpOptionIsUsed() {
        $helper = $this->getMock('\CLIHelper\Helper', array('printHelp', 'getOptParse', 'end'));
        $helper->expects($this->once())->method('printHelp');
        $helper->expects($this->once())->method('end');
        $helper->expects($this->once())->method('getOptParse')->willReturn(array("help" => ""));
        $helper->parse();
    }

    public function testParse_callsPrintHelpIfOptionsNotValid() {
        $helper = $this->getMock('\CLIHelper\Helper', array('printHelp', 'getOptParse', 'end', 'displayParsedErrorOptions'));
        $helper->expects($this->once())->method('getOptParse')->willReturn(array("s" => "", "long" => ""));
        $helper->expects($this->once())->method('displayParsedErrorOptions');
        $helper->expects($this->once())->method('printHelp');
        $helper->expects($this->once())->method('end');

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $helper->parse();
    }

    public function testValidateParsedOptions_addsErrorWhenShortAndLongOptAreBothUsed() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array("s" => "", "long" => ""));

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $helper->validateParsedOptions();
        $this->assertEquals(1, count($helper->getParsedOptionErrors()));
    }

    public function testValidateParsedOptions_addsErrorWhenTypeIsValueAndNoValueProvided() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array("s" => ""));

        $opt = new Option();
        $opt->setName("name");
        $opt->setType(Option::TYPE_VALUE);
        $opt->setShortOpt("s");
        $helper->addOption($opt);

        $helper->validateParsedOptions();
        $this->assertEquals(1, count($helper->getParsedOptionErrors()));
    }

    public function testValidateParsedOptoins_addsErrorWhenRequiredOptionIsNotProvided() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array());

        $opt = new Option();
        $opt->setName("name");
        $opt->setType(Option::TYPE_VALUE);
        $opt->setShortOpt("s");
        $opt->setRequired(true);
        $helper->addOption($opt);

        $helper->validateParsedOptions();
        $this->assertEquals(1, count($helper->getParsedOptionErrors()));
    }

    /**
     * @expectedException \CLIHelper\MutuallyExclusiveOptionException
     */
    public function testGetUsedOption_throwsExceptionIfShortAndLongOptionAreUsed() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array("s" => "", "long" => ""));

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $helper->getUsedOption($opt);
    }

    public function testGetUsedOption_returnsShortOptDisplayForShortOptions() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array("s" => ""));

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $this->assertEquals($opt->getShortOptDisplay(), $helper->getUsedOption($opt));
    }

    public function testGetUsedOption_returnsLongOptDisplayForLongOptions() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array("long" => ""));

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $this->assertEquals($opt->getLongOptDisplay(), $helper->getUsedOption($opt));
    }

    public function testGetUsedOption_returnsFalseIfOptionNotUsed() {
        $helper = $this->getMock('\CLIHelper\Helper', array('getParsedOptions'));
        $helper->expects($this->any())->method('getParsedOptions')->willReturn(array());

        $opt = new Option();
        $opt->setName("name");
        $opt->setShortOpt("s");
        $opt->setLongOpt("long");
        $helper->addOption($opt);

        $this->assertFalse($helper->getUsedOption($opt));
    }

    /**
     * This may seem like a pointless test, but it's very important to make sure no regressions are introduced.
     * The standard C library getopt does NOT show an option as being used if the option requires a value and
     * none is specified.  This is of course only possible if the option is the last option on the command line,
     * because any option before the last would assume the next option is the value of the previous option.
     */
    public function testGetUsedOption_returnsAnOptionRequiringAValueWhenOptionIsTheLastOptionOnCommandLine() {
        $helper = $this->getMock('\CLIHelper\Helper', array('validateParsedOptions'));
        $helper->expects($this->any())->method('validateParsedOptions')->willReturn(true);

        // Save off global argv so we can modify it
        $save = $_SERVER['argv'];

        $_SERVER['argv']  = array("scriptname", "--param1", "--valueparam");
        $opt = new Option();
        $opt->setName("name");
        $opt->setLongOpt("valueparam");
        $helper->addOption($opt);
        $helper->parse();
        $this->assertEquals($opt->getLongOptDisplay(), $helper->getUsedOption($opt));

        // Restore global argv
        $_SERVER['argv'] = $save;

    }

}