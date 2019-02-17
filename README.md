## Doctrine Persistent Entity Manager Middleware for PHP 7.1+ based on PSR-15

[![Build Status](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/badges/build.png?b=master)](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/autorusltd/doctrine-persistent-entity-manager-middleware/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/arus/doctrine-persistent-entity-manager-middleware/v/stable)](https://packagist.org/packages/arus/doctrine-persistent-entity-manager-middleware)
[![Total Downloads](https://poser.pugx.org/arus/doctrine-persistent-entity-manager-middleware/downloads)](https://packagist.org/packages/arus/doctrine-persistent-entity-manager-middleware)
[![License](https://poser.pugx.org/arus/doctrine-persistent-entity-manager-middleware/license)](https://packagist.org/packages/arus/doctrine-persistent-entity-manager-middleware)

## Installation (via composer)

```bash
composer require arus/doctrine-persistent-entity-manager-middleware
```

## How to use?

```php
use Arus\Middleware\DoctrinePersistentEntityManagerMiddleware;

$requestHandler->add(new DoctrinePersistentEntityManagerMiddleware($container));

$requestHandler->handle($request);
```

## Test run

```bash
composer test
```

## Useful links

* http://php-di.org/
* https://www.doctrine-project.org/
* https://www.php-fig.org/psr/psr-15/
