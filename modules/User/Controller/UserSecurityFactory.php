<?php

namespace MyTravel\User\Controller;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\SecurityFactoryInterface;
use Symfony\Component\Security\Core\Authentication\Provider\UserAuthenticationProvider;

class UserSecurityFactory implements SecurityFactoryInterface {

  public function create(ContainerBuilder $container, $id, $config, $userProvider, $defaultEntryPoint) {
    $providerId = 'security.authentication.provider.auth.' . $id;
    $container
    ->setDefinition($providerId, new ChildDefinition(UserAuthenticationProvider::class))
    ->replaceArgument(0, new Reference($userProvider))
    ;

    $listenerId = 'security.authentication.listener.auth.' . $id;
    $listener = $container->setDefinition($listenerId, new ChildDefinition(WsseListener::class));

    return array($providerId, $listenerId, $defaultEntryPoint);
  }

  public function getPosition() {
    return 'pre_auth';
  }

  public function getKey() {
    return 'auth';
  }

  public function addConfiguration(NodeDefinition $node) {
    
  }

}
