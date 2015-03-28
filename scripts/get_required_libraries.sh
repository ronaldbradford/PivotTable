#!/bin/sh

cd lib

# Get the PSR 2 sniffer
echo "Getting PSR 2 code validator"
curl -sOL https://squizlabs.github.io/PHP_CodeSniffer/phpcs.phar

# Get PHPUnit
echo "Getting PHP Unit"
curl -sOL https://phar.phpunit.de/phpunit.phar

echo "Getting Composer"
curl -sOL https://getcomposer.org/composer.phar

chmod +x *.phar

exit 0

