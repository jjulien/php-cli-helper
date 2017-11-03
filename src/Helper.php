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
     * Stores a list of errors encountered after parsing the command line options
     *
     * @var array
     */
    protected $parsedOptionErrors;

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
        $this->scriptName = basename($_SERVER['argv'][0]);
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
        // Option must have a name, and at least a short or long arg
        if (!$opt->isComplete()) {
            throw new InvalidOptionException(implode("; ", $opt->getOptionErrors()));
        }
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
    public function getLongestOptionLength() {
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
     * Displays the help string to file handle (default: STDOUT) for this program and all options
     *
     * @param $handle
     */
    public function printHelp($handle=STDOUT) {
        fwrite($handle, $this->getHelp());
    }

    /**
     * Returns the help string
     *
     * @return string
     */
    public function getHelp() {
        $helpMessage = "";
        $helpMessage .= "\nUsage: " . $this->getScriptName() . " " . $this->getHelpOptionSummary() . "\n";
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
            $helpMessage .= sprintf("%" . $length . "s : ", $optionString);
            $helpMessage .= $this->getDisplayHelpMessageForOption($help, $length + 3);
        }
        $helpMessage .= "\n";
        return $helpMessage;
    }

    /**
     * Get the extended help message for an option.  This takes into account the
     * length of the message and implements word wrapping.  While wrapping, it attempts to be smart
     * enough to keep deliberate spacing and new line characters that may be desired by
     * the user.
     *
     * Basic logic is if the message contains a \n then it should be honored.  When wrapping
     * a line, if there are 2 or less trailing whitespaces, they are discarded, if there are
     * more than 2 white spaces they will be wrapped and displayed on the following line
     *
     * @param $help
     * @param $padlength
     * @return string
     */
    private function getDisplayHelpMessageForOption($help, $padlength) {
        $extendedHelp = "";
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
            $extendedHelp .= sprintf("%s\n", $displayString);
            $lines++;
        }
        return $extendedHelp;
    }

    /**
     * Returns a string for the option summary line which is the first line of the help output.
     * Required options are displayed first, followed by optional ones.  Required options are
     * wrapped in < > and optional in [ ].  Mutually exclusive options are separated with a |.
     *
     * Options that require a value also contain the word VALUE after them.
     *
     * Example:
     * Usage: script_name <-r VALUE| --required VALUE> [-o | --optional] [--not-mutually-exclusive VALUE] [-n]
     * @return string
     */
    private function getHelpOptionSummary() {
        $requiredString = "";
        $optionalString = "";

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
     * Returns the value for an option.  Returns a boolean or a string
     *
     * @param $name
     * @return mixed
     * @throws OptionNotFoundException
     */
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
            $this->end(0);
        }

        if (! $this->validateParsedOptions()) {
            $this->displayParsedErrorOptions();
            $this->printHelp(STDERR);
            $this->end(1);
        }
    }

    /**
     * Helper method that is used so testing can mock and avoid program termination
     *
     * @param $code
     */
    public function end($code) {
        exit($code);
    }

    /**
     * Print errors encountered during option parsing to STDERR
     */
    public function displayParsedErrorOptions() {
        fwrite(STDERR, "\n");
        foreach ($this->parsedOptionErrors as $error) {
            fwrite(STDERR, $error . "\n");
        }
    }

    /**
     * Validate the options provided match all of the rules specified for each option.
     *
     * - Mutually exclusive options, such as options that have both a short and long form
     *   cannot be used together, such as -f or --file
     * - Options that require a value must have a value provided
     * - Required options must be provided
     *
     * @return boolean
     */
    public function validateParsedOptions() {
        $this->parsedOptionErrors = array();
        foreach ($this->getOptions() as $opt) {
            // Validate mutually exclusive options
            try {
                $usedOption = $this->getUsedOption($opt);
            } catch (MutuallyExclusiveOptionException $e) {
                $this->parsedOptionErrors[] = "Options " . $opt->getShortOptDisplay() . " and " . $opt->getLongOptDisplay() . " are mutually exclusive.  You cannot use them both.";
                continue;
            }

            if ($usedOption && $opt->getType() == Option::TYPE_VALUE && !$this->getValue($opt->getName())) {
                // Validate "Value" options have values
                $this->parsedOptionErrors[] = "Option " . $usedOption . " requires a value to be provided";
            } elseif ($opt->isRequired() && !$this->getValue($opt->getName())) {
                // Validate required options
                if ($opt->isDual()) {
                    $optionDisplay = $opt->getShortOptDisplay() . " or " . $opt->getLongOptDisplay();
                } else {
                    if ($opt->getShortOpt()) {
                        $optionDisplay = $opt->getShortOptDisplay();
                    } else {
                        $optionDisplay = $opt->getLongOptDisplay();
                    }
                }
                $this->parsedOptionErrors[] = "Option " . $optionDisplay . " is required";
            }
        }
        return (count($this->parsedOptionErrors) == 0);
    }

    /**
     * Returns the short or long option that was used on the command line, if it was used
     *
     * @param Option $opt
     * @return string
     * @throws MutuallyExclusiveOptionException
     */
    public function getUsedOption(Option $opt) {
        // If an option requires a value, and it is the last option on the command line, getopts
        // assumes there is no value and doesn't include it in the parsed option list at all.
        // We don't want to throw it away like this, and rather let the user know they have used
        // an option, but that option requires a value.  This is why we are also checking
        // $_SERVER['argv'] here
        $short = ( array_key_exists($opt->getShortOpt(), $this->getParsedOptions()) ||
                   in_array($opt->getShortOptDisplay(), $_SERVER['argv']));
        $long = ( array_key_exists($opt->getLongOpt(), $this->getParsedOptions()) ||
                  in_array($opt->getLongOptDisplay(), $_SERVER['argv']));

        if ($opt->isDual() && $short && $long ) {
            throw new MutuallyExclusiveOptionException();
        } elseif ($short) {
            return $opt->getShortOptDisplay();
        } elseif ($long) {
            return $opt->getLongOptDisplay();
        } else {
            return false;
        }
    }


    /**
     * Parses the array of Option objects, generates getopt parameters and returns the associative array from getopt
     *
     * @return array
     */
    public function getOptParse() {
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

    /**
     * @return array
     */
    public function getParsedOptionErrors() {
        return $this->parsedOptionErrors;
    }


    /**
     * @return OptionBuilder
     * @internal param $name
     */
    public function newOption() {
        return new OptionBuilder($this);
    }

}
