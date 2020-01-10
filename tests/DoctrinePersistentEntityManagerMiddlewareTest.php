<?php declare(strict_types=1);

namespace Arus\Middleware\Tests;

/**
 * Import classes
 */
use Arus\Middleware\DoctrinePersistentEntityManagerMiddleware;
use DI\Container;
use DI\ContainerBuilder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup as DoctrineSetup;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\MiddlewareInterface;
use Sunrise\Http\Factory\ServerRequestFactory;
use Sunrise\Http\Router\RequestHandler;
use RuntimeException;

/**
 * DoctrinePersistentEntityManagerMiddlewareTest
 */
class DoctrinePersistentEntityManagerMiddlewareTest extends TestCase
{

    /**
     * @return void
     */
    public function testConstructor() : void
    {
        $container = $this->createContainer('foo');
        $middleware = new DoctrinePersistentEntityManagerMiddleware($container, 'foo');

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

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The DI container must contain the EntityManager');

        new DoctrinePersistentEntityManagerMiddleware($builder->build());
    }

    /**
     * @return void
     */
    public function testPreservingEntityManager() : void
    {
        $container = $this->createContainer('foo');
        $expectedEntityManager = $container->get('foo');

        (new RequestHandler)
        ->add(new DoctrinePersistentEntityManagerMiddleware($container, 'foo'))
        ->handle((new ServerRequestFactory)->createServerRequest('GET', '/', []));

        $this->assertTrue($expectedEntityManager->isOpen());
        $this->assertSame($expectedEntityManager, $container->get('foo'));
    }

    /**
     * @return void
     */
    public function testReopeningEntityManager() : void
    {
        $container = $this->createContainer('foo');
        $closedEntityManager = $container->get('foo');
        $closedEntityManager->close();

        (new RequestHandler)
        ->add(new DoctrinePersistentEntityManagerMiddleware($container, 'foo'))
        ->handle((new ServerRequestFactory)->createServerRequest('GET', '/', []));

        $reopenedEntityManager = $container->get('foo');

        $this->assertFalse($closedEntityManager->isOpen());
        $this->assertTrue($reopenedEntityManager->isOpen());
        $this->assertNotSame($closedEntityManager, $reopenedEntityManager);
    }

    /**
     * @param string $entryName
     *
     * @return Container
     */
    private function createContainer(string $entryName) : Container
    {
        $builder = new ContainerBuilder();
        $builder->useAnnotations(false);
        $builder->useAutowiring(false);

        $container = $builder->build();

        $container->set($entryName, function () : EntityManagerInterface {
            $config = DoctrineSetup::createAnnotationMetadataConfiguration([__DIR__], true, null, null, false);

            // See the file "phpunit.xml.dist" in the package root
            return EntityManager::create(['url' => $_ENV['DATABASE_URL']], $config);
        });

        return $container;
    }
}
