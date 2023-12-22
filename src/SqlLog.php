<?php

namespace Chengyi\Thinkmanorm;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

class SqlLog extends Logger
{
    private static $_instance;

    public static function getInstance(): SqlLog
    {
        if (!(self::$_instance instanceof SqlLog)) {
            $config = config('log', [])['default'];
            $handlers = self::handlers($config);
            $processors = self::processors($config);
            self::$_instance = new self('default', $handlers, $processors);
        }
        return self::$_instance;
    }

    /**
     * Handlers.
     * @param array $config
     * @return array
     */
    protected static function handlers(array $config): array
    {
        $handlerConfigs = $config['handlers'] ?? [[]];
        $handlers = [];
        foreach ($handlerConfigs as $value) {
            $class = $value['class'] ?? [];
            $constructor = $value['constructor'] ?? [];

            $formatterConfig = $value['formatter'] ?? [];

            $class && $handlers[] = self::handler($class, $constructor, $formatterConfig);
        }

        return $handlers;
    }


    /**
     * Handler.
     * @param string $class
     * @param array $constructor
     * @param array $formatterConfig
     * @return HandlerInterface
     */
    protected static function handler(string $class, array $constructor, array $formatterConfig): HandlerInterface
    {
        /** @var HandlerInterface $handler */
        $handler = new $class(... array_values($constructor));

        if ($handler instanceof FormattableHandlerInterface && $formatterConfig) {
            $formatterClass = $formatterConfig['class'];
            $formatterConstructor = $formatterConfig['constructor'];

            /** @var FormatterInterface $formatter */
            $formatter = new $formatterClass(... array_values($formatterConstructor));

            $handler->setFormatter($formatter);
        }

        return $handler;
    }

    /**
     * Processors.
     * @param array $config
     * @return array
     */
    protected static function processors(array $config): array
    {
        $result = [];
        if (!isset($config['processors']) && isset($config['processor'])) {
            $config['processors'] = [$config['processor']];
        }

        foreach ($config['processors'] ?? [] as $value) {
            if (is_array($value) && isset($value['class'])) {
                $value = new $value['class'](... array_values($value['constructor'] ?? []));
            }
            $result[] = $value;
        }

        return $result;
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     */
    public function sql($message, array $context = []): void
    {
        $this->addRecord(static::INFO, (string) $message, $context);
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->addRecord(self::INFO, (string) $message, $context);
    }
}