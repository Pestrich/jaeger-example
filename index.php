<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/Sleep.php';

use Jaeger\Config;
use Jaeger\Span;
use OpenTracing\GlobalTracer;

try {
    $config = new Config(
        require __DIR__ . '/config.php',
        'service-1'
    );

    $config->initializeTracer();

    $tracer = GlobalTracer::get();

    /* Start base span */
    $scope = $tracer->startActiveSpan('Базовый Span');
    /** @var Span $span */
    $span = $scope->getSpan();

    $span->setTag('tag-1', 'value1');
    $span->setTags([
        'tag-2' => true,
        'tag-3' => 123456,
        'process.process-tag-key-1' => 'process-value-1', // all tags with `process.` prefix goes to process section. Похоже не работает если указывать не в настройках
        'global-tag-key-1' => 'global-tag-value-1', // this tag will be appended to all spans. Похоже работает как обычный тэг
    ]);
    $span->log([
        'key-1' => 'value1',
        'key-2' => 2,
        'key-3' => true,
        'key-4' => [
            'nested-key-1' => 'value1',
            'nested-key-2' => 'value2',
        ],
    ]);
    $span->addBaggageItem('baggage-item-1', 'baggage-value1');

    /* Start nested span */
    $nestedSpanScope = $tracer->startActiveSpan('Вложенный Span 1');
    /** @var Span $nestedSpan */
    $nestedSpan = $nestedSpanScope->getSpan();

    $nestedSpan->setTag('Вложенный tag1', 'value1');
    $nestedSpan->addBaggageItem('Вложенный baggage-item1', 'baggage-value1');

    $sleep = new Sleep();
    $sleep->printNumber();
    $sleep->printNumber();
    $sleep->printNumber();

    $nestedSpanScope->close();
    /* End nested span */

    require __DIR__ . '/codes.php';

    $scope->close();
    /* End base span */

    /**
     * Необходимо использовать register_shutdown_function или fastcgi_finish_request,
     * чтобы не задерживать окончание запроса к клиенту.
     */
    register_shutdown_function(static fn () => $tracer->flush());

    // Нужен php-fpm
    // fastcgi_finish_request();
    // $tracer->flush();
} catch (Exception $e) {
    echo "<pre>";
    print_r($e->getMessage() . ' ' . $e->getFile() . ' ' . $e->getLine());
    echo "</pre>";
}
