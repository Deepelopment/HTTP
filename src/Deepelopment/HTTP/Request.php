<?php
/**
 * PHP Deepelopment Framework.
 *
 * @package Deepelopment/Debeetle
 * @license Unlicense http://unlicense.org/
 */

namespace Deepelopment\HTTP;

use InvalidArgumentException;

/**
 * HTTP request class.
 *
 * @package Deepelopment\HTTP
 */
class Request
{
    /**
     * Request scopes
     *
     * @var array
     */
    protected $scope = array();

    /**
     * List of scopes sorted by priority
     *
     * @var array
     */
    protected $priority = array(INPUT_GET, INPUT_POST, INPUT_COOKIE);

    /**
     * @param array $scope  Used to override real scopes
     * @param bool  $parse  Flag specifying to parse real scopes
     */
    public function __construct(array $scope = array(), $parse = TRUE)
    {
        $this->scope = $scope + array(
            INPUT_GET     => array(),
            INPUT_POST    => array(),
            INPUT_COOKIE  => array(),
            INPUT_ENV     => array(),
            INPUT_REQUEST => array(),
        );
        if ($parse) {
            $priority = $this->priority;
            $priority[] = INPUT_ENV;
            foreach ($priority as $scope) {
                $vars = filter_input_array($scope);
                if (!is_array($vars)) {
                    $vars = array();
                }
                if (isset($this->scope[$scope])) {
                    $this->scope[$scope] += $vars;
                } else {
                    $this->scope[$scope] = $vars;
                }
                if (INPUT_ENV != $scope) {
                    $this->scope[INPUT_REQUEST] += $vars;
                }
            }
        }
    }

    /**
     * Returns request variable value.
     *
     * @param  string $name     Variable name
     * @param  mixed  $default  Default variable value
     * @param  int    $scope    Scope name
     *         (INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_REQUEST, INPUT_ENV)
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function get($name, $default = NULL, $scope = INPUT_REQUEST)
    {
        if( isset($this->scope[$scope])) {
            return
                isset($this->scope[$scope][$name])
                    ? $this->scope[$scope][$name]
                    : $default;
        }
        throw new InvalidArgumentException(
            sprintf(
                'Invalid request scope "%s"',
                $scope
            )
        );
    }

    /**
     * Sets request variable value.
     *
     * @param  string $name   Variable name
     * @param  mixed  $value  Default variable value
     * @param  int    $scope  Scope name
     *         (INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_REQUEST, INPUT_ENV)
     * @return void
     * @throws InvalidArgumentException
     */
    public function set($name, $value, $scope)
    {
        if(isset($this->scope[$scope])){
            if (is_null($value)) {
                unset($this->scope[$scope][$name]);
            }else {
                $this->scope[$scope][$name] = $value;
            }
            return;
        }
        throw new InvalidArgumentException(
            sprintf(
                'Invalid request scope "%s"',
                $scope
            )
        );
    }

    /**
     * Returns request scope.
     *
     * @param  int $scope  Scope name
     *         (INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_REQUEST, INPUT_ENV)
     * @return array
     * @throws InvalidArgumentException
     */
    public function getScope($scope = INPUT_REQUEST){
        if(isset($this->scope[$scope])){
            return $this->scope[$scope];
        }
        throw new InvalidArgumentException(
            sprintf(
                'Invalid request scope "%s"',
                $scope
            )
        );
    }

    /**
     * Sets request scope.
     *
     * @param  int $scope   Scope name
     *         (INPUT_POST, INPUT_GET, INPUT_COOKIE, INPUT_REQUEST, INPUT_ENV)
     * @param  array $vars  Variables
     * @return void
     * @throws InvalidArgumentException
     */
    public function setScope($scope = INPUT_REQUEST, array $vars){
        if(isset($this->scope[$scope])){
            $this->scope[$scope] = $vars;
            return;
        }
        throw new InvalidArgumentException(
            sprintf(
                'Invalid request scope "%s"',
                $scope
            )
        );
    }

    /**
     * Returns request method or "CLI" if script is running in CLI mode.
     *
     * @return string
     */
    public function getMethod()
    {
        return filter_input(INPUT_SERVER, 'REQUEST_METHOD');
    }

    /**
     * Returns all HTTP headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return apache_request_headers();
    }
}
