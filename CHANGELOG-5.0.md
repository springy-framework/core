## v5.0.0

### Added
-   Added `Handler` class with namespace `Springy\Exceptions`
-   Added `Request` class with namespace `Springy\HTTP`
-   Added `Response` class with namespace `Springy\HTTP`
-   Added `SpringyException` class with namespace `Springy\Exceptions`
-   Added `Springy\Core\Configuration->configHost` method
-   Added `Springy\Core\Configuration->configPath` method
-   Added `Springy\Core\Configuration->getEnvironment` method
-   Added `Springy\Core\Configuration->setEnvironment` method
-   Added `Springy\HTTP\Cookie::getInstance` method
-   Added `Springy\Core\Copyright::getInstance` method
-   Added `Springy\Core\Debug::getInstance` method
-   Added `Springy\Core\Kernel::getInstance` method
-   Added `Springy\Core\Kernel->configuration` method
-   Added `Springy\Core\Kernel->getEnvironmentType` method
-   Added `Springy\Core\Kernel->errorHandler` method
-   Added `Springy\Core\Kernel->httpRequest` method
-   Added `Springy\Core\Kernel->httpResponse` method
-   Added `Springy\Core\Kernel->setUp` method
-   Added `Springy\HTTP\Session::getInstance` method
-   Added `Springy\HTTP\Session->configure` method
-   Added `Springy\HTTP\URI::getInstance` method
-   Added constants `ENV_TYPE_CLI` and `ENV_TYPE_WEB` to `Kernel`

### Changed
-   Added parameters type declaration and return type declaration for several methods
-   Environment alias configuration `'cmd.shell'` renamed to `'cli'`
-   The `Configuration` class was moved to `Springy\Core` namespace
-   The `Controller` class was moved to `Springy\HTTP\WebController` and does not extends `Springy\Security\AclManager` anymore
-   The `Cookie` class was moved to `Springy\HTTP` namespace
-   The `File` class was moved to `Springy\Utils` namespace
-   The `Input` class was moved to `Springy\HTTP` namespace
-   The `Kernel` class was moved to `Springy\Core` namespace
-   The `Session` class was moved to `Springy\HTTP` namespace
-   The `UploadedFile` class was moved to `Springy\HTTP` namespace
-   The `URI` class was moved to `Springy\HTTP` namespace
-   The `Springy\HTTP\Cookie` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\Core\Configuration` class is no more static
-   The `Springy\Core\Debug` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\Core\Kernel` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\Session` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no longer responsible for determining the controller. This becomes the responsibility of the `Kernel` class.
-   Method `addIgnoredError` moved from `Springy\Core\Kernel` to `Springy\Exceptions\Handler`
-   Method `delIgnoredError` moved from `Springy\Core\Kernel` to `Springy\Exceptions\Handler`
-   Method `getIgnoredError` moved from `Springy\Core\Kernel` to `Springy\Exceptions\Handler`
-   Method `Springy\Core\Copyright->printCopyright` renamed to `content`
-   Method `Springy\Core\Debug::print_rc` is no more static and was renamed to `highligh`
-   Method `Springy\Core\Debug::printOut` is no more static and was renamed to `inject`
-   Method `Springy\Core\Kernel::charset` was separated in `getCharset` and `setCharset`
-   Method `Springy\Core\Kernel::environment` was separated in `getEnvironment` and `setEnvironment`
-   Method `Springy\Core\Kernel::projectCodeName` was separated in `getProjectCodeName` and `setProjectCodeName`
-   Method `Springy\Core\Kernel::systemName` was separated in `getSystemName` and `setSystemName`
-   Method `Springy\Core\Kernel::systemVersion` was separated in `getSystemVersion` and `setSystemVersion`
-   Method `Springy\HTTP\Session::setSessionId` is no more static and was renamed to `setId`
-   Method `Springy\HTTP\Session::unregister` is no more static and was renamed to `forget`
-   Method `Springy\HTTP\URI::getAllSegments` is no more static and was renamed to `getSegments`
-   Method `Springy\Security\AclManager->isPermitted` renamed to `hasPermission`
-   Method `Springy\Security\AclUserInterface->getPermissionFor` renamed to `hasPermissionFor`

### Removed
-   Removed `contents` method from `Springy\HTTP\Cookie`
-   Removed `del` method from `Springy\HTTP\Cookie`
-   Removed `generateHash` method from `Springy\Security\BasicHasher`
-   Removed `getAll` method from `Springy\HTTP\Session`
-   Removed `getParams` method from `Springy\HTTP\URI` now you must uses `Springy\HTTP\Input` class to get query string values
-   Removed `parseURI` method from `Springy\HTTP\URI`
-   Removed `setupCurrentAclObject` method from `Springy\Security\AclManager`
-   Removed `validateURI` method from `Springy\HTTP\URI`
-   Removed support to configuration `'uri.redirect_last_slash'`
-   Removed support to configuration `'uri.force_slash_on_index'`

### Main configuration file

The main configuration file was renamed from syscont.php to config.php and moved from web root directory to /conf folder.