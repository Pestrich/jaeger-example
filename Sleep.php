<?php

declare(strict_types=1);

use Jaeger\Span;
use OpenTracing\GlobalTracer;
use OpenTracing\Tracer;

final class Sleep
{
    private Tracer $tracer;

    public function __construct()
    {
        $this->tracer = GlobalTracer::get();
    }

    /**
     * @throws Exception
     */
    public function printNumber(): void
    {
        /* Start nested span */
        $nestedSpanScope = $this->tracer->startActiveSpan('Вложенный Span 2');
        /** @var Span $nestedSpan */
        $nestedSpan = $nestedSpanScope->getSpan();

        $nestedSpan->setTag('Вложенный tag1', 'value1');
        $nestedSpan->addBaggageItem('Вложенный baggage-item1', 'baggage-value1');

        echo "<pre>";
        print_r(random_int(0, 100));
        echo "</pre>";
        sleep(1);

        $nestedSpanScope->close();
        /* End nested span */
    }

    /*public function __destruct()
    {
        register_shutdown_function(static fn () => $this->tracer->flush());
    }*/
}
