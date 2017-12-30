<?php

namespace MyTravel\User\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserListener extends AbstractAuthenticationListener {

  protected $tokenStorage;
  protected $authenticationManager;

  public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager) {
    $this->tokenStorage = $tokenStorage;
    $this->authenticationManager = $authenticationManager;
  }

  public function handle(GetResponseEvent $event) {
    $request = $event->getRequest();

    // Get Data from session

    $token = new UsernamePasswordToken();
    $token->setUser($matches[1]);

    $token->digest = $matches[2];
    $token->nonce = $matches[3];
    $token->created = $matches[4];

    try {
      $authToken = $this->authenticationManager->authenticate($token);
      $this->tokenStorage->setToken($authToken);

      return;
    } catch (AuthenticationException $failed) {
      // ... you might log something here
      // To deny the authentication clear the token. This will redirect to the login page.
      // Make sure to only clear your token, not those of other authentication listeners.
      // $token = $this->tokenStorage->getToken();
      // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
      //     $this->tokenStorage->setToken(null);
      // }
      // return;
    }

    // By default deny authorization
    $response = new Response();
    $response->setStatusCode(Response::HTTP_FORBIDDEN);
    $event->setResponse($response);
  }
  
  protected function attemptAuthentication(Request $request) {
    ;
  }

}
