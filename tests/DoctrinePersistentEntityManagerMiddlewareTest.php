<?php declare(strict_types=1);

namespace Arus\Middleware\Tests;

/**
 * Import classes
 */
use Arus\Middleware\DoctrinePersistentEntityManagerMiddleware;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Factory\ServerRequestFactory;
use Sunrise\Http\Router\RequestHandler;

/**
 * DoctrinePersistentEntityManagerMiddlewareTest
 */
class DoctrinePersistentEntityManagerMiddlewareTest extends TestCase
{

    /**
     * @var null|Container
     */
    private $container;

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $middleware = new DoctrinePersistentEntityManagerMiddleware($this->container);

        $this->assertInstanceOf(MiddlewareInterface::class, $middleware);
    }

    /**
     * @return void
     */
    public function testConstructorWithoutEntityManagerInContainer() : void
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->useAutowiring(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The DI container must contain the EntityManager');

        new DoctrinePersistentEntityManagerMiddleware($builder->build());
    }

    /**
     * @return void
     */
    public function testPreservingEntityManager() : void
    {
        $expectedEntityManager = $this->container->get(EntityManager::class);

        (new RequestHandler)
        ->add(new DoctrinePersistentEntityManagerMiddleware($this->container))
        ->handle((new ServerRequestFactory)->createServerRequest('GET', '/', []));

        $this->assertTrue($expectedEntityManager->isOpen());
        $this->assertSame($expectedEntityManager, $this->container->get(EntityManager::class));
    }

    /**
     * @return void
     */
    public function testReopeningEntityManager() : void
    {
        $closedEntityManager = $this->container->get(EntityManager::class);
        $closedEntityManager->close();

        (new RequestHandler)
        ->add(new DoctrinePersistentEntityManagerMiddleware($this->container))
        ->handle((new ServerRequestFactory)->createServerRequest('GET', '/', []));

        $reopenedEntityManager = $this->container->get(EntityManager::class);

        $this->assertFalse($closedEntityManager->isOpen());
        $this->assertTrue($reopenedEntityManager->isOpen());
        $this->assertNotSame($closedEntityManager, $reopenedEntityManager);
    }

    /**
     * @return void
     */
    protected function setUp()
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->useAutowiring(false);

        $this->container = $builder->build();

        $this->container->set(EntityManager::class, function () : EntityManager {
            $config = DoctrineSetup::createAnnotationMetadataConfiguration([__DIR__], true, null, null, false);

            // See the file "phpunit.xml.dist" in the package root
            return EntityManager::create(['url' => $_ENV['DATABASE_URL']], $config);
        });
    }

    /**
     * @return void
     */
    protected function tearDown()
    {
        if ($this->container->has(EntityManager::class)) {
            $entityManager = $this->container->get(EntityManager::class);
            $entityManager->getConnection()->close();
            $entityManager->close();
        }

        $this->container = null;
    }
}
