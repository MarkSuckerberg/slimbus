<?php

namespace Statbus\Controllers;

use ParagonIE\EasyDB\EasyDB;
use Psr\Container\ContainerInterface;
use Slim\Router;
use Statbus\Extensions\PrefixedDB;

class Controller
{

  protected ContainerInterface $container;
  protected $view;
  protected PrefixedDB $DB;
  protected Router $router;
  protected $settings;

  public $page = 1;
  public $pages = 0;
  public $per_page = 60;

  public $breadcrumbs = [];
  public $ogdata = [];

  public $request;
  public $response;

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
    $base = str_replace("/stats", "", trim($this->request->getUri()->getBaseUrl(), '/'));
    return $base . $path;
  }
}
