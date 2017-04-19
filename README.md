# Laravel Console Mutex

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e4083afa-8ca9-4ac0-8be8-9bfadcb05fa7/big.png)](https://insight.sensiolabs.com/projects/e4083afa-8ca9-4ac0-8be8-9bfadcb05fa7)

[![StyleCI](https://styleci.io/repos/59570052/shield?branch=master&style=flat)](https://styleci.io/repos/59570052)
[![Build Status](https://travis-ci.org/dmitry-ivanov/laravel-console-mutex.svg?branch=master)](https://travis-ci.org/dmitry-ivanov/laravel-console-mutex)
[![Coverage Status](https://coveralls.io/repos/github/dmitry-ivanov/laravel-console-mutex/badge.svg?branch=master)](https://coveralls.io/github/dmitry-ivanov/laravel-console-mutex?branch=master)

[![Latest Stable Version](https://poser.pugx.org/illuminated/console-mutex/v/stable)](https://packagist.org/packages/illuminated/console-mutex)
[![Latest Unstable Version](https://poser.pugx.org/illuminated/console-mutex/v/unstable)](https://packagist.org/packages/illuminated/console-mutex)
[![Total Downloads](https://poser.pugx.org/illuminated/console-mutex/downloads)](https://packagist.org/packages/illuminated/console-mutex)
[![License](https://poser.pugx.org/illuminated/console-mutex/license)](https://packagist.org/packages/illuminated/console-mutex)

Prevents overlapping for Laravel console commands.

![Example](doc/img/example.gif)

## Table of contents

- [Requirements](#requirements)
- [Usage](#usage)
- [Strategies](#strategies)
- [Advanced](#advanced)
  - [Set custom timeout](#set-custom-timeout)
  - [Handle several commands](#handle-several-commands)
- [Troubleshooting](#troubleshooting)
  - [Trait included, but nothing happens?](#trait-included-but-nothing-happens)
  - [Several traits conflict?](#several-traits-conflict)

## Requirements

- `PHP >=5.5.9`
- `Laravel >=5.1`

## Usage

1. Install package through `composer`:

    ```shell
    composer require illuminated/console-mutex
    ```

2. Use `olafnorge\Console\WithoutOverlapping` trait:

    ```php
    use olafnorge\Console\WithoutOverlapping;

    class ExampleCommand extends Command
    {
        use WithoutOverlapping;

        // ...
    }
    ```

## Strategies

Overlapping can be prevented by various strategies:

- `file` (default)
- `mysql`
- `redis`
- `memcached`

Default `file` strategy is fine for a small applications, which are deployed on a single server.
If your application is more complex and, for example, is deployed on a several nodes, then you probably would like to use some other mutex strategy.

You can change mutex strategy by specifying `$mutexStrategy` field:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    protected $mutexStrategy = 'mysql';

    // ...
}
```

Or by using `setMutexStrategy` method:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    public function __construct()
    {
        parent::__construct();

        $this->setMutexStrategy('mysql');
    }

    // ...
}
```

## Advanced

### Set custom timeout

By default mutex is checking for a running command, and if it finds such, it just exits. However, you can manually set
timeout for a mutex, so it can wait for another command to finish it's execution, instead of just quitting immediately.

You can change mutex timeout by specifying `$mutexTimeout` field:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    protected $mutexTimeout = 3000; // milliseconds

    // ...
}
```

Or by using `setMutexTimeout` method:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    public function __construct()
    {
        parent::__construct();

        $this->setMutexTimeout(3000); // milliseconds
    }

    // ...
}
```

There are three possible options for `$mutexTimeout` field:

- `0` - check without waiting (default);
- `{milliseconds}` - check, and wait for a maximum of milliseconds specified;
- `null` - wait, till running command finish it's execution;

### Handle several commands

Sometimes it is useful to set common mutex for a several commands. You can easily achieve this by setting them the same mutex name.
By default, mutex name is generated based on a command's name and arguments. To change this, just override `getMutexName` method in your command:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    public function getMutexName()
    {
        return "icmutex-for-command1-and-command2";
    }

    // ...
}
```

## Troubleshooting

### Trait included, but nothing happens?

Note, that `WithoutOverlapping` trait is overriding `initialize` method:

```php
trait WithoutOverlapping
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeMutex();
    }

    // ...
}
```

If your command is overriding `initialize` method too, then you should call `initializeMutex` method by yourself:

```php
class ExampleCommand extends Command
{
    use WithoutOverlapping;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeMutex();

        $this->foo = $this->argument('foo');
        $this->bar = $this->argument('bar');
        $this->baz = $this->argument('baz');
    }

    // ...
}
```

### Several traits conflict?

If you're using some other cool `illuminated/console-%` packages, well, then you can find yourself getting "traits conflict".
For example, if you're trying to build [loggable command](https://github.com/dmitry-ivanov/laravel-console-logger), which is protected against overlapping:

```php
class ExampleCommand extends Command
{
    use Loggable;
    use WithoutOverlapping;

    // ...
}
```

You'll get fatal error, the "traits conflict", because both of these traits are overriding `initialize` method:
>If two traits insert a method with the same name, a fatal error is produced, if the conflict is not explicitly resolved.

But don't worry, solution is very simple. Override `initialize` method by yourself, and initialize traits in required order:

```php
class ExampleCommand extends Command
{
    use Loggable;
    use WithoutOverlapping;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->initializeMutex();
        $this->initializeLogging();
    }

    // ...
}
```
