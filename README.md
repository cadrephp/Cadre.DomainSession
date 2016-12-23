# Cadre.Domain_Session

Library for tracking session data within the domain (no cookie handling).

For a project skeleton see [Cadre.Project](https://github.com/cadrephp/Cadre.Project).

## Preconfigured Libraries

- [Composer](https://getcomposer.org/) PHP dependency manager
- [Phing](https://www.phing.info/) PHP build system
- [PHPUnit](https://phpunit.de/) for testing and code coverage
- [PHP CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

## Setup

```bash
# Setup new package repo
composer create-project -s dev cadre/package example-package --repository-url=https://packages.cadrephp.com
cd example-package
git init .

# Update dependencies
vim composer.json
composer update

# Run lint, phpcs, and phpunit
vendor/bin/phing build

# Run tests and generate HTML coverage report (in build/coverage)
vendor/bin/phing coverage
```
