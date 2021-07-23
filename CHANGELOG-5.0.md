# Springy Framework Change Log

## v5.0.0

### To do

-   Implement parse error message for migration in YAML formats
-   Count method for models
-   Automatic URL redirections
-   RESTful controller
-   Data validation on `RowsIterator->set()` method

#### Test cases to do

-   Springy\Core\DebugFormat\*
-   Springy\Database\RowsIteratPodor->getWritableColumns()
-   Springy\Exceptions\Handler->setLogDir()
-   Springy\Exceptions\Handler->setUnreportable()
-   Springy\Exceptions\Handler->addWebmaster()
-   Springy\Exceptions\Handler->setWebmaster()
-   Springy\HTTP\Request->getBody()
-   Springy\HTTP\Request->getHeaders()
-   Springy\HTTP\Request->getJsonError()
-   Springy\HTTP\Request->getJsonErrorMsg()
-   Springy\HTTP\Routing
-   Springy\Utils\NetworkUtils
-   Helper functions
-   All console classes

### Added

-   Added `Springy\Console\Controller` class
-   Added `Springy\Console\Kernel` class
-   Added `Springy\Core\Configuration->configHost()` method
-   Added `Springy\Core\Configuration->setEnvironment()` method
-   Added `Springy\Core\Configuration->getPath()` method
-   Added `Springy\Core\Configuration->setPath()` method
-   Added `Springy\Core\ControllerInterface` interface
-   Added `Springy\Core\Debug::getInstance()` method
-   Added `Springy\Core\Debug->getSimpleData()` method
-   Added `Springy\Core\Kernel::getInstance()` method
-   Added `Springy\Core\Kernel->errorHandler()` method
-   Added `Springy\Core\Kernel->httpRequest()` method
-   Added `Springy\Core\Kernel->httpResponse()` method
-   Added `Springy\Core\Kernel->setUp()` method
-   Added `Springy\Database\Connection->delete()` method
-   Added `Springy\Database\Connection->fetchCurrent()` method
-   Added `Springy\Database\Connection->insert()` method
-   Added `Springy\Database\Connection->run()` method
-   Added `Springy\Database\Connection->select()` method
-   Added `Springy\Database\Connection->update()` method
-   Added `Springy\Database\Model->addJoin()` method
-   Added `Springy\Database\Model->clearGroupBy()` method
-   Added `Springy\Database\Model->setFetchAsObject()` method
-   Added `Springy\Database\Model->setGroupBy()` method
-   Added `Springy\Database\Model->setHaving()` method
-   Added `Springy\Database\Query\CommandBase` class
-   Added `Springy\Database\Query\Condition` class
-   Added `Springy\Database\Query\Conditions->addSubConditions` class
-   Added `Springy\Database\Query\Insert` class
-   Added `Springy\Database\Query\Join` class
-   Added `Springy\Database\Query\OperatorComparationInterface` interface
-   Added `Springy\Database\Query\OperatorGroupInterface` interface
-   Added `Springy\Database\Query\Select` class
-   Added `Springy\Database\Query\Update` class
-   Added `Springy\Database\Query\Value` class
-   Added `Springy\Database\RowsIterator` class
-   Added `Springy\Exceptions\Handler` class
-   Added `Springy\Exceptions\HttpError` class
-   Added `Springy\Exceptions\HttpErrorBadRequest` class
-   Added `Springy\Exceptions\HttpErrorConflict` class
-   Added `Springy\Exceptions\HttpErrorForbidden` class
-   Added `Springy\Exceptions\HttpErrorGone` class
-   Added `Springy\Exceptions\HttpErrorImaTeapot` class
-   Added `Springy\Exceptions\HttpErrorLengthRequired` class
-   Added `Springy\Exceptions\HttpErrorMethodNotAllowed` class
-   Added `Springy\Exceptions\HttpErrorNotAcceptable` class
-   Added `Springy\Exceptions\HttpErrorNotFound` class
-   Added `Springy\Exceptions\HttpErrorPreconditionFailed` class
-   Added `Springy\Exceptions\HttpErrorPreconditionRequired` class
-   Added `Springy\Exceptions\HttpErrorServiceUnavailable` class
-   Added `Springy\Exceptions\HttpErrorTooManyRequests` class
-   Added `Springy\Exceptions\HttpErrorUnauthorized` class
-   Added `Springy\Exceptions\HttpErrorUnsupportedMediaType` class
-   Added `Springy\Exceptions\SpringyException` class
-   Added `Springy\HTTP\Controller` class
-   Added `Springy\HTTP\Cookie::getInstance()` method
-   Added `Springy\HTTP\Kernel` class
-   Added `Springy\HTTP\Request` class
-   Added `Springy\HTTP\Response` class
-   Added `Springy\HTTP\Session::getInstance()` method
-   Added `Springy\HTTP\Session->configure()` method
-   Added `Springy\HTTP\URI::getInstance()` method
-   Added `Springy\Mail\Mailer->setAlternativeBody()` method
-   Added `Springy\Mail\Mailer->setBody()` method
-   Added `Springy\Template\Drivers\Mustache` class
-   Added `Springy\Template\Template->addFunction()` method
-   Added `Springy\Template\Template->setAutoEscape()` method
-   Added `Springy\Template\Template->setDebug()` method
-   Added `Springy\Template\Template->setEscapeHtml()` method
-   Added `Springy\Template\Template->setOptimizations()` method
-   Added `Springy\Template\Template->setStrict()` method
-   Added `Springy\Template\Template->setUseSubDirs()` method
-   Added `Springy\Utils\JSON->merge()` method
-   Added `Springy\Utils\JSON->setData()` method
-   Added `Springy\Utils\FileSystemUtils` trait
-   Added `Springy\Utils\NetworkUtils` trait
-   Added `Springy\Utils\StringUtils` trait
-   Added `Springy\Validation\Rule` class
-   Added configuration file `dbms.php` (see bellow)
-   Added configuration entry `'application.authentication'` (see bellow)
-   Added configuration entry `'database.default'`
-   Added configuration entry `'database.model_structures'`
-   Added configuration entry `'template.auto_escape'`
-   Added configuration entry `'template.file_sufix'`
-   Added property `$dbIdentity` into `Springy\Database\Model` class
-   Added property `$defaultLimit` into `Springy\Database\Model` class
-   Added property `$errorIfColNotExists` into `Springy\Database\Model` class
-   Added property `$fetchAsObject` into `Springy\Database\Model` class
-   Added `app_name()` helper function
-   Added `app_version()` helper function
-   Added `env()` helper function

### Changed

-   Added parameters type declaration and return type declaration for several methods
-   Due to deprecation of `Springy\Controller` class the web controllers must extends new class `Springy\HTTP\Controller`
-   Environment alias configuration `'cmd.shell'` renamed to `'cli'`
-   The `Springy\Configuration` class was moved to `Springy\Core` namespace
-   The `Springy\Controller` class was moved to `Springy\HTTP\WebController` and does not extends `Springy\Security\AclManager` anymore
-   The `Springy\Cookie` class was moved to `Springy\HTTP` namespace
-   The `Springy\Core\Input` class was moved to `Springy\HTTP` namespace
-   The `Springy\Core\Configuration` class is no more static
-   The `Springy\Core\Debug` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\Core\Kernel` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\DB` class was moved and renamed to `Springy\Database\Connection`
-   The `Springy\DB\Conditions` class was moved and renamed to `Springy\Database\Query\Conditions`
-   The `Springy\Files\File` class was moved to `Springy\Utils` namespace
-   The `Springy\Files\UploadedFile` class was moved to `Springy\HTTP` namespace
-   The `Springy\Mail` class was moved and renamed to `Springy\Mail\Mailer`
-   The `Springy\HTTP\Cookie` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\Session` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no more static and has a `getInstance` static method to get its instance object
-   The `Springy\HTTP\URI` class is no longer responsible for determining the controller. This becomes the responsibility of the `Kernel` class.
-   The `Springy\Kernel` class was moved to `Springy\Core` namespace
-   The `Springy\Model` class was moved to `Springy\Database\Model` namespace
-   The `Springy\Security\DBAuthDriver` class was renamed to `Springy\Security\AuthDrive`
-   The `Springy\Session` class was moved to `Springy\HTTP` namespace
-   The `Springy\Template` class was moved to `Springy\Template` namespace
-   The `Springy\URI` class was moved to `Springy\HTTP` namespace
-   Method `Springy\Core\Copyright->printCopyright()` renamed to `Springy\Core\Copyright->content()`
-   Method `Springy\Core\Debug::print_rc()` is no more static and was renamed to `Springy\Core\Copyright->highligh()`
-   Method `Springy\Core\Debug::printOut()` is no more static and was renamed to `Springy\Core\Copyright->inject()`
-   Method `Springy\Database\RowsIterator->end()` only moves the cursor pointer and does not returns value any more
-   Method `Springy\Database\RowsIterator->next()` only moves the cursor pointer and does not returns value any more
-   Method `Springy\Database\RowsIterator->prev()` only moves the cursor pointer and does not returns value any more
-   Method `Springy\DB::connected()` is no more static and was renamed to `Springy\Database\Connection->isConnected()`
-   Method `Springy\DB->errorInfo()` was renamed to `Springy\Database\Connection->getError()`
-   Method `Springy\DB->fetchAll()` was renamed to `Springy\Database\Connection->getAll()`
-   Method `Springy\DB->fetchCurrent()` was renamed to `Springy\Database\Connection->getCurrent()`
-   Method `Springy\DB->fetchFirst()` was renamed to `Springy\Database\Connection->getFirst()`
-   Method `Springy\DB->fetchLast()` was renamed to `Springy\Database\Connection->getLast()`
-   Method `Springy\DB->fetchNext()` was renamed to `Springy\Database\Connection->fetch()`
-   Method `Springy\DB->fetchPrev()` was renamed to `Springy\Database\Connection->getPrev()`
-   Method `Springy\DB->driverName()` was renamed to `Springy\Database\Connection->getDriverName()`
-   Method `Springy\DB->lastQuery()` was renamed to `Springy\Database\Connection->getLastQuery()`
-   Method `Springy\DB->lastInsertedId()` was renamed to `Springy\Database\Connection->getLastInsertedId()`
-   Method `Springy\DB->serverVersion()` was renamed to `Springy\Database\Connection->getServerVersion()`
-   Method `Springy\DB->statmentErrorInfo()` was renamed to `Springy\Database\Connection->getError()`
-   Method `Springy\DB\Conditions->condition()` was renamed to `Springy\Database\Query\Conditions->add()`
-   Method `Springy\Kernel::addIgnoredError()` moved to `Springy\Exceptions\Handler->addIgnoredError()`
-   Method `Springy\Kernel::delIgnoredError()` moved to `Springy\Exceptions\Handler->delIgnoredError()`
-   Method `Springy\Kernel::getIgnoredError()` moved to `Springy\Exceptions\Handler->getIgnoredError()`
-   Method `Springy\Kernel::projectCodeName()` was changed to read only and renamed to `Springy\Core\Kernel->getAppCodeName()`
-   Method `Springy\Kernel::systemName()` was changed to read only and renamed to `Springy\Core\Kernel->getApplicationName()`
-   Method `Springy\Kernel::systemVersion()` was changed to read only and renamed to `Springy\Core\Kernel->getApplicationVersion()`
-   Method `Springy\Mail->bcc()` renamed to `Springy\Mail\Mailer->addBcc()`
-   Method `Springy\Mail->cc()` renamed to `Springy\Mail\Mailer->addCc()`
-   Method `Springy\Mail->from()` renamed to `Springy\Mail\Mailer->setFrom()`
-   Method `Springy\Mail->setTemplate()` renamed to `Springy\Mail\Mailer->setTemplateId()`
-   Method `Springy\Mail->subject()` renamed to `Springy\Mail\Mailer->setSubject()`
-   Method `Springy\Mail->to()` renamed to `Springy\Mail\Mailer->addTo()`
-   Method `Springy\Model->all()` renamed to `Springy\Database\RowsIterator->getRows()`
-   Method `Springy\Model->calculeteColumnsRow()` renamed to `Springy\Database\RowsIterator->computeCols()`
-   Method `Springy\Model->query()` renamed to `Springy\Database\Model->select()`
-   Method `Springy\Model->rows()` renamed to `Springy\Database\RowsIterator->rowsCount()`
-   Method `Springy\Model->setEmbeddedObj()` renamed to `Springy\Database\RowsIterator->addEmbed()`
-   Method `Springy\Model->validationErrors()` renamed to `Springy\Database\RowsIterator->getValidationErrors()`
-   Method `Springy\Security\AclManager->isPermitted` renamed to `Springy\Security\AclManager->hasPermission()`
-   Method `Springy\Security\Strings::validateEmailAddress` moved and renamed to `Springy\Utils\StringUtils->isValidEmailAddress()`
-   Method `Springy\Security\AclUserInterface->getPermissionFor` renamed to `Springy\Security\AclUserInterface->hasPermissionFor()`
-   Method `Springy\Session::setSessionId()` is no more static and was renamed to `Springy\HTTP\Session->setId()`
-   Method `Springy\Session::unregister()` is no more static and was renamed to `Springy\HTTP\Session->forget()`
-   Method `Springy\Template->clearCache()` was renamed to `Springy\Template\Template->clearTemplateCache()`
-   Method `Springy\Template->clearAllCache()` was renamed to `Springy\Template\Template->clearCache()`
-   Method `Springy\Template->clearAssign()` was renamed to `Springy\Template\Template->unassign()`
-   Method `Springy\Template->templateObject()` was renamed to `Springy\Template\Template->getTemplateDriver()`
-   Method `Springy\URI::getAllSegments()` is no more static and was renamed to `Springy\HTTP\URI->getSegments()`
-   Method `Springy\Utils\JSON->add()` now accept an array to merge or two mixed data with `key` and `value` to be added to json data
-   Method `Springy\Validation\Validator::__constructor()` does not receives an array of messages anymore
-   Method `Springy\Validation\Validator->messages()` was renamed to `Springy\Validation\Validator->getErrors()`
-   Constant `Springy\Template::TPL_ENGINE_SMARTY` renamed to `Springy\Exceptions\SpringyException::DRV_SMARTY`
-   Constant `Springy\Template::TPL_ENGINE_TWIG` renamed to `Springy\Exceptions\SpringyException::DRV_TWIG`
-   Property `Springy\Database\Model::$primaryKey` must be array
-   Main configuration file `sysconf.php` moved from the web server root directory to the configuration directory and renamed to `main.php`
-   Main system configuration `SYSTEM_NAME` renamed to `main.app.name`
-   Main system configuration `SYSTEM_VERSION` renamed to `main.app.version`
-   Main system configuration `PROJECT_CODE_NAME` renamed to `main.app.code_name`
-   Main system configuration `CHARSET` renamed to `main.charset`
-   Main system configuration `ENVIRONMENT` renamed to `main.environment`
-   Configuration `'db'` moved to `'database.connections'`
-   Configuration `'mail.default_driver'` renamed to `'mail.driver'`
-   Configuration `'mail.mails_go_to'` renamed to `'mail.fake_to'`
-   Configuration `'system.debug'` renamed to `'application.debug'`
-   Configuration `'system.session.domain'` renamed to `'session.domain'`
-   Configuration `'system.session.expires'` renamed to `'session.expires'`
-   Configuration `'system.session.memcached.address'` renamed to `'session.host'`
-   Configuration `'system.session.memcached.port'` renamed to `'session.port'`
-   Configuration `'system.session.name'` renamed to `'session.name'`
-   Configuration `'system.session.type'` renamed to `'session.engine'`
-   Configuration `'template.auto_reload'` renamed to `'template.force_compile'`
-   Configuration `'template.autoescape'` renamed to `'template.auto_escape'`
-   Configuration `'template.template_cached_path'` renamed to `'template.paths.cache'`
-   Configuration `'template.compiled_template_path'` renamed to `'template.paths.compiled'`
-   Configuration `'template.default_template_path'` renamed to `'template.paths.alternative'`
-   Configuration `'template.strict_variables'` renamed to `'template.strict'`
-   Configuration `'template.template_engine'` renamed to `'template.driver'`
-   Configuration `'template.template_path'` renamed to `'template.paths.templates'`
-   Configuration `'uri.host_controller_path'` moved to `'routing.hostings'`
-   Configuration `'uri.routes'` moved to `'routing.routes'`
-   General system configuration `ACTIVE_ENVIRONMENT` moved to `main.environment`
-   General system configuration `ENVIRONMENT_ALIAS` moved to `main.environments`
-   General system configuration `PROJECT_CODE_NAME` moved to `main.app.code_name`
-   General system configuration `SYSTEM_NAME` moved to `main.app.name`
-   General system configuration `SYSTEM_VERSION` moved to `main.app.version`
-   The trigger function was removed from `Springy\Database\Model` but will be called if exists in heir class
-   The structure of array os rules for `Springy\Validation\Validator` class was changed to receive the custom error messages

### Removed

-   Removed `$action` property from `Springy\Security\AclManager`
-   Removed `$controller` property from `Springy\Security\AclManager`
-   Removed `$defaultModule` property from `Springy\Security\AclManager`
-   Removed `$modulePrefix` property from `Springy\Security\AclManager`
-   Removed `Springy\Controller->_pageNotFound()` method. Throws a `Springy\Exceptions\HttpErrorNotFound` exception to returns a '404-page not found' HTTP error.
-   Removed `Springy\Cookie::contents()` method
-   Removed `Springy\Cookie::del()` method
-   Removed `Springy\Controller->_template()` method
-   Removed `Springy\CreditCardValidation` class
-   Removed `Springy\DB::castDateBrToDb()` method
-   Removed `Springy\DB::castDateDbToBr()` method
-   Removed `Springy\DB::dateToStr()` method
-   Removed `Springy\DB::dateToTime()` method
-   Removed `Springy\DB::disableReportError()` method
-   Removed `Springy\DB::enableReportError()` method
-   Removed `Springy\DB::longBrazilianDate()` method
-   Removed `Springy\DB::makeDbDateTime()` method
-   Removed `Springy\DB::rollBackAll()` method
-   Removed `Springy\DB::transactionAllRollBack()` method
-   Removed `Springy\DB->execute()` method. See new methods `run()`, `select()`, `insert()`, `delete()` and `update()` in `Springy\Database\Connection` class.
-   Removed `Springy\DB->get_all()` method
-   Removed `Springy\DB->num_rows()` method
-   Removed `Springy\DB->errorCode()` method
-   Removed `Springy\DB->statmentErrorCode()` method
-   Removed `Springy\DB\Conditions->filter()` method
-   Removed `Springy\DBDelete` class
-   Removed `Springy\DBExpression` class
-   Removed `Springy\DBFiltro` class
-   Removed `Springy\DBInsert` class
-   Removed `Springy\DBSelect` class
-   Removed `Springy\DBUpdate` class
-   Removed `Springy\DBWhere` class
-   Removed `Springy\DeepDir` class
-   Removed `Springy\Kernel::arrayToObject()` method
-   Removed `Springy\Kernel::assignTemplateVar()` method
-   Removed `Springy\Kernel::charset()` method
-   Removed `Springy\Kernel::environment()` method
-   Removed `Springy\Kernel::getTemplateFunctions()` method
-   Removed `Springy\Kernel::getTemplateVar()` method
-   Removed `Springy\Kernel::objectToArray()` method
-   Removed `Springy\Kernel::registerTemplateFunction()` method
-   Removed `Springy\Log` class
-   Removed `Springy\Pagination` class
-   Removed `Springy\Utils\Excel` class
-   Removed `Springy\Utils\JSON_Static` class
-   Removed `Springy\Utils\Strings` class. See new trait `Springy\Utils\StringUtils` and `Springy\Utils\NetworkUtils` for substitute methods.
-   Removed `Springy\Utils\ZipFile` class
-   Removed `Springy\Mail->body()` method. See new methods `Springy\Mail\Mailer->setBody()` and `Springy\Mail\Mailer->setAlternativeBody()`
-   Removed `Springy\Mail->setHeader()` method
-   Removed `Springy\Model->calculateColumns()` method
-   Removed `Springy\Model->clearChangedColumns()` method
-   Removed `Springy\Model->groupBy()` method
-   Removed `Springy\Model->having()` method
-   Removed `Springy\Model->reset()` method
-   Removed `Springy\Model->validationErrorMessages()` method
-   Removed `Springy\Model->validationRules()` method but if exists in heir class will be called to get the array of rules
-   Removed `Springy\Session::getAll()` method
-   Removed `Springy\Security\AclManager->getCurrentAction()` method
-   Removed `Springy\Security\AclManager->getCurrentController()` method
-   Removed `Springy\Security\AclManager->getCurrentModule()` method
-   Removed `Springy\Security\AclManager->getDefaultModule()` method
-   Removed `Springy\Security\AclManager->getModulePrefix()` method
-   Removed `Springy\Security\AclManager->setModulePrefix()` method
-   Removed `Springy\Security\AclManager->setDefaultModule()` method
-   Removed `Springy\Security\AclManager->setupCurrentAclObject()` method
-   Removed `Springy\Security\BasicHasher->generateHash()` method
-   Removed `Springy\SOAP` class
-   Removed `Springy\Template->clearConfig()` method
-   Removed `Springy\Template->display()` method
-   Removed `Springy\Template->registerPlugin()` method see new method `Springy\Template\Template->addFunction()`
-   Removed `Springy\Template->setAutoTemplatePaths()` method
-   Removed `Springy\Template->setConfigDir()` method
-   Removed `Springy\URI::_GET()` method see new class `Springy\HTTP\Input`
-   Removed `Springy\URI::getParams()` method now you must uses `Springy\HTTP\Input` class to get query string values
-   Removed `Springy\URI::parseURI()` method
-   Removed `Springy\URI::validateURI()` method
-   Removed `Springy\URI::getParam()` method see new class `Springy\HTTP\Input`
-   Removed `Springy\URI::httpHost()` method see new helper funcion `current_host()`
-   Removed `Springy\URI::isAjaxRequest()` method see new class `Springy\HTTP\Request`
-   Removed `Springy\Utils\JSON->getDados()` method
-   Removed `Springy\Utils\JSON->printJ()` method
-   Removed `Springy\Validation\Validator->getDefaultErrorMessage()` method
-   Removed `Springy\Validation\Validator->getMessages()` method
-   Removed `Springy\Validation\Validator->setDefaultErrorMessage()` method
-   Removed `Springy\Validation\Validator->setMessages()` method
-   Removed `$cacheLifeTime` parameters from `Springy\Database\Connection::__construct`
-   Removed comparison constants aliases from `Springy\Database\Conditions` class
-   Removed support to configuration `'system.assets_path'`
-   Removed support to configuration `'system.assets_source_path'`
-   Removed support to configuration `'system.authentication'`
-   Removed support to configuration `'system.bug_authentication'`
-   Removed support to configuration `'system.cache-control'`
-   Removed support to configuration `'system.dba_user'`
-   Removed support to configuration `'system.developer_pass'`
-   Removed support to configuration `'system.developer_user'`
-   Removed support to configuration `'system.rewrite_url'`
-   Removed support to configuration `'system.session.database'`
-   Removed support to configuration `'system.system_error.create_table'`
-   Removed support to configuration `'system.system_error.db_server'`
-   Removed support to configuration `'system.system_error.reported_errors'`
-   Removed support to configuration `'system.system_error.save_in_database'`
-   Removed support to configuration `'system.system_error.table_name'`
-   Removed support to configuration `'template.template_config_path'`
-   Removed support to configuration `'template.debugging_ctrl'`
-   Removed support to configuration `'template.errors'`
-   Removed support to configuration `'template.escape_html'` see `'template.auto_escape'`
-   Removed support to configuration `'uri.assets_dir'`
-   Removed support to configuration `'uri.common_urls'`
-   Removed support to configuration `'uri.css_dir'`
-   Removed support to configuration `'uri.dynamic'`
-   Removed support to configuration `'uri.force_slash_on_index'`
-   Removed support to configuration `'uri.ignored_segments'`
-   Removed support to configuration `'uri.images_dir'`
-   Removed support to configuration `'uri.js_dir'`
-   Removed support to configuration `'uri.prevalidate_controller'`
-   Removed support to configuration `'uri.redirect_last_slash'`
-   Removed support to configuration `'uri.register_method_set_common_urls'`
-   Removed support to configuration `'uri.secure'`
-   Removed support to configuration `'uri.static'`
-   Removed support to configuration `'uri.swf_dir'`
-   Removed support to configuration `'system.system_error.save_in_database'`
-   Removed support to `$over_conf` variable in configuration files
-   Removed support to hook controller `_global.php`
-   Removed support to Manuel Lemos' MIME Mail Message classes. Thanks a lot!
-   Removed template variable `HOST`
-   Removed template variable `CURRENT_PAGE_URI`
-   Removed `assetFile` pre-defined template function. The application must implements by it self using `Springy\Template\Template->addFunction()` method.
-   Removed support to `sysconf.php` file inside public root folder

### Auto-routing system

The framework will try to discover the controller class even if the route configuration file is not defined. The controller name is built from URL segments.

> **Warning!** File names with hyphens are not supported in PSR-4 by Composer 2. Then use the StudlyCaps file name for hyphen separated segment router.

### Configuration files

#### The Application File Configuration: application.php

##### Entry 'application.authentication'

-   `'driver'` : the authentication driver closure.
-   `'hasher'` : the authentication hasher class name or closure.
-   `'identity'` : the authentication identity class name or closure.

#### The Database Connection Configuration: database.php

The dbms.php file in configuration directories is used by Springy\DBMS\Connection to configure itself connections to database servers.

-   `'default'` : name of the default connection.
-   `'cache'` : cache system for select queries.
-   `'connections'` : each connection configuration.

##### The `'connections'` Configuration Structure

-   `'driver'` : the DBMS server engine.
-   `'database'` : the database name.
-   `'username'` : the username.
-   `'password'` : the password.
-   `'host'` : the DBMS server host.
-   `'port'` : the DBMS server port.
-   `'socket'` : the DBMS server socket.
-   `'persistent'` : the DBMS server engine.
-   `'charset'` : the charset of the database.
-   `'timezone'` : the timezone of the database.
-   `'retries'` : connection retries in case of lost connection error.
-   `'retry_sleep'` : sleep time in seconds between connection retries in case of lost connection error.
-   `'round_robin'` : round robin connection controller for the driver.
-   `'sslmode'`, `'sslcert'`, `'sslkey'`, `'sslrootcert'` : SSL connection options for PostgreSQL driver.
-   `'schema'` :  schema configuration for PostgreSQL driver.

###### Database Supported `'connections.*.drivers'`

-   `'mysql'` : MySQL server.
-   `'sqlite'` : SQLite3 database.
-   `'pgsql'` : PostgreSQL server.

###### The `'connections.*.round_robin'` Configuration Structure

-   `'driver'` : round robin controller driver. Can be 'memcached', 'file' ou false/null to turns off.
-   `'file'` : full path of the file used to save round robin control by 'file' driver.
-   `'address'` : address of the Memcached server for 'memcached' driver.
-   `'port'` : TCP port of the Memcached server for 'memcached' driver.
-   `'key'` : round robin item key in the Memcached server for 'memcached' driver.

#### The Routing configuration: routing.php

The configuration file `routing.php` has to key pair arrays.

The 'routes' array with the structure *pattern => handling*.

And the 'namespaces' array that changes the default namespace for controllers.

##### The Pattern

The pattern is a string with two parts separated by **@** char.

The first part is the request methods and you can set as one or several separated by the **|** char.
You can also use the wildcard "*" to represent all the methods (GET, POST, PUT, DELETE, OPTIONS, PATCH and HEAD).

Example: 'GET|POST'

The second part of the pattern is the URI-Pattern and can be static or dynamic with PCRE regular expression or placeholder expression.

###### Static URI-Patterns

Example: '/user'

###### Dynamic PCRE URI-Patterns

Example: '/user/(\d+)'

###### Dynamic Placeholder URI-Patterns

Example: '/user/{id}'

##### The Handling

###### Array Handling

The array handlings must be a key pair where the key is a pattern to be appended to the parent pattern and the value as the handling.

### RowsIterator Columns Object

The RowsIterator columns object will be loaded from a JSON file informed to the constructor.

Example:

```php
$rowsIterator = new RowsIterator('/path/to/my/structure.json');
```

#### The JSON File With Columns Structure

The JSON file with the columns structure must contains keys with column names and at last one of the following properties:

-   "primaryKey": `bool` - defines the column as a primary key
-   "computed": `string` - if its value is a function name that can be called it defines the column as computed
-   "hook": `string` - defines a hook function to process the column value by defining data for it
-   "readOnly": `bool` - sets the column to read-only
-   "insertedAt": `bool` - it sets column as date-added controller
-   "softDelete": `bool` - it defines the column as a soft delete controller int(0|1)
-   "validation": `array` - an array or object validation rules and error messages

Example:

```json
{
  "id": {
    "primaryKey": true,
    "readOnly": true
  },
  "name": {
    "validation": [
      "required",
      "minlength:3"
    ]
  },
  "created_at": {
    "insertedAt": true,
    "readOnly": true
  }
}
```

Observations:

All computed columns are read only.

May only have one column with added date controller attribute. If more than one column has this attribute, only the first one is set.

You can have only one exclusion control column. So only the first column with this attribute will be considered.

### Validation Rules Array

The validation rule array used by the `Springy\Validation\Validator` class must be in a structure where the field name remains in each array index and the rule set in their value.

Each set of rules can be a sequence of rules concatenated by pipe "|" or an array of validation rules.

Each validation rule can be a string in the format "rule_name:param1,param2,paramN:custom error message" or an array with the following indexes:

-   'params' => an array of parameters or a string with parameters separated by commas ","
-   'message' => the custom message string. See messages tag section.

#### Messages tag

-   '@field' : the name of the field
-   '@rule' : the name of the rule
-   '@value' : the value of the field

### The Migration System

#### YAML Migrations Script

Can contains to sections `migration` and `rollback`.

If `rollback` sections is empty or missing, there is no rollback to the migration.

If there is no `migration` section or is empty, will be ignored.

Both sections can be a script SQL or a list of scripts.

#### SQL Migration Scripts

All SQL files will be imported on migrate only and no rollback scripts.

#### PHP Migration Scripts

Must be in namespace `App\Migrations\Rev#` where **#** is the version number and contains a method called `migrate()`.

If there is no method called `rollback()` no rollback can be applied to the migration.

Both methods `migrate()` and `rollback()` will receives a `Springy\Database\Connection` object as parameter.

### Custom Error Pages

To create custom error pages ...
