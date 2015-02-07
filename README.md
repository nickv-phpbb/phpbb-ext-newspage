# phpBB 3.1 Newspage Extension

NV Newspage is an Extension for [phpBB 3.1](https://www.phpbb.com/)

[![Build Status](https://secure.travis-ci.org/nickvergessen/phpbb-ext-newspage.png?branch=develop-ascraeus)](https://travis-ci.org/nickvergessen/phpbb-ext-newspage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nickvergessen/phpbb-ext-newspage/badges/quality-score.png?b=develop-ascraeus)](https://scrutinizer-ci.com/g/nickvergessen/phpbb-ext-newspage/?branch=develop-ascraeus)


## Description

Adds an extra page to the board where a switchable number of news can be displayed.
The news texts can be shorten to a given number of characters.
Also the icons can be switched on/off (post icons, user icons)


## Development Installation

Clone into phpBB/ext/nickvergessen/newspage:

    git clone https://github.com/nickvergessen/phpbb-ext-newspage.git phpBB/ext/nickvergessen/newspage

Set up the dependencies:

    php composer.phar install --dev

Go to "ACP" > "Customise" > "Extensions" and enable the "phpBB 3.1 NV Newspage Extension" extension.


## List of events

See the [documentation](docs/events.md) for a list of available php and template events.


## Live Installation

If you want to install the extension in a live board, please only use official releases.
Note that github releases are **NOT** the releases you are looking for.


## Collaborate

* Create a issue in the [tracker](https://github.com/nickvergessen/phpbb-ext-newspage/issues)
* Submit a [pull-request](https://github.com/nickvergessen/phpbb-ext-newspage/pulls)


## Testing

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB development wiki](https://wiki.phpbb.com/Unit_Tests).


## License

[GPLv2](license.txt)
