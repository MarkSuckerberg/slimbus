<?php

namespace Statbus\Controllers;

use DateTime;
use Psr\Container\ContainerInterface;
use Statbus\Controllers\Controller as Controller;
use Statbus\Models\Round as Round;
use Statbus\Controllers\StatController as StatController;
use Statbus\Controllers\LogsController as LogsController;
use Statbus\Controllers\UserController as UserController;

class RoundController extends Controller
{

  private $columns = "tbl_round.id,
      tbl_round.initialize_datetime,
      tbl_round.start_datetime,
      tbl_round.shutdown_datetime,
      tbl_round.end_datetime,
      tbl_round.server_port AS port,
      tbl_round.server_ip AS ip,
      tbl_round.commit_hash,
      tbl_round.game_mode AS mode,
      tbl_round.game_mode_result AS result,
      tbl_round.end_state,
      tbl_round.shuttle_name AS shuttle,
      tbl_round.map_name AS map,
      tbl_round.station_name,
      SEC_TO_TIME(TIMESTAMPDIFF(SECOND, tbl_round.initialize_datetime, tbl_round.shutdown_datetime)) AS duration,
      SEC_TO_TIME(TIMESTAMPDIFF(SECOND, tbl_round.start_datetime, tbl_round.end_datetime)) AS round_duration,
      SEC_TO_TIME(TIMESTAMPDIFF(SECOND, tbl_round.initialize_datetime, tbl_round.start_datetime)) AS init_time,
      SEC_TO_TIME(TIMESTAMPDIFF(SECOND, tbl_round.end_datetime, tbl_round.shutdown_datetime)) AS shutdown_time";

  private Round $roundModel;
  private StatController $statController;

  public function __construct(ContainerInterface $container)
  {
    parent::__construct($container);
    $this->pages = ceil($this->DB->cell("SELECT count(tbl_round.id) FROM tbl_round WHERE tbl_round.end_state IS NOT NULL") / $this->per_page);
    $this->statController = new StatController($this->container);

    $this->roundModel = new Round($this->container->get('settings')['statbus']);

    $this->breadcrumbs['Rounds'] = $this->router->pathFor('round.index');
  }

  public function index($request, $response, $args)
  {
    if (isset($args['page'])) {
      $this->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    $rounds = $this->DB->run("SELECT $this->columns
      FROM tbl_round
      WHERE tbl_round.end_state IS NOT NULL
      ORDER BY tbl_round.shutdown_datetime DESC
      LIMIT ?,?", ($this->page * $this->per_page) - $this->per_page, $this->per_page);

    foreach ($rounds as &$round) {
      $round = $this->roundModel->parseRound($round);
    }
    if ($rounds) {
      $this->firstListing = $rounds[0]->end_datetime;
      $this->lastListing = end($rounds)->start_datetime;
    } else {
      $this->firstListing = null;
      $this->lastListing = null;
    }
    return $this->view->render($response, 'rounds/listing.tpl', [
      'rounds' => $rounds,
      'round' => $this,
      'wide' => true,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function single($request, $response, $args)
  {
    $id = $args['id'];
    if ($id == 'latest') {
      $values = $this->DB->run("SELECT MAX(id) as id FROM tbl_round WHERE shutdown_datetime IS NOT NULL;");

      if (count($values) != 1) {
        return $this->view->render($response, 'base/error.tpl', [
          'code' => 404,
          'message' => 'Unable to find latest round',
          'link' => $this->router->pathFor('round.index'),
          'linkText' => 'Round Listing',
          'ogdata' => $this->ogdata
        ]);
      }

      $id = $values[0]->id;
    }

    $round = $this->getRound($id);
    if (!$round->id) {
      return $this->view->render($response, 'base/error.tpl', [
        'code' => 404,
        'message' => 'Round not found, or is ongoing',
        'link' => $this->router->pathFor('round.index'),
        'linkText' => 'Round Listing',
        'ogdata' => $this->ogdata
      ]);
    }
    $format = null;
    if (isset($request->getQueryParams()['format'])) {
      $format = htmlspecialchars($request->getQueryParams()['format']);
    }
    if (isset($args['stat'])) {
      $this->stat($round, $args['stat'], $response, $format);

      if ('json' === $format) {
        unset($round->stat->json);
        return $response->withJson($round->stat);
      }
      return $round->stat;
    }
    $round->stats = $this->statController->getStatsForRound($round->id);
    $round->data = $this->statController->getStatsForRound($round->id, [
      'nuclear_challenge_mode',
      'testmerged_prs',
      'newscaster_stories'
    ]);
    if ('json' === $format) {
      return $response->withJson($round);
    }
    return $this->view->render($response, 'rounds/round.tpl', [
      'round' => $round,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function stat(object $round, string $stat, $response, $format)
  {
    $stat = htmlspecialchars($stat);
    $round->stat = (new StatController($this->container))->getRoundStat($round->id, $stat);
    $url = parent::getFullURL($this->router->pathFor('round.single', [
      'id' => $round->id,
      'stat' => $stat
    ]));
    $this->breadcrumbs[$stat] = $url;
    $this->ogdata['url'] = $url;
    $this->ogdata['description'] = "Stats for $stat from round $round->id on $round->server";

    if ($format === 'json') {
      return $response->withJson($stat);
    }

    return $this->view->render($response, 'stats/single.tpl', [
      'round' => $round,
      'stat' => $round->stat,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function stationNames($request, $response, $args)
  {
    $names = $this->DB->run("SELECT station_name, id FROM tbl_round WHERE station_name IS NOT NULL ORDER BY RAND() DESC LIMIT 0, 1000;");
    return $this->view->render($response, 'rounds/stationnames.tpl', [
      'names' => $names,
      'ogdata' => $this->ogdata
    ]);
  }

  public function mapView($request, $response, $args)
  {
    $round = $this->getRound($args['id']);
    $this->breadcrumbs['Map'] = $this->router->pathFor('round.map', [
      'id' => $round->id,
    ]);
    return $this->view->render($response, 'rounds/map.tpl', [
      'round' => $round,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }


  public function getRound(int $id)
  {
    $id = filter_var($id, FILTER_VALIDATE_INT);
    $round = $this->DB->rowObj("SELECT $this->columns,
      MAX(next.id) AS next,
      MAX(prev.id) AS prev,
      COUNT(DISTINCT D.id) AS deaths,
      COUNT(DISTINCT T.id) as tickets
      FROM tbl_round
      LEFT JOIN tbl_round AS next ON next.id = tbl_round.id + 1
      LEFT JOIN tbl_round AS prev ON prev.id = tbl_round.id - 1 
      LEFT JOIN tbl_death AS D ON D.round_id = tbl_round.id
      LEFT JOIN tbl_ticket AS T ON T.round_id = tbl_round.id AND T.action = 'Ticket Opened'
      WHERE tbl_round.id = ?
      AND tbl_round.shutdown_datetime IS NOT NULL", $id);
    $round = $this->roundModel->parseRound($round);
    $url = parent::getFullURL($this->router->pathFor('round.single', ['id' => $round->id]));
    $this->breadcrumbs[$round->id] = $url;
    $this->ogdata['url'] = $url;
    $this->ogdata['title'] = "Round #$round->id on $round->server";
    $this->ogdata['description'] = "A round of $round->mode on $round->map that lasted $round->duration and ended with $round->result and $round->deaths deaths.";
    return $round;
  }

  public function listLogs($request, $response, $args)
  {
    $round = $this->getRound($args['id']);
    if (isset($request->getQueryParams()['format'])) {
      $format = htmlspecialchars($request->getQueryParams()['format']);
    }
    $logs = (new LogsController($this->container, $round))->listing();

    if (count($logs) == 0) {
      return $this->view->render($response, 'base/error.tpl', [
        'code' => 404,
        'link' => $this->router->pathFor('round.single', [
          'id' => $round->id
        ]),
        'linkText' => "Back to round " . $round->id,
        'message' => "Logfiles not found for round $round->id",
      ]);
    }

    if ('json' === $format) {
      return $response->withJson($logs);
    }

    $url = parent::getFullURL($this->router->pathFor('round.logs', ['id' => $round->id]));

    $this->breadcrumbs['Logs'] = $url;

    $this->ogdata['url'] = $url;
    $this->ogdata['title'] = "Log listing for round #$round->id on $round->server";
    $this->ogdata['description'] = (($logs) ? count($logs) : 0) . " log files available";

    return $this->view->render($response, 'rounds/logs.tpl', [
      'round' => $round,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata,
      'logs' => $logs
    ]);
  }
  public function getLogFile($request, $response, $args)
  {
    $round = $this->getRound($args['id']);
    $file = htmlspecialchars($args['file']);
    $format = '';
    if (isset($args['format'])) {
      $format = htmlspecialchars($args['format']);
    }
    $logs = (new LogsController($this->container, $round))->getFile($file, $format);

    if ($format === 'json') {
      return $response->withJson($logs);
    }

    $this->breadcrumbs['Logs'] = parent::getFullURL($this->router->pathFor('round.logs', [
      'id' => $round->id,
    ]));

    $url = parent::getFullURL($this->router->pathFor('round.log', [
      'id' => $round->id,
      'file' => $file
    ]));

    $this->breadcrumbs[$file] = $url;

    $this->ogdata['url'] = $url;
    $this->ogdata['title'] = "$file logfile for Round #$round->id on $round->server";
    #$this->ogdata['description'] = (($logs) ? count($logs) : 0)." lines found in $file.";
    if (!$logs) {
      return $this->view->render($response, 'base/error.tpl', [
        'code' => 404,
        'link' => $this->router->pathFor('round.logs', [
          'id' => $round->id
        ]),
        'linkText' => "Back to logs for round " . $round->id,
        'message' => "Logfile not found: $file",
      ]);
    }
    return $this->view->render($response, 'rounds/log.tpl', [
      'round' => $round,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata,
      'file' => $logs,
      'filename' => $file,
      'format' => $format,
      'wide' => true
    ]);
  }

  public function getGameLogs($request, $response, $args)
  {
    $file = 'game.log';
    $round = $this->getRound($args['id']);
    if (isset($args['page'])) {
      $this->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    $round->page = $this->page;
    $this->pages = 1;
    $logs = (new LogsController($this->container, $round))->getGameLogs();
    $round->pages = (new LogsController($this->container, $round))->getPages();

    if (!$logs) {
      return $this->view->render($response, 'base/error.tpl', [
        'message' => "Alt DB not configured. No parsed logs available",
        'code' => 500,
        'linkText' => "Back",
        'link' => parent::getFullURL($this->router->pathFor(
          'round.single',
          [
            'id' => $round->id,
          ]
        ))
      ]);
    }

    $url = parent::getFullURL($this->router->pathFor('round.log', [
      'id' => $round->id,
      'file' => $file
    ]));

    $this->breadcrumbs['Logs'] = parent::getFullURL($this->router->pathFor('round.logs', ['id' => $round->id]));

    $this->breadcrumbs['Parsed Game Logs'] = $url;

    return $this->view->render($response, 'rounds/log.tpl', [
      'round' => $round,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata,
      'file' => $logs,
      'filename' => $file,
      'wide' => true
    ]);
  }

  public function getRoundsWithTestmerge($request, $response, $args)
  {
    $pr = 0;
    $data = new \stdClass;
    $data->page = 1;

    if (isset($args['page'])) {
      $data->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    if (isset($args['pr'])) {
      $pr = filter_var($args['pr'], FILTER_VALIDATE_INT);
    }

    $data->pages = ceil($this->DB->cell("SELECT count(tbl_feedback.id) FROM tbl_feedback WHERE key_name = 'testmerged_prs' AND JSON_CONTAINS(JSON_EXTRACT(tbl_feedback.json, '$.data.*.number'), JSON_QUOTE(?), '$')", $pr) / $this->per_page);

    if ($data->pages == 0) {
      return $this->view->render($response, 'base/error.tpl', [
        'message' => "PR #$pr not found.",
        'code' => 404,
        'linkText' => "Back to all testmerges",
        'link' => parent::getFullURL($this->router->pathFor(
          'stat.collate',
          [
            'stat' => 'testmerged_prs',
            'version' => 2
          ]
        ))
      ]);
    }

    $rounds = $this->DB->run("SELECT $this->columns,
      tbl_feedback.json as prdata
      FROM tbl_feedback
      LEFT JOIN tbl_round ON tbl_feedback.round_id = tbl_round.id
      WHERE key_name = 'testmerged_prs'
      AND JSON_CONTAINS(JSON_EXTRACT(tbl_feedback.json, '$.data.*.number'), JSON_QUOTE(?), '$')
      ORDER BY round_id DESC
      LIMIT ?,?", $pr, ($this->page * $this->per_page) - $this->per_page, $this->per_page);
    foreach ($rounds as &$round) {
      $round = $this->roundModel->parseRound($round);
    }

    $data->prdata = json_decode($rounds[0]->prdata, true)['data'];

    $data->last_date = $rounds[0]->initialize_datetime;
    $data->first_date = end($rounds)->initialize_datetime;
    $data->first_round = $rounds[0]->id;
    $data->last_round = end($rounds)->id;

    $data->prdata = current(array_filter($data->prdata, function ($v) use ($pr) {
      return $v["number"] == (string) $pr;
    }));

    $data->rounds = $rounds;


    $format = null;
    if (isset($request->getQueryParams()['format'])) {
      $format = htmlspecialchars($request->getQueryParams()['format']);
    }
    if ($format === 'json') {
      return $response->withJson($data);
    }

    $this->breadcrumbs = [];
    $this->breadcrumbs['Testmerges'] = $this->router->pathFor(
      'stat.collate',
      [
        'stat' => 'testmerged_prs',
        'version' => 2
      ]
    );
    $this->breadcrumbs[$pr] = $this->router->pathFor(
      'stat.testmerge',
      [
        'pr' => $pr
      ]
    );


    $this->ogdata['title'] = "Testmerge Rounds for PR #$pr";
    $this->ogdata['description'] = $data->prdata["title"] . " testmerged in " . count($rounds) . " rounds.";

    return $this->view->render($response, 'stats/testmerge.tpl', [
      'data' => $data,
      'rounds' => $rounds,
      'round' => $this,
      'wide' => true,
      'pr' => $pr,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function getRoundsWithStat($request, $response, $args)
  {
    if (isset($args['page'])) {
      $this->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    if (isset($args['stat'])) {
      $stat = htmlspecialchars($args['stat']);
    }
    $version = 1;
    if (isset($args['version'])) {
      $version = filter_var($args['version'], FILTER_VALIDATE_INT);
    }
    $this->pages = ceil($this->DB->cell("SELECT count(tbl_feedback.id) FROM tbl_feedback WHERE key_name = ? AND version = ?", $stat, $version) / $this->per_page);
    $rounds = $this->DB->run("SELECT $this->columns
      FROM tbl_feedback
      LEFT JOIN tbl_round ON tbl_feedback.round_id = tbl_round.id
      WHERE key_name = ?
      AND version = ?
      ORDER BY round_id DESC
      LIMIT ?,?", $stat, $version, ($this->page * $this->per_page) - $this->per_page, $this->per_page);
    foreach ($rounds as &$round) {
      $round = $this->roundModel->parseRound($round);
    }
    return $this->view->render($response, 'stats/rounds.tpl', [
      'rounds' => $rounds,
      'round' => $this,
      'wide' => true,
      'stat' => $stat,
      'version' => $version,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function getRoundsForCkey($ckey)
  {
    $this->pages = ceil($this->DB->cell("SELECT count(tbl_round.id) FROM tbl_connection_log
        LEFT JOIN tbl_round ON tbl_connection_log.round_id = tbl_round.id
        WHERE tbl_connection_log.ckey = ?
        AND tbl_round.shutdown_datetime IS NOT NULL", $ckey) / $this->per_page);
    $rounds = $this->DB->run("SELECT $this->columns
      FROM tbl_connection_log
      LEFT JOIN tbl_round ON tbl_connection_log.round_id = tbl_round.id
      WHERE tbl_connection_log.ckey = ?
      AND tbl_round.shutdown_datetime IS NOT NULL
      ORDER BY tbl_connection_log.`datetime` DESC
      LIMIT ?,?", $ckey, ($this->page * $this->per_page) - $this->per_page, $this->per_page);
    foreach ($rounds as &$round) {
      $round = $this->roundModel->parseRound($round);
    }
    return $rounds;
  }

  public function getMyRounds($request, $response, $args)
  {
    if (isset($args['page'])) {
      $this->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    $user = $this->container->get('user');
    $rounds = $this->getRoundsForCkey($user->ckey);
    return $this->view->render($response, 'me/rounds.tpl', [
      'rounds' => $rounds,
      'round' => $this,
      'wide' => true,
      'breadcrumbs' => $this->breadcrumbs,
      'ogdata' => $this->ogdata
    ]);
  }

  public function getPlayerRounds($request, $response, $args)
  {
    if (isset($args['page'])) {
      $this->page = filter_var($args['page'], FILTER_VALIDATE_INT);
    }
    if (isset($args['ckey'])) {
      $ckey = htmlspecialchars($args['ckey']);
    }
    $rounds = $this->getRoundsForCkey($ckey);
    $this->breadcrumbs = [
      $ckey => parent::getFullURL($this->router->pathFor('player.single', [
        'ckey' => $ckey
      ])),
      'Rounds' => parent::getFullURL($this->router->pathFor('player.rounds', [
        'ckey' => $ckey
      ]))
    ];
    return $this->view->render($response, 'player/rounds.tpl', [
      'rounds' => $rounds,
      'round' => $this,
      'wide' => true,
      'breadcrumbs' => $this->breadcrumbs,
      'ckey' => $ckey,
      'ogdata' => $this->ogdata
    ]);
  }

  public function getActiveRounds($request, $response, $args)
  {
    $user = $this->container->get('user');
    if (!$user->userCanAccessTGDB) {
      return false;
    }
    $rounds = $this->DB->run("SELECT DISTINCT(*) FROM tbl_round WHERE `end_state` IS NULL LIMIT 0, 4 ORDER BY id DESC");
    return $request->withJson($rounds);
  }

  public function winLoss($request, $response, $args)
  {
    $p = $request->getQueryParams();
    $start = null;
    $end = null;
    if (isset($p['start']) && isset($p['end'])) {
      $start = filter_var(
        $p['start'],
        FILTER_SANITIZE_STRING,
        FILTER_FLAG_STRIP_HIGH
      );
      $end = htmlspecialchars($p['end']);
    }
    $minmax = $this->DB->rowObj("SELECT 
      min(STR_TO_DATE(R.initialize_datetime, '%Y-%m-%d')) AS min,
      max(STR_TO_DATE(R.shutdown_datetime, '%Y-%m-%d')) AS max
      FROM tbl_round AS R
      WHERE R.shutdown_datetime != '0000-00-00 00:00:00'
      AND R.initialize_datetime != '0000-00-00 00:00:00'");
    if (!$start) {
      $start = $minmax->min;
      $end = $minmax->max;
    } else {
      $startDate = new DateTime($start);
      $start = $startDate->format('Y-m-d');
      $endDate = new DateTime($end);
      $end = $endDate->format('Y-m-d');
    }
    $data = $this->getWinLossRatios($start, $end);
    return $this->view->render($response, 'info/winloss.tpl', [
      'modes' => $data,
      'start' => $start,
      'end' => $end,
      'min' => $minmax->min,
      'max' => $minmax->max,
      'ogdata' => $this->ogdata
    ]);
  }

  public function getWinLossRatios($start = null, $end = null)
  {
    $data = $this->DB->run("SELECT count(tbl_round.id) AS rounds,
        tbl_round.game_mode,
        tbl_round.game_mode_result,
        FLOOR(AVG(TIMESTAMPDIFF(MINUTE, tbl_round.start_datetime, tbl_round.end_datetime))) AS duration
        FROM tbl_round
        WHERE tbl_round.game_mode IS NOT NULL
        AND tbl_round.game_mode != 'undefined'
        AND tbl_round.game_mode_result IS NOT NULL
        AND tbl_round.game_mode_result != 'undefined'
        AND tbl_round.initialize_datetime BETWEEN ? AND ?
        AND tbl_round.shutdown_datetime IS NOT NULL
        GROUP BY tbl_round.game_mode, tbl_round.game_mode_result
        ORDER BY tbl_round.game_mode ASC, rounds DESC;", $start, $end);
    usort($data, function ($a, $b) {
      return strcmp($a->game_mode, $b->game_mode);
    });
    return $data;
  }
}
