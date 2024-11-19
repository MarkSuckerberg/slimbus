<?php

namespace Statbus\Controllers;

use DateTimeImmutable;
use Psr\Container\ContainerInterface;
use Statbus\Controllers\Controller as Controller;
use Statbus\Models\Stat as Stat;

class StatController extends Controller
{
  private Stat $statModel;

  public function __construct(ContainerInterface $container)
  {
    parent::__construct($container);
    $this->statModel = (new Stat());

    $this->breadcrumbs['All Stats'] = $this->router->pathFor('stat.list');
  }

  public function getRoundStat($round, $stat, $json = false)
  {
    $stat = $this->DB->rowObj("SELECT * FROM tbl_feedback WHERE round_id = ? AND key_name = ?", $round, $stat);
    if (!$stat) {
      return false;
    }
    if ($json) {
      return $stat->json;
    }
    return $this->statModel->parseStat($stat);
  }

  public function getStatsForRound($round, array $stats = null)
  {
    if (is_array($stats)) {
      $and = "((key_name = '";
      $and .= implode("') OR (key_name = '", $stats);
      $and .= "'))";
      $stats = $this->DB->run("SELECT * FROM tbl_feedback
        WHERE $and
        AND round_id = ?", $round);
      $tmp = [];
      foreach ($stats as &$stat) {
        $stat = $this->statModel->parseStat($stat);
        $tmp[$stat->key_name] = $stat;
      }
      $stats = $tmp;
      return $stats;
    } else {
      $stats = $this->DB->run("SELECT key_name FROM tbl_feedback WHERE round_id = ? ORDER BY key_name ASC", $round);
      if (!$stats)
        return false;
      foreach ($stats as $s) {
        $tmp[] = $s->key_name;
      }
      $stats = array_flip($tmp);
      return $stats;
    }
  }

  public function list($request, $response, $args)
  {
    $stats = $this->DB->run("SELECT R.key_name, R.key_type, R.version, count(R.round_id) AS rounds FROM tbl_feedback R GROUP BY R.key_name, R.version ORDER BY R.key_name ASC;");

    $format = null;
    if (isset($request->getQueryParams()['format'])) {
      $format = htmlspecialchars($request->getQueryParams()['format']);
    }
    if ($format === 'json') {
      return $response->withJson($stats);
    }

    return $this->view->render($response, 'stats/listing.tpl', [
      'stats' => $stats
    ]);
  }

  public function collate($request, $response, $args)
  {
    $version = 1;
    if (isset($args['version'])) {
      $version = filter_var($args['version'], FILTER_VALIDATE_INT);
    }
    if (isset($args['stat'])) {
      $stat = htmlspecialchars($args['stat']);
      $p = $request->getQueryParams();
      $start = null;
      $end = null;
      if (isset($p['start']) && isset($p['end'])) {
        $start = htmlspecialchars($p['start']);
        $end = htmlspecialchars($p['end']);
      }
    }

    $minmax = $this->DB->rowObj("SELECT 
      min(STR_TO_DATE(R.datetime, '%Y-%m-%d')) AS min,
      DATE_ADD(max(STR_TO_DATE(R.datetime, '%Y-%m-%d')), INTERVAL 1 DAY) AS max
      FROM tbl_feedback AS R
      WHERE R.key_name = ? AND R.version = ?;", $stat, $version);

    if (!$minmax->min) {
      return $this->view->render($response, 'base/error.tpl', [
        'message' => "Stat '$stat' not found.",
        'code' => 404,
        'linkText' => "Back to all stats",
        'link' => parent::getFullURL($this->router->pathFor(
          'stat.list'
        ))
      ]);

    }

    if (!$start) {
      #$start = new DateTimeImmutable($minmax->min);
      #$end = new DateTimeImmutable($minmax->max);
      #$diff = $start->diff($end)->days;

      #$start = $diff > 60 ? $start->sub(new DateInterval('P30D'))->format('Y-m-d') : $minmax->min;

      $start = $minmax->min;
      $end = $minmax->max;
    } else {
      $startDate = new DateTimeImmutable($start);
      $start = $startDate->format('Y-m-d');
      $endDate = new DateTimeImmutable($end);
      $end = $endDate->format('Y-m-d');
    }
    $stat = $this->DB->run("SELECT R.key_name, R.key_type, R.json, R.round_id, R.version, R.datetime FROM tbl_feedback R WHERE R.key_name = ? AND R.version = ? AND R.datetime BETWEEN ? AND ? ORDER BY R.datetime ASC", $stat, $version, $start, $end);
    $stat = $this->statModel->parseStat($stat, TRUE);

    $format = null;
    if (isset($request->getQueryParams()['format'])) {
      $format = htmlspecialchars($request->getQueryParams()['format']);
    }
    if ($format === 'json') {
      return $response->withJson($stat);
    }

    $this->ogdata['title'] = 'Collated stats for ' . $stat->key_name;
    #$this->ogdata['description'] = 'Counting ' . count($stat->data) . ' entries';

    $this->breadcrumbs[$stat->key_name] = $this->router->pathFor('stat.collate', ['stat' => $stat->key_name, 'version' => $stat->version]);

    return $this->view->render($response, 'stats/collated.tpl', [
      'stat' => $stat,
      'start' => $start,
      'end' => $end,
      'min' => $minmax->min,
      'max' => $minmax->max,
      'json' => json_encode($stat),
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }
}