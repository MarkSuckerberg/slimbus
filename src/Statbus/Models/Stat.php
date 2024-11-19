<?php

namespace Statbus\Models;

use DateInterval;
use DatePeriod;
use DateTime;
use stdClass;

class Stat
{

  private $filters;

  public function __construct()
  {
    $this->filters = json_decode(file_get_contents(ROOTDIR . "/src/conf/stat_filters.json"));
  }

  public function parseStat(&$stat, $collate = false)
  {
    if (!$collate) {
      return $this->singleParse($stat);
    } else {
      foreach ($stat as &$s) {
        $s = $this->singleParse($s);
      }
      $stat = $this->collate($stat);
      return $stat;
    }
  }

  public function singleParse(&$stat)
  {
    if (isset($this->filters->{$stat->key_name}->label->filter)) {
      $stat->label = $this->filters->{$stat->key_name}->label;
      $stat->json = str_replace($stat->label->filter, '', $stat->json);
    }
    $stat->data = json_decode($stat->json, TRUE)['data'];
    $stat = $this->specialCases($stat);
    $stat->output = $stat->data;
    switch ($stat->key_type) {
      case 'associative':
        break;

      case 'amount':
        break;

      case 'nested tally':
        break;

      case 'tally':
        $stat->total = array_sum($stat->data);
        $stat->output = arsort($stat->output);
        break;
    }
    return $stat;
  }

  public function collate(&$stat)
  {
    $tmp = new stdclass;
    $tmp->collated = TRUE;
    $tmp->key_name = $stat[0]->key_name;
    $tmp->key_type = $stat[0]->key_type;
    $tmp->version = $stat[0]->version;
    $tmp->first_date = $stat[0]->datetime;
    $tmp->last_date = end($stat)->datetime;
    $tmp->first_round = $stat[0]->round_id;
    $tmp->last_round = end($stat)->round_id;

    $tmp->rounds = [];
    $tmp->dates = [];
    $tmp->js = [];

    if (isset($this->filters->{$tmp->key_name}->label)) {
      $tmp->label = $this->filters->{$tmp->key_name}->label;
    }

    $a = new DatePeriod(
      new DateTime($stat[0]->datetime),
      new DateInterval('P1D'),
      new DateTime(end($stat)->datetime)
    );

    foreach ($a as $key => $value) {
      $tmp->dates[$value->format('Y-m-d')] = 0;
    }

    switch ($tmp->key_type) {
      case 'text':
        foreach ($stat as $s) {
          $tmp->rounds[$s->round_id] = true;

          if (!is_array($s->data)) {
            $tmp->output[] = $s->data;
            continue;
          }

          foreach ($s->data as $k) {
            $tmp->output[] = $k;
          }
        }

        $tmp->output = array_unique($tmp->output);
        break;

      case 'associative':
        $data = [];
        foreach ($stat as $s) {
          $tmp->rounds[$s->round_id] = count($s->data);
          foreach ($s->data as $x => $y) {
            $data[] = $y;
          }
        }
        $tmp->data = array_unique($data, SORT_REGULAR);
        $tmp->output = $tmp->data;
        break;

      case 'amount':
        $tmp->output = 0;
        foreach ($stat as $s) {
          $tmp->output += $s->data;
          $tmp->rounds[$s->round_id] = $s->data;
          $tmp->dates[(new DateTime($s->datetime))->format('Y-m-d')] += $s->data;
        }
        foreach ($tmp->dates as $k => $v) {
          $tmp->js[] = [
            'x' => $k,
            'y' => $v
          ];
        }
        break;

      case 'nested tally':
        $data = [];
        foreach ($stat as $s) {
          $tmp->rounds[] = $s->round_id;
          foreach ($s->data as $name => $v) {
            foreach ($v as $key => $value) {
              if ($data[$name][$key]) {
                $data[$name][$key] += $value;
                continue;
              }

              $data[$name][$key] = $value;
            }
          }
        }

        arsort($data, SORT_NUMERIC);
        $tmp->data = $data;
        $tmp->output = $data;
        break;

      case 'tally':
        $data = [];
        foreach ($stat as $s) {
          $tmp->rounds[$s->round_id] = $s->data;
          foreach ($s->data as $k => $v) {
            if ($data[$k]) {
              $data[$k] += $v;
              continue;
            }

            $data[$k] = $v;
            $tmp->total += $v;
          }
        }
        arsort($data, SORT_NUMERIC);
        $tmp->data = $data;
        break;
    }
    return $tmp;
  }

  public function specialCases(&$stat)
  {
    switch ($stat->key_name) {
      case 'commendation':
        foreach ($stat->data as &$d) {
          $d['id'] = strtoupper(substr(hash('sha512', $d['commendee'] . $d['reason']), 0, 6));
        }
        break;

      case 'time_dilation_current':
        $stat->chartdata = new stdClass;
        foreach ($stat->data as &$d) {
          $current = current($d);
          $key = key($d);
          $d = $current;
          $d['datetime'] = $key;

          $stat->chartdata->datetimes[] = $key;
          $stat->chartdata->avg[] = $d['avg'];
          $stat->chartdata->current[] = $d['current'];

        }
        $stat->chartdata = json_encode($stat->chartdata);
        break;

      case 'testmerged_prs':
        $stat->data = array_map("unserialize", array_unique(array_map("serialize", $stat->data)));
        break;
    }

    return $stat;
  }

}
