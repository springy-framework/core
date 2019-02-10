## v5.0.0

### Added
-   Added `Handler` class with namespace `Springy\Exceptions`
-   Added `Request` class with namespace `Springy\HTTP`
-   Added `SpringyException` class with namespace `Springy\Exceptions`
-   Added `Configuration->configHost` method
-   Added `Configuration->configPath` method
-   Added `Configuration->environment` method
-   Added `Cookie::getInstance` method
-   Added `Debug::getInstance` method
-   Added `Kernel::getInstance` method
-   Added `Kernel->configuration` method
-   Added `Kernel->environmentType` method
-   Added `Kernel->errorHandler` method
-   Added `Kernel->httpRequest` method
-   Added `Kernel->httpResponse` method
-   Added `Kernel->setUp` method
-   Added `Session::getInstance` method
-   Added `Session->configure` method
-   Added constants `ENV_TYPE_CLI` and `ENV_TYPE_WEB` to `Kernel`

### Changed
-   Added parameters type declaration and return type declaration for several methods
-   The `Cookie` class was moved to `Springy\HTTP` namespace
-   The `Cookie` class is now a singleton class and its methods is no more static
-   The `Configuration` class was moved to `Springy\Core` namespace
-   The `Configuration` class is no more static
-   The `Debug` class is now a singleton class and its methods is no more static
-   The `File` class was moved to `Springy\Utils` namespace
-   The `Kernel` class was moved to `Springy\Core` namespace
-   The `Kernel` class is now a singleton class and its methods is no more static
-   The `Input` class was moved to `Springy\HTTP` namespace
-   The `Session` class was moved to `Springy\HTTP` namespace
-   The `Session` class is now a singleton class and its methods is no more static
-   The `UploadedFile` class was moved to `Springy\HTTP` namespace
-   The `URI` class was moved to `Springy\HTTP` namespace
-   The `URI` class is now a singleton class and its methods is no more static
-   Method `addIgnoredError` moved from `Kernel` to `Handler`
-   Method `delIgnoredError` moved from `Kernel` to `Handler`
-   Method `getIgnoredError` moved from `Kernel` to `Handler`
-   Method `Debug::print_rc` renamed to `Debug->highligh`
-   Method `Debug::printOut` renamed to `Debug->inject`
-   Method `Session::setSessionId` renamed to `Session->setId`
-   Method `Session::unregister` renamed to `Session->unset`
-   Method `URI::getAllSegments` renamed to `URI->getSegments`
-   Environment alias configuration `'cmd.shell'` renamed to `'cli'`
-   The `URI` class is no longer responsible for determining the controller. This becomes the responsibility of the `Kernel` class.

### Removed
-   Removed `contents` method from `Cookie`
-   Removed `del` method from `Cookie`
-   Removed `getAll` method from `Session`
-   Removed `getParams` method from `URI` now you must uses `Input` class to get query string values
-   Removed `parseURI` method from `URI`
-   Removed `validateURI` method from `URI`
-   Removed support to configuration `'uri.redirect_last_slash'`
-   Removed support to configuration `'uri.force_slash_on_index'`

### Main configuration file

The main configuration file was renamed from syscont.php to config.php and moved from web root directory to /conf folder.