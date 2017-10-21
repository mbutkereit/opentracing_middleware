<?php
namespace Drupal\tracing_middleware\Tracer;

use JaegerPhp\Jaeger;
use OpenTracing\Tracer;

class JaegerTracer extends Jaeger implements Tracer{
}