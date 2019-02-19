## v5.0.0

### Added
-   Added `Springy\Core\Configuration->configHost` method
-   Added `Springy\Core\Configuration->configPath` method
-   Added `Springy\Core\Configuration->getEnvironment` method
-   Added `Springy\Core\Configuration->setEnvironment` method
-   Added `Springy\Core\ControllerInterface` interface
-   Added `Springy\Core\Copyright::getInstance` method
-   Added `Springy\Core\Debug::getInstance` method
-   Added `Springy\Core\Kernel::getInstance` method
-   Added `Springy\Core\Kernel->configuration` method
-   Added `Springy\Core\Kernel->getEnvironmentType` method
-   Added `Springy\Core\Kernel->errorHandler` method
-   Added `Springy\Core\Kernel->httpRequest` method
-   Added `Springy\Core\Kernel->httpResponse` method
-   Added `Springy\Core\Kernel->setUp` method
-   Added `Springy\Exceptions\Handler` class
-   Added `Springy\Exceptions\Http403Error` class
-   Added `Springy\Exceptions\Http404Error` class
-   Added `Springy\Exceptions\HttpError` class
-   Added `Springy\Exceptions\SpringyException` class
-   Added `Springy\HTTP\Cookie::getInstance` method
-   Added `Springy\HTTP\Request` class
-   Added `Springy\HTTP\Response` class
-   Added `Springy\HTTP\Session::getInstance` method
-   Added `Springy\HTTP\Session->configure` method
-   Added `Springy\HTTP\WebController` class
-   Added `Springy\HTTP\URI::getInstance` method
-   Added `Springy\Utils\JSON->merge` method
-   Added `Springy\Utils\JSON->setData` method
-   Added `Springy\Utils\StringUtils` trait
-   Added constants `Springy\Core\Kernel::ENV_TYPE_CLI` and `Springy\Core\Kernel::ENV_TYPE_WEB`

### Changed
-   Added parameters type declaration and return type declaration for several methods
-   Due to deprecation of `Springy\Controller` class the web controllers must extends new class `Springy\Core\WebController`
-   Environment alias configuration `'cmd.shell'` renamed to `'cli'`
-   The `Springy\Configuration` class was moved to `Springy\Core` namespace
-   The `Springy\Controller` class was moved to `Springy\HTTP\WebController` and does not extends `Springy\Security\AclManager` anymore
-   The `Springy\Cookie` class was moved to `Springy\HTTP` namespace
-   The `Springy\Core\Input` class was moved to `Springy\HTTP` namespace
-   The `Springy\Core\Configuration` class is no more static
-   The `Springy\Core\Debug` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\Core\Kernel` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\Files\File` class was moved to `Springy\Utils` namespace
-   The `Springy\Files\UploadedFile` class was moved to `Springy\HTTP` namespace
-   The `Springy\HTTP\Cookie` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\Session` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no longer responsible for determining the controller. This becomes the responsibility of the `Kernel` class.
-   The `Springy\Kernel` class was moved to `Springy\Core` namespace
-   The `Springy\Session` class was moved to `Springy\HTTP` namespace
-   The `Springy\URI` class was moved to `Springy\HTTP` namespace
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
-   Method `Springy\Security\Strings::validateEmailAddress` moved and renamed to `Springy\Utils\StringUtils->isValidEmailAddress`
-   Method `Springy\Security\AclUserInterface->getPermissionFor` renamed to `hasPermissionFor`
-   Method `Springy\Utils\JSON->add` now accept an array to merge or two mixed data with `key` and `value` to be added to json data

### Removed
-   Removed `$action` property from `Springy\Security\AclManager`
-   Removed `$controller` property from `Springy\Security\AclManager`
-   Removed `$defaultModule` property from `Springy\Security\AclManager`
-   Removed `$modulePrefix` property from `Springy\Security\AclManager`
-   Removed `Springy\Controller` class
-   Removed `Springy\Utils\Excel` class
-   Removed `Springy\Utils\JSON_Static` class
-   Removed `Springy\Utils\Strings` class. See new trait `Springy\Utils\StringUtils` for substitute methods.
-   Removed `Springy\Utils\ZipFile` class
-   Removed `contents` method from `Springy\HTTP\Cookie`
-   Removed `del` method from `Springy\HTTP\Cookie`
-   Removed `generateHash` method from `Springy\Security\BasicHasher`
-   Removed `getAll` method from `Springy\HTTP\Session`
-   Removed `getCurrentAction` method from `Springy\Security\AclManager`
-   Removed `getCurrentController` method from `Springy\Security\AclManager`
-   Removed `getCurrentModule` method from `Springy\Security\AclManager`
-   Removed `getDados` method from `Springy\Utils\JSON`
-   Removed `getDefaultModule` method from `Springy\Security\AclManager`
-   Removed `getModulePrefix` method from `Springy\Security\AclManager`
-   Removed `getParams` method from `Springy\HTTP\URI` now you must uses `Springy\HTTP\Input` class to get query string values
-   Removed `parseURI` method from `Springy\HTTP\URI`
-   Removed `printJ` method from `Springy\Utils\JSON`
-   Removed `setDefaultModule` method from `Springy\Security\AclManager`
-   Removed `setModulePrefix` method from `Springy\Security\AclManager`
-   Removed `setupCurrentAclObject` method from `Springy\Security\AclManager`
-   Removed `validateURI` method from `Springy\HTTP\URI`
-   Removed support to configuration `'uri.redirect_last_slash'`
-   Removed support to configuration `'uri.force_slash_on_index'`

### Main configuration file

The main configuration file was renamed from syscont.php to config.php and moved from web root directory to /conf folder.