<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/**
 * ProjectServiceContainer.
 *
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class ProjectServiceContainer extends Container
{
    private $parameters;
    private $targetDirs = array();
    private $privates = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->services = $this->privates = array();
        $this->methodMap = array(
            'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => 'getTestServiceSubscriberService',
            'foo_service' => 'getFooServiceService',
        );

        $this->aliases = array();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->privates = array();
        parent::reset();
    }

    /**
     * {@inheritdoc}
     */
    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    /**
     * {@inheritdoc}
     */
    public function isCompiled()
    {
        return true;
    }

    /**
     * Gets the public 'Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber' shared service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getTestServiceSubscriberService()
    {
        return $this->services['Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber();
    }

    /**
     * Gets the public 'foo_service' shared autowired service.
     *
     * @return \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber
     */
    protected function getFooServiceService()
    {
        return $this->services['foo_service'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber(new \Symfony\Component\DependencyInjection\ServiceLocator(array('Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\CustomDefinition' => function (): ?\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition {
            return ($this->privates['Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] ?? ($this->privates['Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition()));
        }, 'Symfony\\Component\\DependencyInjection\\Tests\\Fixtures\\TestServiceSubscriber' => function (): \Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber {
            return ($this->services['Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] ?? $this->getTestServiceSubscriberService());
        }, 'bar' => function (): \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition {
            return ($this->services['Symfony\Component\DependencyInjection\Tests\Fixtures\TestServiceSubscriber'] ?? $this->getTestServiceSubscriberService());
        }, 'baz' => function (): ?\Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition {
            return ($this->privates['Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] ?? ($this->privates['Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition'] = new \Symfony\Component\DependencyInjection\Tests\Fixtures\CustomDefinition()));
        })));
    }
}
