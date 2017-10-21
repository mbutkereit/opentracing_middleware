<?php

namespace Drupal\tracing_middleware\Tracer;

use OpenTracing\Propagators\TextMapReader;
use OpenTracing\Propagators\TextMapWriter;
use OpenTracing\Span;
use OpenTracing\SpanContext;
use OpenTracing\SpanReference;
use OpenTracing\Tag;
use OpenTracing\Tracer;

class NullTracer implements Tracer {

  private $span;
  private $spanContext;

  public function startSpan($operationName, SpanReference $parentReference = NULL, $startTimestamp = NULL, Tag ...$tags) {
    return new NullSpan();

  }

  public function inject(SpanContext $spanContext, $format, TextMapWriter $carrier) {}

  public function extract($format, TextMapReader $carrier) {}
}