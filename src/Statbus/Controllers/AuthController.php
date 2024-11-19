<?php

namespace Statbus\Controllers;
use Jumbojett\OpenIDConnectClient;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Statbus\Controllers\Controller as Controller;


class AuthController extends Controller
{

  private OpenIDConnectClient $oidc;


  public function __construct(ContainerInterface $container)
  {
    parent::__construct($container);

    $this->settings = $container->get('settings')['statbus']['auth'];

    if (!isset($this->settings['remote_auth'])) {
      return;
    }

    $this->oidc = new OpenIDConnectClient(
      $this->settings['remote_auth'],
      $this->settings['client_id'],
      $this->settings['client_secret']
    );
    //$this->oidc->setCertPath($this->settings['cert_path']);
  }

  public function auth($request, $response, $args)
  {
    if (!$this->settings['remote_auth']) {
      return $this->view->render($this->response, 'base/error.tpl', [
        'message' => "Authentication not supported",
        'code' => 501,
      ]);
    }

    return $this->view->render($response, 'auth/confirm.tpl');
  }

  public function auth_redirect($request, ResponseInterface $response, $args)
  {
    if (!$this->oidc->authenticate()) {
      //Authenticate() sets the location header to the redirect URI if false
      return $response->withStatus(302);
    }

    $ckey = $this->oidc->getVerifiedClaims('ckey');
    if (!isset($ckey)) {
      return $this->view->render($response, 'base/error.tpl', [
        'message' => 'Ckey verification failed.',
        'code' => 403
      ]);
    }

    echo $this->oidc->getVerifiedClaims();

    //Get a new ID when logging in
    regenerate_statbus_session();
    $_SESSION['ckey'] = $ckey;


    return $this->view->render($response, 'auth/return.tpl', [
      'return_uri' => $_SESSION['return_uri'] ?: false,
      'user' => $this->container->get('user'),
      'user_ckey' => $ckey
    ]);
  }

  public function logout($request, $response, $args)
  {
    destroy_statbus_session();
    return $this->view->render($response, 'auth/logout.tpl');
  }
}