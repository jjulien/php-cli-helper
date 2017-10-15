<?php

namespace CLIHelper;

class OptionTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetType_shouldNotAllowBooleanForRequiredOption() {
        $option = new Option();
        $option->setRequired(true);
        $option->setType(Option::TYPE_BOOLEAN);
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetRequired_shouldNotAllowBooleanForRequiredOption() {
        $option = new Option();
        $option->setType(Option::TYPE_BOOLEAN);
        $option->setRequired(true);
    }

    public function testIsDual_returnsTrueWhenShortAndLongOptsAreSet() {
        $option = new Option();
        $option->setShortOpt("l");
        $option->setLongOpt("long");
        $this->assertTrue($option->isDual());
    }

    public function testIsDual_returnsFalseWhenOnlyShortIsSet() {
        $option = new Option();
        $option->setShortOpt("s");
        $this->assertFalse($option->isDual());
    }

    public function testIsDual_returnsFalseWhenOnlyLongIsSet() {
        $option = new Option();
        $option->setLongOpt("lone");
        $this->assertFalse($option->isDual());
    }

    public function testHelpSummaryLine_usesGreaterThanLessThanForRequired() {
        $option = new Option();
        $option->setShortOpt("s");
        $option->setRequired(true);
        self::assertRegExp("/^<.*>$/", $option->getHelpSummaryLine());
    }

    public function testHelpSummaryLine_usesBracketsForOptional(){
        $option = new Option();
        $option->setShortOpt("s");
        self::assertRegExp("/^\[.*\]$/", $option->getHelpSummaryLine());
    }

    public function testGetShortOptDisplay_hasSingleDashPrefix() {
        $option = new Option();
        $option->setShortOpt("c");
        self::assertRegExp("/^-[^-]/", $option->getShortOptDisplay());
    }

    public function testGetLongOptDisplay_hasDoubleDashPrefix() {
        $option = new Option();
        $option->setLongOpt("long");
        print $option->getLongOptDisplay() . "\n";
        self::assertRegExp("/^--[^-]/", $option->getLongOptDisplay());
    }

    public function testSetShortOpt_stripsLeadingDashIfProvided() {
        $option = new Option();
        $option->setShortOpt("-s");
        self::assertEquals("s", $option->getShortOpt());
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetShortOpt_throwsExceptionIfMoreThanOneDashProvided() {
        $option = new Option();
        $option->setShortOpt("--s");
    }

    public function testSetLongOpt_stripsLeadingDoubleDashIfProvided() {
        $option = new Option();
        $option->setLongOpt("--long");
        self::assertEquals("long", $option->getLongOpt());
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetShortOpt_throwsExceptionIfMoreThanTwoDashesProvided() {
        $option = new Option();
        $option->setLongOpt("---long");
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetLongOpt_throwsExceptionIfSingleLeadingDashProvided() {
        $option = new Option();
        $option->setLongOpt("-long");
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetShortOpt_throwsExceptionIfMoreOneCharacterProvided() {
        $option = new Option();
        $option->setShortOpt("long");
    }

    /**
     * @expectedException \CLIHelper\InvalidOptionException
     */
    public function testSetLongOpt_throwsExceptionIfOnlyOneCharacterProvided() {
        $option = new Option();
        $option->setLongOpt("s");
    }

}
