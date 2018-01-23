<?php

namespace Tests;

use StdClass;
use PHPUnit\Framework\TestCase;
use carlosjfernandes\Container\Container;
use carlosjfernandes\Container\Exceptions\ContainerException;

class ContainerTest extends TestCase
{
    public function test_bind_from_clousure()
    {
        $container = new Container();

        $container->bind('key', function () {
            return 'Object';
        });

        $this->assertSame('Object', $container->make('key'));
    }

    public function test_bind_instance()
    {
        $container = new Container();

        $std = new StdClass();

        $container->instance('key', $std);

        $this->assertSame($std, $container->make('key'));
    }

    public function test_singleton_instance()
    {
        $container = new Container();

        $container->singleton('foo', 'Tests\Foo');

        $this->assertSame($container->make('foo'), $container->make('foo'));
    }

    public function test_bind_from_class_name()
    {
        $container = new Container();

        $container->bind('key', 'StdClass');

        $this->assertInstanceOf('StdClass', $container->make('key'));
    }

    public function test_bind_with_automatic_resolution()
    {
        $container = new Container();

        $container->bind('foo', 'Tests\Foo');

        $this->assertInstanceOf('Tests\Foo', $container->make('foo'));
    }

    public function test_expected_container_exception_if_dependency__does_not_exist()
    {
        $container = new Container();

        $container->bind('qux', 'Tests\Qux');

        $this->expectException(ContainerException::class);

        $container->make('qux');

    }

    /**
     * @expectedException \carlosjfernandes\Container\Exceptions\ContainerException
     * @expectedExceptionMessage Unable to build build [Tests\Norf]: Class Tests\Norf does not exist
     */
    public function test_class_does_not_exist()
    {
        $container = new Container();

        $container->bind('norf', 'Tests\Norf');

        $container->make('norf');
    }

    public function test_container_make_with_arguments_does_not_class()
    {
        $container = new Container();

        $this->assertInstanceOf(
            'Tests\MailDummy',
            $container->make('Tests\MailDummy', ['url' => 'localhost', 'key' => 'secret']));
    }

    public function test_container_make_with_default_arguments()
    {
        $container = new Container();

        $this->assertInstanceOf(
            'Tests\MailDummy',
            $container->make('Tests\MailDummy', ['url' => 'localhost']));
    }
}

class MailDummy
{
    private $url;
    private $key;

    public function __construct($url, $key = null)
    {
        $this->url = $url;
        $this->key = $key;
    }
}

class Foo
{
    public function __construct(Bar $bar)
    {
        //
    }
}

class Bar
{
    public function __construct(FooBar $foobar)
    {
        //
    }
}

class FooBar
{
    public function __construct(Delta $delta)
    {

    }
}

class Delta
{

}

class Qux
{
    public function __construct(Norf $norf)
    {

    }
}