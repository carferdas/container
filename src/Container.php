<?php

namespace carlosjfernandes\Container;

use Closure;
use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;
use carlosjfernandes\Container\Exceptions\ContainerException;

class Container
{
    protected $shared = [];
    protected $bindings = [];
    private static $instance;

    public static function getInstance()
    {
        if (! static::$instance) {
            static::$instance = new Container;
        }

        return static::$instance;
    }

    public static function setInstance(Container $container)
    {
        static::$instance = $container;
    }

    public function bind($name, $resolver, $shared = false)
    {
        $this->bindings[$name] = ['resolver' => $resolver, 'shared' => $shared];
    }

    public function singleton($name, $resolver)
    {
        $this->bind($name, $resolver, true);
    }

    public function make($name, $args = [])
    {
        if (isset($this->shared[$name])) {
            return $this->shared[$name];
        }

        if (isset($this->bindings[$name])) {
            $resolver = $this->bindings[$name]['resolver'];
            $shared = $this->bindings[$name]['shared'];
        } else {
            $resolver = $name;
            $shared = false;
        }

        if ($resolver instanceof Closure) {
            $object = $resolver($this);
        } else {
            $object = $this->build($resolver, $args);
        }

        if ($shared) {
            $this->shared[$name] = $object;
        }

        return $object;
    }

    public function instance($name, $object)
    {
        $this->shared[$name] = $object;
    }

    public function build($name, $args)
    {
        try {
            $reflection = new ReflectionClass($name);
        } catch (ReflectionException $exception) {
            throw new ContainerException("Unable to build build [$name]: {$exception->getMessage()}", null, $exception);
        }

        if (! $reflection->isInstantiable()) {
            throw new InvalidArgumentException("$name is not instantiable");
        }

        $constructor = $reflection->getConstructor(); //ReflectionMethod

        if (is_null($constructor)) {
            return new $name;
        }

        $constructorParameters = $constructor->getParameters(); //ReflectionParameter array

        $dependencies = [];

        foreach ($constructorParameters as $constructorParameter) {
            $parameterName = $constructorParameter->getName();

            if (isset($args[$parameterName])) {
                $dependencies[] = $args[$parameterName];
                continue;
            }

            try {
                $parameterClass = $constructorParameter->getClass();
            } catch (ReflectionException $exception) {
                throw new ContainerException("Unable to build [$name]: {$exception->getMessage()}", null, $exception);
            }

            if ($parameterClass != null) {
                $parameterClassName = $parameterClass->getName();
                $dependencies[] = $this->build($parameterClassName, $args);
            } else {
                if ($constructorParameter->isOptional()) {
                    $dependencies[] = $constructorParameter->getDefaultValue();
                } else {
                    throw new ContainerException("Please provide the value of the parameter [$parameterName]");
                }
            }
        }

        return $reflection->newInstanceArgs($dependencies);
    }
}