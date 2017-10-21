<?php

namespace Drupal\tracing_middleware\StackMiddleware;

use OpenTracing\Carriers\HttpHeaders;
use OpenTracing\Carriers\TextMap;
use OpenTracing\Propagator;
use OpenTracing\SpanReference;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use OpenTracing\GlobalTracer;
use OpenTracing\SpanOptions;
use Symfony\Component\HttpKernel\TerminableInterface;
use JaegerPhp\Config as JaegerConfig;

/**
 * Class OpenTracing
 * @package Drupal\Core\StackMiddleware
 */
class OpenTracing implements HttpKernelInterface,TerminableInterface {

  use ContainerAwareTrait;

  /**
   * The wrapped HTTP kernel.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The session service name.
   *
   * @var string
   */
  protected $sessionServiceName;

  /**
   * @var \OpenTracing\Span
   */
  protected $serverSpan;

  /**
   * Constructs a Session stack middleware object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   * @param string $service_name
   *   The name of the session service, defaults to "session".
   */
  public function __construct(HttpKernelInterface $http_kernel, $service_name = 'open_tracing') {
    $this->httpKernel = $http_kernel;
    $this->sessionServiceName = $service_name;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = TRUE) {

    $traceConfig =  JaegerConfig::getInstance();
    $trace = $traceConfig->initTrace('Drupal', '192.168.99.100:5775');
    GlobalTracer::set($trace);
    $factory = new DiactorosFactory();
    try {
      $spanContext = $trace->extract(Propagator::HTTP_HEADERS, HttpHeaders::fromRequest($factory->createRequest($request)));
    }catch (\Exception $e){

    }
    if(isset($spanContext)){
    $this->serverSpan = $trace->startSpan('Full Span', SpanReference::createAsChildOf($spanContext));}else{
      $this->serverSpan = $trace->startSpan('Full Span');
    }
    $result = $this->httpKernel->handle($request, $type, $catch);

    return $result;
  }

  public function terminate(Request $request, Response $response) {
    $tracer = GlobalTracer::get();
    $this->serverSpan->finish();
    $tracer->flush();
  }
}
