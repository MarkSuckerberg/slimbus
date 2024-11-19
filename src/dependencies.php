<?php

use Slim\Csrf\Guard;
use Slim\Http\Environment;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;

define('ROOTDIR', dirname(__DIR__));

use Slim\App;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\GreedyCacheStrategy;
use Kevinrob\GuzzleCache\Storage\DoctrineCacheStorage;
use Doctrine\Common\Cache\ChainCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\Common\Cache\ArrayCache;
use Twig\TwigFilter;
use Twig\Extension\DebugExtension;
use Twig\Extra\Intl\IntlExtension;

return function (App $app) {
  $container = $app->getContainer();

  // DB
  $container['DB'] = function ($c) {
    $settings = $c->get('settings')['database']['primary'];
    return (new Statbus\Controllers\DBController($settings))->db;
  };

  $container['ALT_DB'] = function ($c) {
    $settings = $c->get('settings')['database']['alt'];
    return (new Statbus\Controllers\DBController($settings))->db;
  };

  // User
  $container['user'] = function ($container) {
    $user = (new Statbus\Controllers\UserController($container))->fetchUser();
    return $user;
  };

  //Crsf
  $container['csrf'] = function ($c) {
    $csrf = new Guard('sb_csrf');
    $csrf->setFailureCallable(function ($request, $response, $next) {
      $request = $request->withAttribute("csrf_status", false);
      return $next($request, $response);
    });
    return $csrf;
  };

  // Register component on container
  $container['view'] = function ($container) {
    $settings = $container->get('settings')['twig'];

    $view = new Twig($settings['template_path'], [
      'debug' => $settings['twig_debug'],
      'cache' => $settings['template_cache']
    ]);

    // Instantiate and add Slim specific extension
    $router = $container->get('router');
    $uri = Uri::createFromEnvironment(new Environment($_SERVER));
    $view->addExtension(new TwigExtension($router, $uri));

    $view->addExtension(new DebugExtension);
    $view->addExtension(new IntlExtension);

    //Fancy timestamp filter
    $twigTimestampFilter = new TwigFilter('timestamp', function ($string) {
      $string = date('Y-m-d H:i:s', strtotime($string));
      $return = "<span class='timestamp'>";
      $return .= "<time datetime='$string' title='$string' ";
      $return .= "data-toggle='tooltip'>$string</time></span>";
      return $return;
    }, array('is_safe' => array('html')));
    $view->getEnvironment()->addFilter($twigTimestampFilter);

    //My censored filter that I <3
    $twigCensorFilter = new TwigFilter('censor', function ($string) {
      $string = strip_tags($string);
      return preg_replace("/\S/", '█', $string);
    });
    $view->getEnvironment()->addFilter($twigCensorFilter);

    //Global statbus settings
    $view->getEnvironment()->addGlobal('statbus', $container->get('settings')['statbus']);

    //Alert HTML if set
    if (is_file(__DIR__ . "/conf/alert.html")) {
      $alert = file_get_contents(__DIR__ . "/conf/alert.html");
      $view->getEnvironment()->addGlobal('alert', $alert);
    }

    //User added by the UserController when it gets instantiated
    return $view;
  };

  //Guzzle
  $container['guzzle'] = function ($container) {
    $stack = HandlerStack::create();
    $stack->push(
      new CacheMiddleware(new GreedyCacheStrategy(new DoctrineCacheStorage(new ChainCache([
        new ArrayCache(),
        new FilesystemCache('/tmp/'),
      ])), 3600)),
      'greedy-cache'
    );
    $client = new Client([
      'handler' => $stack,
      'headers' => [
        'Accept-Encoding' => 'gzip',
        'User-Agent' => 'Statbus'
      ],
    ]);
    return $client;
  };
};

function pick($list)
{
  if (is_string($list)) {
    $list = explode(',', $list);
  } else if (is_object($list)) {
    $list = json_decode(json_encode($list), TRUE);
  }
  return $list[floor(rand(0, count($list) - 1))];
}
