# SymphonyCMS PDO Connector

- Version: v0.1.6
- Date: Nov 30 2018
- [Release notes](https://github.com/pointybeard/symphony-pdo/blob/master/CHANGELOG.md)
- [GitHub repository](https://github.com/pointybeard/symphony-pdo)

[![Latest Stable Version](https://poser.pugx.org/pointybeard/symphony-pdo/version)](https://packagist.org/packages/pointybeard/symphony-pdo) [![License](https://poser.pugx.org/pointybeard/symphony-pdo/license)](https://packagist.org/packages/pointybeard/symphony-pdo)

Provides PDO based connection to the Symphony database.

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/symphony-pdo` or add `"pointybeard/symphony-pdo": "~1.0"` to your `composer.json` file.

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

// Or use the ResultIterator class instead
new SymphonyPDO\Lib\ResultIterator(
	"\\A\\Properties\\Class",
	$query
);

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/symphony-pdo/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/symphony-pdo/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"SymphonyCMS PDO Connector" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
