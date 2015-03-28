#!/bin/bash


for FILE in `ls {src,tests}/*.php`
do
  echo "Validating PHP ${FILE}..."
  php lib/phpcs.phar --standard=PSR2 --report-width=120 ${FILE}
done
