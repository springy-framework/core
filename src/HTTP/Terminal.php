<?php

/**
 * Web console terminal controllers base.
 *
 * @copyright 2019 Fernando Val
 * @author    Fernando Val <fernando.val@gmail.com>
 * @license   https://github.com/springy-framework/core/blob/master/LICENSE MIT
 *
 * @version   1.0.0
 */

namespace Springy\HTTP;

use Springy\Core\Configuration;
use Springy\Utils\JSON;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Web console terminal controllers base class.
 */
class Terminal
{
    /** @var array the user/password credential */
    protected $credential;
    /** @var Request the request object */
    protected $request;
    /** @var int JSON-RPC request ID */
    protected $requestId;
    /** @var Response the response object */
    protected $response;

    // The authentication session id var name
    protected const TERM_SESSION_ID = 'termId';

    /**
     * Constructor.
     *
     * @param array $segments
     */
    public function __construct()
    {
        $configuration = Configuration::getInstance();
        $configuration->set('application.debug', false);
        $this->credential = $configuration->get(
            'application.authentication.terminal',
            ['springy', 'terminal']
        );

        $this->request = Request::getInstance();
        $this->response = Response::getInstance();

        if ($this->request->isGet()) {
            $this->startTerminal();

            return;
        } elseif (!$this->request->isPost() || !$this->request->isAjax()) {
            $this->response->header()->notFound();

            return;
        }

        $this->parseRpc();
    }

    /**
     * Runs the command if exists or return invalid command error.
     *
     * @param string $command
     * @param string $parameters
     *
     * @return void
     */
    protected function command(string $command, string $parameters): void
    {
        $commands = [
            'errors'   => 'Springy\Console\ErrorsCommand',
            'migrator' => 'Springy\Console\MigratorCommand',
            'help'     => 'Springy\Console\HelpCommand',
        ];

        if (!isset($commands[$command])) {
            $this->sendError(404, 'Invalid command.' . LF);

            return;
        }

        $class = $commands[$command];
        $input = new StringInput($command . ' ' . $parameters);
        $output = new BufferedOutput();
        $command = new $class([$command]);
        $command->run($input, $output);
        $this->sendResult($output->fetch());
    }

    /**
     * Returns the greetings message.
     *
     * @return string
     */
    protected function getHello(): string
    {
        $format = new OutputFormatter(true);

        return $format->format(sprintf(
            '\n<options=bold>%s v%s</> - Web Console Terminal\n\n<info>Welcome!</>\n',
            app_name(),
            app_version()
        ));
    }

    /**
     * Parses the JSON-RPC request.
     *
     * @return void
     */
    protected function parseRpc(): void
    {
        $body = $this->request->getJsonBody();
        if (is_null($body)) {
            $this->sendError(400, 'Bad request');

            return;
        }

        $this->requestId = $body->id ?? 0;
        $method = $body->method ?? '';
        $params = $body->params ?? [];
        $cred = array_shift($params);
        $sessId = Session::getInstance()->get(self::TERM_SESSION_ID, false);

        if ($method === 'system.describe') {
            $this->serviceDescription();

            return;
        } elseif ($method === 'login') {
            $this->serviceLogin();

            return;
        } elseif ($method === 'logout') {
            $this->serviceLogout();

            return;
        } elseif (!$sessId || $sessId !== $cred) {
            $this->sendError(401, 'Session terminated. Please login again.');

            return;
        }

        if (!in_array('--no-interaction', $params)) {
            $params[] = '--no-interaction';
        }
        if (!in_array('--no-ansi', $params)) {
            $params[] = ' --ansi';
        }

        $this->command($method, implode(' ', $params));
    }

    /**
     * Describes the service.
     *
     * @return void
     */
    protected function serviceDescription(): void
    {
        $json = new JSON([
            'sdversion' => '2.0',
            'name'      => app_name(),
            'address'   => current_url(),
            'id'        => 'urn:md5:' . md5(current_url()),
            'procs'     => [
                [
                    'name'   => 'errors',
                    'help'   => 'Show application errors.',
                    // 'params' => ['command', 'parameter'],
                ],
                [
                    'name'   => 'migrator',
                    'help'   => 'Database migrator.',
                    // 'params' => ['command', 'parameter'],
                ],
                [
                    'name'   => 'logout',
                    'help'   => 'Ends terminal session.',
                    // 'params' => ['command', 'parameter'],
                ],
            ],
        ]);
        $json->send();
    }

    /**
     * Sends a JSON-RPC error.
     *
     * @param int    $code
     * @param string $message
     *
     * @return void
     */
    protected function sendError(int $code, string $message)
    {
        $this->sendResult(null, [
            'code'    => $code,
            'message' => $message,
            'data'    => [
                'name' => 'JSONRPCError',
            ],
        ]);
    }

    /**
     * Sends a JSON-RPC result.
     *
     * @param string $result
     * @param array  $error
     *
     * @return void
     */
    protected function sendResult(string $result = null, array $error = null)
    {
        $json = new JSON([
            'jsonrpc' => '2.0',
            'result'  => $result,
            'id'      => $this->requestId,
            'error'   => $error,
        ]);
        // ], $error['code'] ?? 200);
        $json->send();
    }

    /**
     * Kills the login session.
     *
     * @return void
     */
    protected function serviceLogout()
    {
        Session::getInstance()->forget(self::TERM_SESSION_ID);

        $this->sendResult('');
    }

    /**
     * Performs the login in terminal.
     *
     * @return void
     */
    protected function serviceLogin()
    {
        $body = $this->request->getJsonBody();
        $params = $body->params ?? [];

        if (count($params) != 2 || $params[0] !== $this->credential[0] || $params[1] !== $this->credential[1]) {
            $this->sendError(403, 'Invalid user or password.');

            return;
        }

        $sessionId = md5($params[0] . ':' . $params[1] . ':' . microtime());

        Session::getInstance()->set(self::TERM_SESSION_ID, $sessionId);

        $this->sendResult($sessionId);
    }

    /**
     * Starts the terinal view.
     *
     * @return void
     */
    protected function startTerminal()
    {
        $body = file_get_contents(__DIR__ . DS . 'assets' . DS . 'terminal.html');
        $this->response->body(
            str_replace('###GREATINGS###', $this->getHello(), $body)
        );
    }
}
