PHPCS := ./vendor/squizlabs/php_codesniffer/scripts/phpcs
PHPCFB := ./vendor/squizlabs/php_codesniffer/scripts/phpcbf
PHPUNIT := ./vendor/phpunit/phpunit/phpunit
PHPDOC := ./vendor/phpdocumentor/phpdocumentor/bin/phpdoc
ATHL := ./vendor/athletic/athletic/bin/athletic
PHP_STANDARD ?= PSR2

.PHONY: tests
tests:
	$(PHPCS) --standard=$(PHP_STANDARD) src tests benchmarks
	$(PHPUNIT) -v --bootstrap vendor/autoload.php tests

.PHONY: bench
bench:
	$(ATHL) --bootstrap vendor/autoload.php -p benchmarks

.PHONY: autofix
autofix:
	$(PHPCFB) --standard=$(PHP_STANDARD) src tests benchmarks

docs:
	$(PHPDOC) -d src -t docs
