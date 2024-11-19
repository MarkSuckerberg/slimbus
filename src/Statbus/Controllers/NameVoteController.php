<?php

namespace Statbus\Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Slim\Csrf\Guard;
use Statbus\Controllers\Controller as Controller;
use Statbus\Extensions\PrefixedDB;
use Statbus\Models\Player;


class NameVoteController extends Controller
{

  private PrefixedDB $alt_db;
  private Guard $csrf;
  private $user;

  public function __construct(ContainerInterface $container)
  {
    parent::__construct($container);
    $settings = $this->container->get('settings');
    $this->alt_db = $this->container->get('ALT_DB');
    $this->user = $this->container->get('user');

    $this->ogdata['title'] = "Name Voter 5000";
  }

  public function index($request, $response, $args)
  {
    $this->csrf = $this->container->get('csrf');
    return $this->view->render($response, 'misc/namevote/vote.tpl', [
      'name' => $this->getname(),
      'csrf' => $this->csrf->generateToken()
    ]);
  }

  public function cast($request, $response, $args)
  {
    if (false === $request->getAttribute('csrf_status')) {
      $response = $response->withStatus(403);
      return $response->withJson(json_encode(['CSRF failed', $request->getParsedBody()]));
    }

    $args = $request->getParsedBody();
    if (!isset($args['vote']) || !isset($args['name'])) {
      return $response->withJson(json_encode(['error' => "Missing vote arguments"]));
    }

    $vote = htmlspecialchars($args['vote']);
    $name = htmlspecialchars($args['name']);

    $vote = 'nay' === $vote ? 0 : 1;

    if ($this->alt_db->rowObj("SELECT name, ckey FROM name_vote WHERE name = ? and ckey = ?", $args['name'], $this->user->ckey)) {
      return $this->index($request, $response, $args);
    }

    try {
      $this->alt_db->insert('name_vote', [
        'name' => $name,
        'good' => $vote,
        'ckey' => $this->user->ckey,
      ]);
    } catch (Exception $e) {
      return json_encode($e);
    }

    return $response->withRedirect($this->router->pathFor('nameVoter'));
  }

  public function rankings($request, $response, $args)
  {
    $rank = 'best';
    $rank = htmlspecialchars($args['rank']);
    if ('worst' === $rank) {
      $ranking = $this->alt_db->run("SELECT `name`,
        IFNULL(count(good),1) - sum(good) AS `no`,
        sum(good) AS `yes`
        FROM name_vote
        GROUP BY `name`
        ORDER BY `no` DESC
        LIMIT 0, 100;");
    } else {
      $ranking = $this->alt_db->run("SELECT `name`,
        IFNULL(count(good),1) - sum(good) AS `no`,
        sum(good) AS `yes`
        FROM name_vote
        GROUP BY `name`
        ORDER BY `yes` DESC
        LIMIT 0, 100;");
    }
    return $this->view->render($response, 'misc/namevote/results.tpl', [
      'ranking' => $ranking
    ]);
  }

  public function getName()
  {
    $name = $this->DB->rowObj("SELECT DISTINCT `name`, job
    FROM tbl_death
    ORDER BY RAND()
    LIMIT 0,1;");
    return $name;
  }

}