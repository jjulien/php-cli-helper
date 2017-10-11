<?php

namespace CLIHelper;

class Helper {

    /**
     * Number of characters to use for a single line in the help message before wrapping.  This does not include
     * characters of the option display, but only includes the help message displayed to the right of the option
     * @var int
     */
    protected $maxHelpChars = 50;

    /**
     * An array of argument definitions
     * @var Option[]
     */
    protected $options = array();

    /**
     * Associative array returned get getopt
     *
     * @var array
     */
    protected $parsedOptions;

    /**
     * The name of the script being executed with this helper
     *
     * @var string
     */
    protected $scriptName;

    /**
     * Helper constructor.
     */
    public function __construct() {
        global $argv;
        $this->scriptName = basename(realpath($argv[0]));
        $helpOption = new Option();
        $helpOption->setName("help");
        $helpOption->setType(Option::TYPE_BOOLEAN);
        $helpOption->setShortOpt("h");
        $helpOption->setLongOpt("help");
        $helpOption->setHelp("Displays this message");
        $this->addOption($helpOption);
    }

    /**
     * @param Option $opt
     */
    public function addOption(Option $opt) {
        $this->options[$opt->getName()] = $opt;
    }

    /**
     * @return Option[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param Option[] $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * @param string $name
     * @return Option
     */
    public function getOption($name) {
        return $this->options[$name];
    }

    /**
     * @return mixed
     */
    public function getParsedOptions() {
        return $this->parsedOptions;
    }

    /**
     * @return string
     */
    public function getScriptName() {
        return $this->scriptName;
    }

    /**
     * @return int
     */
    private function getLongestOptionLength() {
        $length = 0;
        foreach ($this->getOptions() as $opt) {
            if ($opt->isDual()) {
                // +5 account for a short -, long --, and a comma and space
                $length = max(array(strlen($opt->getShortOpt()) + strlen($opt->getLongOpt()) + 5, $length));
            } else {
                if ($opt->getLongOpt()) {
                    $length = max(array(strlen($opt->getLongOpt()) + 2, $length));
                } else {
                    $length = max(array(strlen($opt->getShortOpt()) + 1, $length));
                }
            }
        }
        return $length;
    }

    /**
     * Displays the help string to STDOUT for this program and all options
     */
    public function printHelp() {
        fwrite(STDOUT, "\nUsage: " . $this->getScriptName() . " " . $this->getHelpOptionSummary() . "\n");
        foreach ($this->getOptions() as $opt) {
            if ($opt->getHelp()) { $help = $opt->getHelp(); }
            else { $help = "No help available"; }
            $length = $this->getLongestOptionLength() + 5;
            if ($opt->isDual()) {
                $optionString = "-" . $opt->getShortOpt() . ", --" . $opt->getLongOpt();
            } else {
                if ($opt->getShortOpt()) { $optionString = "-" . $opt->getShortOpt(); }
                else { $optionString = "--" . $opt->getLongOpt(); }
            }
            fwrite(STDOUT, sprintf("%" . $length . "s : ", $optionString));
            $this->displayHelpMessageForOption($help, $length + 3);
            //$helpLength = strlen($help);
            //$helpCharsDisplayed = 0;
            //while ($helpCharsDisplayed < $helpLength) {
            //    fwrite(STDOUT, sprintf("%s\n", substr($help, $helpCharsDisplayed, $this->maxHelpChars)));
            //    $helpCharsDisplayed += $this->maxHelpChars;
           // }
        }
        fwrite(STDOUT, "\n");
    }

    private function displayHelpMessageForOption($help, $padlength) {
        // IF string contains \n, then stop it there
        // If any new or end of line contains two or less white spaces, then clear it
        // If it contains more than two, leave all white space, this may be user desired
        // formatting.

        $helpLength = strlen($help);
        $helpCharsDisplayed = 0;

        $lines = 0;
        while ($helpCharsDisplayed < $helpLength) {
            $chunk = substr($help, $helpCharsDisplayed, $this->maxHelpChars);

            // Check if string has a newline
            $newline = strpos("\n", $chunk);
            if ($newline) {
                // We want to actually include the new line in display.  This was intentionally put there by a user
                $displayString = substr($chunk, 0, $newline + 1);
                $helpCharsDisplayed += strlen($displayString);
            } elseif ( strlen($chunk) + $helpCharsDisplayed == $helpLength ) {
                // We have hit the end of the string
                $displayString = $chunk;
                $helpCharsDisplayed += strlen($chunk);
            } else {
                 $lastSpace = strrpos($chunk, " ");
                 if ($lastSpace) {
                     // We are throwing away trailing spaces only if they are 2 or less.  Including the
                     // space in displayString will help use calculate how many trailing spaces there are
                     $displayString = substr($chunk, 0, $lastSpace + 1);
                 } else {
                     $displayString = $chunk;
                 }
                 $originalLength = strlen($displayString);
                 $displayString = rtrim($displayString);
                 $trimmedLength = strlen($displayString);
                 if ($originalLength - $trimmedLength > 2)  {
                     // we need to save the trailing spaces.  We will remove them from this line, but not
                     // increment $helpCharsDisplayed to make sure they are the first thing displayed on
                     // the next line
                     $helpCharsDisplayed += $trimmedLength;
                 } else {
                     // we are discarding the trailing spaces, we must increment $helpCharsDisplayed to
                     // reflect that we displayed them, even though we didn't
                     $helpCharsDisplayed += $originalLength;
                 }
            }
            if ($lines > 0 ) {
                $displayString = str_pad($displayString, strlen($displayString) + $padlength, " ",STR_PAD_LEFT);
            }
            fwrite(STDOUT, sprintf("%s\n", $displayString));
            $lines++;
        }
    }

    private function getHelpOptionSummary() {
        $requiredString = "";
        $optionalString = "";

        // Required will be < -f | --file > for long and short or just --file or -f for either long or short
        // Optional will be [-f|--file] or just [-f] or [--file]
        // We will group all required options first
        foreach ($this->getOptions() as $opt) {
            if ($opt->isRequired()) {
                if ($requiredString) { $requiredString .= " "; }
                $requiredString .= $opt->getHelpSummaryLine();
            } else {
                if ($optionalString) { $optionalString .= " "; }
                $optionalString .= $opt->getHelpSummaryLine();
            }
        }
        return $requiredString . " " . $optionalString;
    }

    /**
     * Returns the help string for this program and all arguments
     *
     * @return string
     */
    public function getHelp() {

    }

    public function getValue($name) {
        if (!array_key_exists($name, $this->getOptions())) {
            throw new OptionNotFoundException();
        }

        // Get argument
        $arg = $this->getOptions()[$name];
        // Check for boolean arguments
        if ($arg->getType() == Option::TYPE_BOOLEAN) {
               if ( array_key_exists($arg->getShortOpt(), $this->getParsedOptions()) ||
                    array_key_exists($arg->getLongOpt(), $this->getParsedOptions()) ) {
                   return true;
               } else {
                   return false;
               }
        }

        // Check for value arguments
        if ($arg->getType() == Option::TYPE_VALUE) {
            if (array_key_exists($arg->getShortOpt(), $this->getParsedOptions())) {
                return $this->getParsedOptions()[$arg->getShortOpt()];
            }
            if (array_key_exists($arg->getLongOpt(), $this->getParsedOptions())) {
                return $this->getParsedOptions()[$arg->getLongOpt()];
            }
        }
    }

    /**
     * Triggers parsing of all provided Option objects.  This will enforce all rules set for parameters and generate
     * errors encountered during the parsing process.  After calling this method, the helper is ready to provide
     * values for all arguments that were passed on the command line
     */
    public function parse() {
        $this->parsedOptions = $this->getOptParse();
        if ($this->getValue("help")) {
            $this->printHelp();
            exit(0);
        }
    }

    /**
     * Parses the array of Option objects, generates getopt parameters and returns the associative array from getopt
     *
     * @return array
     */
    protected function getOptParse() {
        $shortOpts = "";
        $longOpts = array();
        foreach ($this->getOptions() as $key => $arg ) {
            if ($arg->getShortOpt()) {
                $shortOpts .= $arg->getShortOpt();
                if ($arg->getType() != Option::TYPE_BOOLEAN) {
                    $shortOpts .= ":";
                }
            }
            if ($arg->getLongOpt()) {
                $longOpt = $arg->getLongOpt();
                if ($arg->getType() != Option::TYPE_BOOLEAN) {
                    $longOpt .= ":";
                }
                $longOpts[] = $longOpt;
            }
        }
        return getopt($shortOpts, $longOpts);
    }
}
