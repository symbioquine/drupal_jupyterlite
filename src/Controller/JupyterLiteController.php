<?php

namespace Drupal\jupyterlite\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;

use Drupal\Component\Utility\UrlHelper;

use Drupal\Core\Render\BareHtmlPageRenderer;


/**
 * Defines JupyterLiteController class.
 */
class JupyterLiteController extends ControllerBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;


  /**
   * Constructs a new JupyterLiteController object.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *          The request stack.
   * @param \Drupal\Core\State\State $state
   *          The object State.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(RequestStack $request_stack, ConfigFactoryInterface $config_factory,
    AccountProxyInterface $currentUser) {
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
    $this->currentUser = $currentUser;
  }

  /**
   * Top-level handler for jupyterlite requests.
   */
  public function content() {
    $request = $this->requestStack->getCurrentRequest();
    $path = $request->getPathInfo();

    if (strpos($path, '/jupyterlite') !== 0) {
      $response = new Response();
      $response->setStatusCode(400);
      $response->headers->set('Content-Type', 'text/plain');
      $response->setContent("This controller is only intended to serve paths under /jupyterlite");
      return $response;
    }

    // This should never happen since a compliant web server should handle sending a 400 for requests with relative paths
    // but since it could be a pretty bad security vulnerbility if this ever got through we'll defend against it.
    // https://datatracker.ietf.org/doc/html/rfc7230#section-5.3.1
    if (strpos($path, '../') !== false) {
      $response = new Response();
      $response->setStatusCode(400);
      $response->headers->set('Content-Type', 'text/plain');
      $response->setContent("This controller is only intended to serve paths under /jupyterlite");
      return $response;
    }

    $base_path = base_path();

    // Redirect from '/jupyterlite' to '/jupyterlite/' since omitting the trailing slash breaks things
    if ($path == '/jupyterlite') {
      $response = new Response();
      $response->setStatusCode(301);
      $response->headers->set('Location', $base_path . 'jupyterlite/');
      $response->setContent("JupyterLite must be accessed with a trailing slash '/' after 'jupyterlite'");
      return $response;
    }

    // TODO: Use injected services here
    $module_base_path = \Drupal::service('file_system')->realpath(\Drupal::service('module_handler')->getModule('jupyterlite')->getPath());

    $jupyterlite_dist_path = "$module_base_path/jupyterlite-dist/";

    // Remove the '/jupyterlite' prefix
    $path_suffix = substr($path, 12);

    $file_path = $jupyterlite_dist_path . $path_suffix;

    if (empty($path_suffix) || $path_suffix === '/' || !file_exists($file_path)) {
      $file_path = $jupyterlite_dist_path . "index.html";
    }

    $content_type = $this->getContentMimetype($file_path);

    $response_content = file_get_contents($file_path);

    $response = new Response();
    $response->setStatusCode(200);

    $cookie = new Cookie('jupyterliteDrupalBasePath', $base_path, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = false, $raw = true, $sameSite = null);
    $response->headers->setCookie($cookie);

    $response->headers->set('Content-Type', $content_type);
    $response->setContent($response_content);
    return $response;
  }

  // roughly based on https://developer.mozilla.org/en-US/docs/Learn/Server-side/Node_server_without_framework
  private function getContentMimetype($file_path) {
    $extname = pathinfo($file_path, PATHINFO_EXTENSION);

    $mime_types = [
      'html' => 'text/html',
      'js' => 'text/javascript',
      'css' => 'text/css',
      'json' => 'application/json',
      'png' => 'image/png',
      'jpg' => 'image/jpg',
      'gif' => 'image/gif',
      'svg' => 'image/svg+xml',
      'wav' => 'audio/wav',
      'mp4' => 'video/mp4',
      'woff' => 'application/font-woff',
      'ttf' => 'application/font-ttf',
      'eot' => 'application/vnd.ms-fontobject',
      'otf' => 'application/font-otf',
      'wasm' => 'application/wasm',
    ];

    return $mime_types[$extname] ?? 'application/octet-stream';
  }

}
