<?php
namespace Statbus\Middleware;

use Slim\Http\Response;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Views\Twig;
use Statbus\Controllers\AuthController as Auth;
class UserGuard
{
  private ContainerInterface $container;
  private $settings;
  private Twig $view;
  private int $level;
  private $user;

  public function __construct($container, $level = 2)
  {
    $this->container = $container;
    $this->user = $container->get('user');
    $this->view = $container->get('view');
    $this->settings = $container->get('settings');
    $this->level = $level;
  }

  public function __invoke(Request $request, Response $response, $next)
  {
    if (!$this->settings['statbus']['auth']['remote_auth'] && !$this->settings['statbus']['ip_auth']) {
      return $this->view->render($response, 'base/error.tpl', [
        'message' => "No authentication mechanisms specified.",
        'code' => 403
      ]);
    }
    if (!$this->user || !$this->user->ckey) {
      $_SESSION['return_uri'] = (string) $request->getUri();
      $args = null;
      return (new Auth($this->container))->auth($request, $response, $args);
    }
    switch ($this->level) {
      case 1:
      default:
        if (!$this->user->ckey) {
          return $this->view->render($response, 'base/error.tpl', [
            'message' => "You must be logged in to access this page",
            'code' => 403
          ]);
        }
        break;

      case 2:
        if (!$this->user->canAccessTGDB) {
          return $this->view->render($response, 'base/error.tpl', [
            'message' => "You do not have permission to access this page.",
            'code' => 403
          ]);
        }
        $this->view->getEnvironment()->addGlobal('classified', TRUE);
        break;
    }
    $response = $next($request, $response);
    return $response;
  }
}