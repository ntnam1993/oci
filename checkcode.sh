#!/usr/bin/env bash

shopware-app/vendor/bin/psalm -c shopware-app/config/static-analysis/psalm.xml --no-cache shopware-app/custom/static-plugins/$1
shopware-app/vendor/bin/phpstan analyse -c shopware-app/config/static-analysis/phpstan.neon shopware-app/custom/static-plugins/$1
shopware-app/vendor/bin/phpcs -n --standard=shopware-app/config/static-analysis/ruleset.xml shopware-app/custom/static-plugins/$1 --no-cache --colors
shopware-app/vendor/bin/deptrac analyze shopware-app/config/static-analysis/depfile.yaml --no-cache shopware-app/custom/static-plugins/$1
shopware-app/vendor/bin/phpmd shopware-app/custom/static-plugins/$1 ansi shopware-app/config/static-analysis/phpmd-ruleset.xml
shopware-app/vendor/bin/phpunit -c shopware-app/phpunit.xml shopware-app/custom/static-plugins/$1