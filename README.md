# SymphonyCMS: PDO Database Wrapper

- Version: v0.1.7
- Date: May 30 2019
- [Release notes](https://github.com/pointybeard/symphony-pdo/blob/master/CHANGELOG.md)
- [GitHub repository](https://github.com/pointybeard/symphony-pdo)

Wraps the core [Symphony CMS](http://www.getsymphony.com/) database connection with a PDO based library

## Installation

This library is installed via [Composer](http://getcomposer.org/). To install, use `composer require pointybeard/symphony-pdo` or add `"pointybeard/symphony-pdo": "~0.1"` to your `composer.json` file.

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Usage

```php
<?php
use SymphonyPDO;

$query = SymphonyPDO\Loader::instance()->query(
    'SELECT * FROM `tbl_sections` ORDER BY `id` ASC;'
);

var_dump($query->fetchObject()->name);
// string(8) "Articles"

// Or, better yet, use a ResultIterator instead
foreach(new SymphonyPDO\Lib\ResultIterator('\stdClass', $query) as $result) {
    printf('%d => %s (%s)' . PHP_EOL, $result->id, $result->name, $result->handle);
}
// 1 => Articles (articles)
// 2 => Categorties (categories)

```

## Support

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/pointybeard/symphony-pdo/issues),
or better yet, fork the library and submit a pull request.

## Contributing

We encourage you to contribute to this project. Please check out the [Contributing documentation](https://github.com/pointybeard/symphony-pdo/blob/master/CONTRIBUTING.md) for guidelines about how to get involved.

## License

"SymphonyCMS: PDO Database Wrapper" is released under the [MIT License](http://www.opensource.org/licenses/MIT).
