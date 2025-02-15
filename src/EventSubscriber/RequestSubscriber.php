<?php
// Based on https://drupal.stackexchange.com/a/284722

namespace Drupal\jupyterlite\EventSubscriber;

use Drupal\Core\Controller\ControllerResolverInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Responsible for routing all requests under /jupyterlite/* to the farmos_asset_link controller.
 */
class RequestSubscriber implements EventSubscriberInterface  {

  /**
   * The Argument Resolver service.
   *
   * @var \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface
   */
  protected $argumentResolver;

  /**
   * The Controller Resolver service.
   *
   * @var \Drupal\Core\Controller\ControllerResolverInterface
   */
  protected $controllerResolver;

  /**
   * The Route Provider service.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * RequestSubscriber constructor.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   * @param \Drupal\Core\Controller\ControllerResolverInterface $controllerResolver
   * @param \Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface $argumentResolver
   */
  public function __construct(RouteProviderInterface $routeProvider, ControllerResolverInterface $controllerResolver, ArgumentResolverInterface $argumentResolver) {
    $this->routeProvider = $routeProvider;
    $this->controllerResolver = $controllerResolver;
    $this->argumentResolver = $argumentResolver;
  }

  /**
   * The request event handler.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The event.
   */
  public function checkAppRequest(RequestEvent $event) {
    $request = $event->getRequest();
    $path = $request->getPathInfo();

    // Redirect all requests that start with "/jupyterlite" requests to a single
    // route. Note: this is necessary because core doesn't have any other
    // way to really handle "wildcard/catch all" routes.
    if (strpos($path, '/jupyterlite') === 0) {
      $route = $this->routeProvider->getRouteByName('jupyterlite.content');
      $definition = $route->getDefault('_controller');
      $controller = $this->controllerResolver->getControllerFromDefinition($definition, $path);
      $arguments = $this->argumentResolver->getArguments($request, $controller);
      $response = \call_user_func_array($controller, $arguments);
      if ($response instanceof Response) {
        // Set the response, necessary so the kernel knows it got something
        // which will also prevent any other event handler from running.
        $event->setResponse($response);
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Check for /app requests.
    $events[KernelEvents::REQUEST][] = ['checkAppRequest', 1000];
    return $events;
  }

}
