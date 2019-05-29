default: help

help:
	@echo "test - run tests"

test:
	php vendor/bin/phpcs --standard=PSR2 --ignore=vendor/ --extensions=php . -n -p
	curl -LO https://raw.github.com/jrburke/r.js/2.1.8/dist/r.js
	php vendor/bin/phpunit --coverage-text
