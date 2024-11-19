<?php
return [
  'app_name' => $_ENV['APP'] ?: 'Statbus',
  'UA' => $_ENV['UA'] ?? null,
  'remote_log_src' => $_ENV['REMOTE_LOGS'] ?? null,
  'github' => $_ENV['GITHUB'] ?? null,
  'auth' => [
    'remote_auth' => $_ENV['REMOTE_AUTH'] ?? false,
    'client_id' => $_ENV['CLIENT_ID'] ?? null,
    'client_secret' => $_ENV['CLIENT_SECRET'] ?? null,
    'oauth_start' => 'oauth_create_session.php',
    'token_url' => 'oauth.php',
    'auth_session' => 'oauth_get_session_info.php'
  ],
  'ip_auth' => $_ENV['IP_AUTH'] ?? false,
  'ip_auth_days' => $_ENV['IP_AUTH_DAYS'] ?: 10,
  'perm_flags' => [
    'BUILD' => (1 << 0),
    'ADMIN' => (1 << 1),
    'BAN' => (1 << 2),
    'FUN' => (1 << 3),
    'SERVER' => (1 << 4),
    'DEBUG' => (1 << 5),
    'POSSESS' => (1 << 6),
    'PERMISSIONS' => (1 << 7),
    'STEALTH' => (1 << 8),
    'POLL' => (1 << 9),
    'VAREDIT' => (1 << 10),
    'SOUND' => (1 << 11),
    'SPAWN' => (1 << 12),
    'AUTOADMIN' => (1 << 13),
    'DBRANKS' => (1 << 14)
  ],
  'ranks' => [
    'Coder' => [
      'backColor' => '#31b626',
      'foreColor' => '#FFF',
      'icon' => 'code'
    ],
    'Admin' => [
      'backColor' => '#9b59b6',
      'foreColor' => '#FFF',
      'icon' => 'asterisk'
    ],
  ],
  'servers' => [
    [
      'port' => 1234,
      'name' => 'Server'
    ]
  ],
  'election_mode' => false,
  'election_officer' => $_ENV['ELECTION_OFFICER'] ?: false,
  'candidates' => ['marksuckerberg', 'thgvr'],
  'bug_reports' => $_ENV['BUG_REPORTS'] ?: false,
  'mode_icons' => [
    'Abduction' => 'street-view',
    'Ai Malfunction' => 'network-wired',
    'Arching Operation' => '',
    'Assimilation' => 'brain',
    'Blob' => 'cubes',
    'Changeling' => 'spider',
    'Clockwork Cult' => 'cog',
    'Clown Ops' => 'angry',
    'Cult' => 'book-dead text-danger',
    'Devil' => 'handshake',
    'Devil Agents' => 'handshake',
    'Double Agents' => 'user-ninja',
    'Dynamic Mode' => 'dumpster-fire',
    'Everyone Is The Traitor And Also' => '',
    'Extended' => 'user-astronaut',
    'Extended Events' => '',
    'Families' => '',
    'Gang War' => '',
    'Gang War No Security' => '',
    'Hand Of God' => '',
    'Infiltration' => 'user-tie',
    'Internal Affairs' => 'user-secret',
    'Jeffjeff' => '',
    'Just Fuck My Shit Up' => '',
    'Meteor' => 'meteor',
    'Monkey' => 'dizzy',
    'Nuclear Emergency' => 'bomb',
    'Overthrow' => 'frown-open',
    'Ragin\' Mages' => 'magic',
    'Revolution' => 'fist-raised',
    'Rod Madness' => 'slash',
    'Sandbox' => 'grin-stars',
    'Secret Extended' => 'bed',
    'Shadowling' => 'users',
    'Speedy_revolution' => '',
    'Traitor' => 'skull-crossbones',
    'Traitor+brothers' => 'user-injured',
    'Traitor+changeling' => 'user-astronaut',
    'Very Ragin\' Bullshit Mages' => 'cloud-sun',
    'Vigilante Gang War' => '',
    'Wizard' => 'hat-wizard'
  ]
];