# Change Log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [0.1.7][] - 2019-05-30
#### Changed
-   Refactored Loader class

#### Added
-   Added `isConnected()`, `closeConnection()`, `getConnection()`, `getCredentials()`, and `bind()` methods to Loader

## [0.1.6][] - 2018-11-30
#### Added
-   Added `doInTransaction()` method to Database class

#### Changed
-   Database `update`, `delete`, `truncate`, `insert`, and `insertUpdate` are all using `doInTransaction()` now
-   Made `findParamType` and `bindMultiple` static
-   Renamed `__findType()` to `findParamType()`
-   Updated Loader to allow passing database credentials in to the `instance()` method

## [0.1.5][] - 2018-11-19
#### Changed
-   Code clean up with PHP CS Fixer
-   Allowing flags to be passed in to the `ResultIterator` class constructor
-   Updated Loader to correctly pass the PDO flags in when establishing a connection.
-   Updated the `insert()` and `update()` methods to allow passing a custom SQL string to use when building the final query.

#### Added
-   Added `SymphonyPDO` class which introduces transaction convenience methods. Using it instead of PDO when creating database connection.
-   Added `InsertUpdate()` method.

## [0.1.4][] - 2016-02-11
#### Changed
-   Removed unnecessary MySQL constant

## [0.1.3][] - 2015-08-20
#### Fixed
-   Formatting fixes.

#### Changed
-   Updated each() method. Instead of using `iterator_apply()`, it will use a while loop. Ensures every item is correctly iterated over.

## [0.1.2][] - 2015-08-20
#### Fixed
-   Fixed small typos

## [0.1.1][] - 2015-08-20
#### Fixed
-   Fixed classmap path

## 0.1.0 - 2015-08-20
#### Added
-   Initial release

[0.1.7]: https://github.com/pointybeard/symphony-pdo/compare/0.1.6...0.1.7
[0.1.6]: https://github.com/pointybeard/symphony-pdo/compare/0.1.5...0.1.6
[0.1.5]: https://github.com/pointybeard/symphony-pdo/compare/0.1.4...0.1.5
[0.1.4]: https://github.com/pointybeard/symphony-pdo/compare/0.1.3...0.1.4
[0.1.3]: https://github.com/pointybeard/symphony-pdo/compare/0.1.2...0.1.3
[0.1.2]: https://github.com/pointybeard/symphony-pdo/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/pointybeard/symphony-pdo/compare/0.1.0...0.1.1
