# Postinstall dev scripts for Magento 2

Tested with Magento 2.3

# Installation

Composer?

`composer require wizjo/postinstall-m2`

# Usage

The CLI script is available in `vendor/bin/wizjo-postinstall-m2`.

**THE SCRIPT DOES NOT RUN ANY SCRIPTS ON ITS OWN!** It will simply print commands to execute with all required
options and arguments in correct order. To run the command you have to run it manually either by copy-pasta or by piping
it like `wizjo-postinstall-m2 refresh | bash`

## Commands

* `setup:init` - allows to setup fresh Magento installation with sane-defaults without the need to remember all
options required for Magento's `setup:init`. The script also allows you to set deployment mode, default locales and
currencies
* `setup:redis` - allows to setup redis as cache backend for all 3 cache types Magento has to offer: default, page and
session
* `refresh` - command you should use during development only. It will (step by step):
    * remove Magento's generated classes
    * dump composer's autoload
    * run `setup:upgrade` from Magento
    * dump composer's autoload again
    * recompile Magento's generated classes (`setup:di:compile`)
    * flush the cache
    
For more details about each command options and arguments, run the command with `--help` option.
