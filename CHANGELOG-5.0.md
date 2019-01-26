## v5.0.0

### Added

- Added `Kernel::getInstance` method
- Added `Kernel->errorHandler` method
- Added `Handler` class with namespace `Springy\Exceptions`
- Added `SpringyException` class with namespace `Springy\Exceptions`

### Changed

- Added parameters type declaration and return type declaration for several methods
- The `Configuration` class was moved to `Springy\Core` namespace
- The `Kernel` class was moved to `Springy\Core` namespace
- The `Kernel` class is now a singleton class and its methods is no more static
- The `URI` class was moved to `Springy\HTTP` namespace
- The `URI` class is now a singleton class and its methods is no more static
- Method `addIgnoredError` moved from `Kernel` to `Handler`
- Method `delIgnoredError` moved from `Kernel` to `Handler`
- Method `getIgnoredError` moved from `Kernel` to `Handler`
- Environment alias configuration `'cmd.shell'` renamed to `'cli'`

### Main configuration file

The main configuration file was renamed from syscont.php to config.php and moved from web root directory to /conf folder.