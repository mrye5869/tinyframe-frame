<?php
// +----------------------------------------------------------------------
// | zibi [ WE CAN DO IT MORE SIMPLE]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2020 http://xmzibi.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: MrYe    <email：55585190@qq.com>
// +----------------------------------------------------------------------
namespace og\http;

use Closure;
use Exception;
use InvalidArgumentException;
use og\cache\Cache;
use og\cookie\Cookie;
use og\error\Error;
use og\session\Session;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Class Container
 * @package og\http
 */
class Container
{
    /**
     * 当前容器对象
     * @var Container
     */
    protected static $instance = null;

    /**
     * 绑定结果集
     * @var array
     */
    protected $binds = [
        'app'       => App::class,
        'env'       => Env::class,
        'config'    => Config::class,
        'cookie'    => Cookie::class,
        'session'   => Session::class,
        'cache'     => Cache::class,
        'request'   => Request::class,
        'log'       => Log::class,
        'error'     => Error::class,
        'route'     => Route::class,
        'event'     => Event::class,
        'aop'       => Aop::class,
        'middleware'=> Middleware::class,
        'view'      => View::class,
    ];

    /**
     * 对象列表
     * @var array
     */
    protected $instances = [];

    /**
     * 获取当前容器对象
     * @return Container|null
     */
    public static function getInstance()
    {
        if(self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * 设置实例
     *
     * @param $instance
     * @return Container
     */
    public static function setInstance($instance)
    {
        self::$instance = $instance;

        return self::$instance;
    }

    /**
     * 绑定一个类实例到容器
     * @access public
     * @param string $abstract 类名或者标识
     * @param object $instance 类的实例
     * @return $this
     */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;

        return $this;
    }

    /**
     * 获取实例
     *
     * @param string $name
     * @param array $args
     * @param bool $newInstance
     * @return mixed
     * @throws Exception
     */
    public static function get($name = '', $args = [], $newInstance = false)
    {
        return self::getInstance()->make($name, $args, $newInstance);
    }

    /**
     * 绑定实例
     *
     * @param $abstract
     * @param $concrete
     * @return Container
     */
    public static function set($abstract, $concrete)
    {
        return static::getInstance()->bind($abstract, $concrete);
    }

    /**
     * 绑定
     *
     * @param $abstract
     * @param null $concrete
     * @return $this
     */
    public function bind($abstract, $concrete = null)
    {
        if ($concrete instanceof Closure) {
            $this->binds[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            if (isset($this->binds[$abstract])) {
                $abstract = $this->binds[$abstract];
            }
            $this->instances[$abstract] = $concrete;
        } else {
            $abstract = $this->getAlias($abstract);

            $this->binds[$abstract] = $concrete;

        }

        return $this;
    }

    /**
     * 根据别名获取真实类名
     * @param  string $abstract
     * @return string
     */
    public function getAlias($abstract)
    {
        if (isset($this->binds[$abstract])) {
            $bind = $this->binds[$abstract];

            if (is_string($bind)) {
                return $this->getAlias($bind);
            }
        }

        return $abstract;
    }

    /**
     * 创建对象
     * @param $abstract
     * @param array $vars
     * @param bool $newInstance
     * @return mixed
     * @throws Exception
     */
    public function make($abstract, $vars = [] , $newInstance = false)
    {

        $abstract = $this->getAlias($abstract);

        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        if (isset($this->binds[$abstract]) && $this->binds[$abstract] instanceof Closure) {

            $concrete = $this->binds[$abstract];
            //闭包返回对象时
            $object = $this->invokeFunction($concrete, $vars);

        } else {
            //初始化类，并且未进行绑定
            $object = $this->invokeClass($abstract, $vars);
        }

        if (!$newInstance) {
            //获取别名
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * 删除容器中的对象实例
     * @access public
     * @param string $name 类名或者标识
     * @return void
     */
    public function delete($name)
    {
        $name = $this->getAlias($name);

        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }

    /**
     * 判断容器中是否存在类及标识
     * @access public
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->binds[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * 判断容器中是否存在类及标识
     * @access public
     * @param string $name 类名或者标识
     * @return bool
     */
    public function has($name)
    {
        return $this->bound($name);
    }

    /**
     * 判断容器中是否存在对象实例
     * @access public
     * @param string $abstract 类名或者标识
     * @return bool
     */
    public function exists($abstract)
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->instances[$abstract]);
    }

    /**
     * 调用反射执行callable 支持参数绑定
     * @access public
     * @param mixed $callable
     * @param array $vars       参数
     * @param bool  $accessible 设置是否可访问
     * @return mixed
     */
    public function invoke($callable, array $vars = [], $accessible = false)
    {
        if ($callable instanceof Closure) {
            return $this->invokeFunction($callable, $vars);
        } elseif (is_string($callable) && false === strpos($callable, '::')) {
            return $this->invokeFunction($callable, $vars);
        } else {
            return $this->invokeMethod($callable, $vars, $accessible);
        }
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param mixed $method     方法
     * @param array $vars       参数
     * @param bool  $accessible 设置是否可访问
     * @return mixed
     */
    public function invokeMethod($methods, array $vars = [], $accessible = false)
    {
        if (is_array($methods)) {
            list($class, $method) = $methods;

            $class = is_object($class) ? $class : $this->invokeClass($class);

        } else {
            // 静态方法
            list($class, $method) = explode('::', $methods);
        }

        try {
            $reflect = new ReflectionMethod($class, $method);
        } catch (Exception $e) {
            $class = is_object($class) ? get_class($class) : $class;
            throw new Exception('method not exists: ' . $class . '::' . $method . '()', "{$class}::{$method}", $e);
        }

        $args = $this->bindParams($reflect, $vars);

        if ($accessible) {
            $reflect->setAccessible($accessible);
        }

        return $reflect->invokeArgs(is_object($class) ? $class : null, $args);
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @access public
     * @param  string    $class 类名
     * @param  array     $vars  参数
     * @return mixed
     */
    public function invokeClass($class, $vars = [])
    {
        try {
            $reflect = new ReflectionClass($class);

            $constructor = $reflect->getConstructor();

            $args = $constructor ? $this->bindParams($constructor, $vars) : [];

            return $reflect->newInstanceArgs($args);

        } catch (ReflectionException $e) {
            $class = is_object($class) ? get_class($class) : $class;
            throw new Exception('class not exists: ' . $class);
        }
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @access public
     * @param  mixed  $function 函数或者闭包
     * @param  array  $vars     参数
     * @return mixed
     */
    public function invokeFunction($function, $vars = [])
    {
        try {
            $reflect = new ReflectionFunction($function);

            $args = $this->bindParams($reflect, $vars);

            return call_user_func_array($function, $args);
        } catch (ReflectionException $e) {
            throw new Exception('function not exists: ' . $function . '()');
        }
    }

    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param  object  $instance 对象实例
     * @param  mixed   $reflect 反射类
     * @param  array   $vars   参数
     * @return mixed
     */
    public function invokeReflectMethod($instance, $reflect, $vars = [])
    {
        $args = $this->bindParams($reflect, $vars);

        return $reflect->invokeArgs($instance, $args);
    }

    /**
     * 绑定参数
     * @access protected
     * @param  \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param  array                                 $vars    参数
     * @return array
     */
    protected function bindParams($reflect, $vars = [])
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        foreach ($params as $param) {
            $name      = $param->getName();
            $class     = $param->getClass();
            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            }  elseif (isset($vars[$name])) {
                $args[] = $vars[$name];
            }  elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @access protected
     * @param  string   $className  类名
     * @param  array    $vars       参数
     * @return mixed
     */
    protected function getObjectParam($className, &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }


    public function __set($name, $value)
    {
        $this->bind($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function count()
    {
        return count($this->instances);
    }

}