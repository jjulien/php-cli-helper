<?php

namespace CLIHelper;

class Option {

    /**
     * Option acts as an on/off boolean switch
     */
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Option requires a value to be provided
     */
    const TYPE_VALUE   = 'value';

    /**
     * The short/single character name for this option
     *
     * @var string
     */
    protected $shortOpt;

    /**
     * The long name for the option
     *
     * @var string
     */
    protected $longOpt;

    /**
     * The name describing this option
     *
     * @var string
     */
    protected $name;

    /**
     * The type of option
     *
     * @var string
     */
    protected $type = Option::TYPE_VALUE;

    /**
     * Is this option required
     *
     * @var boolean
     */
    protected $required = false;

    /**
     * Help message for this option
     *
     * @var string
     */
    protected $help;

    /**
     * The default value for this option
     *
     * @var mixed
     */
    protected $default;

    /**
     * Errors identified with the option when isComplete is called
     *
     * @var string[]
     */
    protected $optionErrors;

    /**
     * Returns the short option string
     *
     * @return string
     */
    public function getShortOpt()
    {
        return $this->shortOpt;
    }

    /**
     * Set a single character to be used as the short option.  You can provide either a single character
     * such as "s" or you can optionally provide a leading dash such as "-s"
     *
     * @param string $shortOpt
     * @return $this
     * @throws InvalidOptionException
     */
    public function setShortOpt($shortOpt)
    {
        $opt = preg_replace("/^-/", "", $shortOpt);
        if (preg_match("/^-/", $opt)) {
            throw new InvalidOptionException("Short options cannot have more than one leading dashes");
        } elseif (strlen($opt) > 1) {
            throw new InvalidOptionException("Short options can only be a single characters");
            // If the length of $opt is 0 then that means either a blank string or single dash were passed in
        } elseif (strlen($opt) == 0 || ! preg_match("/^[a-zA-Z]/", $opt)) {
            throw new InvalidOptionException("Short options must be a single alpha character");
        }
        $this->shortOpt = $opt;
        return $this;
    }

    /**
     * Returns the long option string
     *
     * @return string
     */
    public function getLongOpt()
    {
        return $this->longOpt;
    }

    /**
     * Sets the long option name for this option.  Long option names must be 2 or more characters.
     * When calling this method you can either provide just the long option name, such as "file-in"
     * or you can optionally prefix it with two leading dashes, such as "--file-in"
     *
     * @param string $longOpt
     * @throws InvalidOptionException
     */
    public function setLongOpt($longOpt)
    {
        $opt = preg_replace("/^--/", "", $longOpt);
        if (preg_match("/^-/", $opt)) {
            throw new InvalidOptionException("Long options must have exactly two leading dashes");
        } elseif (strlen($opt) < 2) {
            throw new InvalidOptionException("long options must be at least 2 characters long");
        } elseif (!preg_match("/^[a-zA-Z]+-*.*/", $opt) ) {
            throw new InvalidOptionException("long options must be made of alpha characters or dashes, and have to begin with exactly 2 dashes");
        }
        $this->longOpt = $opt;
    }

    /**
     * Returns the type of this option.  Options can be of type BOOLEAN or VALUE.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets the type of the option.  Options can be of type BOOLEAN or VALUE.  BOOLEAN options
     * are options that are either on or off, such as the common "verbose" option.  It takes no
     * value, it's either on or off.  VALUE options read a value in that comes on the command
     * line right after the option.  An example would be "--file-in filename" where "--file-in"
     * must have a value provided along with it.
     *
     * @param string $type
     * @throws InvalidOptionException
     */
    public function setType($type)
    {
        if ($type == self::TYPE_BOOLEAN && $this->required) {
            throw new InvalidOptionException("An option cannot be both required and of type BOOLEAN");
        }
        $this->type = $type;
    }

    /**
     * Returns true if this option is required
     *
     * @return booelan
     */
    public function isRequired()
    {
        return $this->required;
    }

    /**
     * Indicate that this option is required and must be provided by the user.  Required
     * options must take a value in and cannot be boolean options, as a required boolean
     * option would always be the same as saying this option must always be on.  There would
     * be no way for the user to customize how a required boolean option behaves.
     *
     * @param $required
     * @throws InvalidOptionException
     */
    public function setRequired($required)
    {
        if ($required && $this->type == self::TYPE_BOOLEAN) {
            throw new InvalidOptionException("An option cannot be both required and of type BOOLEAN");
        }
        $this->required = $required;
    }

    /**
     * Gets the help message to be displayed to the user for this option if help is selected
     * or if an option validation fails
     *
     * @return string
     */
    public function getHelp()
    {
        return $this->help;
    }

    /**
     * Sets the help message to be displayed to the user for this option if help is selected
     * or if an option validation fails
     *
     * @param string $help
     */
    public function setHelp($help)
    {
        $this->help = $help;
    }

    /**
     * Returns the default value for this option.  This value would be used if the option was not
     * provided on the command line
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set the default value to be used for this option if it is not provided by the
     * user on the command line.
     *
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->default = $default;
    }

    /**
     * Set the name of this option.  The option name is used to retrieve information about
     * the option, such as if it was used by the user and what value they provided.  Because
     * options can have both a short and long option, the name is the way the option is uniquely
     * identified in your program.
     *
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * Returns the name of the option
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns true if this option has both a short and long option
     *
     * @return boolean
     */
    public function isDual() {
        return ($this->getShortOpt() && $this->getLongOpt());
    }

    /**
     * Returns the value that should be displayed on the help summary line of the usage clause
     * Required options are wrapped in < > and optional in [ ].  Mutually exclusive options are
     * separated with a |.  Options that require a value also contain the word VALUE after them.
     *
     * Examples:
     *   <-r VALUE| --required VALUE>
     *   [-o | --optional]
     *   [--not-mutually-exclusive VALUE]
     *   [-n]
     *
     * @return string
     */
    public function getHelpSummaryLine() {
        if ($this->getType() == self::TYPE_VALUE) {
            $valueString = " VALUE";
        } else {
            $valueString = "";
        }

        if ($this->isRequired()) {
            $openGroup = "<";
            $closeGroup = ">";
        } else {
            $openGroup = "[";
            $closeGroup = "]";
        }
        $summary = $openGroup;
        if ($this->isDual()) {
            $summary .= "-" . $this->getShortOpt() . $valueString . " | --" . $this->getLongOpt() . $valueString;
        } else {
            if ($this->getShortOpt()) {
                $summary .= "-" . $this->getShortOpt() . $valueString;
            }
            else {
                $summary .= "--" . $this->getLongOpt() . $valueString;
            }
        }
        $summary .= $closeGroup;
        return $summary;
    }

    /**
     * Returns the option string prefixed with a single dash.  This is
     * how the user would invoke this option on the command line.
     * @return string
     */
    public function getShortOptDisplay() {
        return "-" . $this->getShortOpt();
    }

    /**
     * Returns the option string prefixed with a double dash.  This is
     * how the user would invoke this option on the command line.
     *
     * @return string
     */
    public function getLongOptDisplay() {
        return "--" . $this->getLongOpt();
    }

    /**
     * Returns the errors with this option.  These errors would indicate a programming
     * error, not a user error.  Such as forgetting to give an option a name.  It is used
     * by the helper to validate new options that are being added
     *
     * @return \string[]
     */
    public function getOptionErrors() {
        return $this->optionErrors;
    }


    /**
     * Tests if an option is complete, aka. has enough information to be useable.  Options
     * must have a name, type and at least a short or long opt
     *
     * @return boolean
     */
    public function isComplete() {
        $this->optionErrors = array();
        if (!$this->getName()) {
            $this->optionErrors[] = "Options must have a name";
        }
        if (!$this->getShortOpt() && !$this->getLongOpt()) {
            $this->optionErrors[] = "Options must have at least a short or long option specified";
        }
        if (!$this->getType()) {
            $this->optionErrors[] = "Options must have a type set";
        }
        return (count($this->getOptionErrors()) == 0);
    }
}