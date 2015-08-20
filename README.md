Symphony PDO
===========

Provides PDO based connection to the Symphony database.

## Features

 * Provides a PDO based wrapper around the Symphony core database connection
 * Cleaner, more efficent, driver for interacting with the Symphony database

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "pointybeard/symphony-pdo": "~0.1"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update


## Usage

```php
<?php
use pointybeard\SymphonyPDO;

$db = SymphonyPDO\Loader::instance();

$query = $db->prepare(sprintf(
    'SELECT e.entry_id FROM `tbl_entries_data_%d` AS `e` WHERE e.value = :value LIMIT 1',
    'some value'
));
$query->bindParam(':value', $b, PDO::PARAM_STR);
$query->execute();
$result = $query->fetch();


// Or use the PDOResultIterator class instead
new SymphonyPDO\Lib\PDOResultIterator(
	"\\A\\Properties\\Class",
	$query
);

```


## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/symphony-pdo/issues),
or better yet, fork the library and submit a pull request.