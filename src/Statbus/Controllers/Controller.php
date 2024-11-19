<?php

namespace Statbus\Controllers;

use GuzzleHttp\Psr7\Request as Psr7Request;
use ParagonIE\EasyDB\EasyDB;
use Psr\Container\ContainerInterface;
use Slim\Router;
use Statbus\Extensions\PrefixedDB;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class Controller
{

  protected ContainerInterface $container;
  protected $view;
  protected PrefixedDB $DB;
  protected Router $router;
  protected $settings;
  protected $response;
  protected $request;

  public $page = 1;
  public $pages = 0;
  public $per_page = 60;

  public $breadcrumbs = [];
  public $ogdata = [];

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
    $this->DB = $this->container->get('DB');
    $this->view = $this->container->get('view');
    $this->router = $this->container->get('router');
    $this->request = $this->container->get('request');
    $this->response = $this->container->get('response');
    $this->ogdata = [
      'site_name' => $this->container->get('settings')['statbus']['app_name'],
      'url' => $this->request->getUri(),
      'type' => 'object',
      'title' => $this->container->get('settings')['statbus']['app_name'],
      'image' => 'https://shiptest.net/images/shiptest-logo.png'
    ];
    $this->view->getEnvironment()->addGlobal('ogdata', $this->ogdata);
    $this->view->getEnvironment()->addGlobal('settings', $this->container->get('settings')['statbus']);
    if (!$this->DB) {
      $error = $this->view->render($this->response, 'base/error_critical.tpl', [
        'message' => "Unable to establish a connection to the statistics database.",
        'text' => 'This means that the game server database is down, or otherwise unreachable. This error has been logged and your Statbus administrators have been made aware of the issue.',
        'code' => 500,
        'skip' => true
      ]);
      $this->response = $this->response->withStatus(500);
      die($this->response->getBody());
    }
  }

  public function getFullURL($path)
  {
    $baseUri = $this->request->getUri();
    $base = $baseUri->getScheme() . '://' . $baseUri->getHost();
    return $base . $path;
  }
}
