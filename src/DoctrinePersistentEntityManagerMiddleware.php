<?php declare(strict_types=1);

/**
 * It's free open-source software released under the MIT License.
 *
 * @author Anatoly Fenric <anatoly@fenric.ru>
 * @copyright Copyright (c) 2019, Autorus Ltd.
 * @license https://github.com/autorusltd/doctrine-persistent-entity-manager-middleware/blob/master/LICENSE
 * @link https://github.com/autorusltd/doctrine-persistent-entity-manager-middleware
 */

namespace Arus\Middleware;

/**
 * Import classes
 */
use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * DoctrinePersistentEntityManagerMiddleware
 */
class DoctrinePersistentEntityManagerMiddleware implements MiddlewareInterface
{

    /**
     * The dependency injection container
     *
     * @var Container
     */
    private $container;

    /**
     * Constructor of the class
     *
     * @param Container $container
     *
     * @throws RuntimeException If the given DI container does not contain the EntityManager
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        if (! $this->container->has(EntityManagerInterface::class)) {
            throw new RuntimeException('The DI container must contain the EntityManager');
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $entityManager = $this->container->get(EntityManagerInterface::class);

        if (! $entityManager->isOpen()) {
            $this->container->set(EntityManagerInterface::class, function () use ($entityManager) {
                return EntityManager::create(
                    $entityManager->getConnection(),
                    $entityManager->getConfiguration(),
                    $entityManager->getConnection()->getEventManager()
                );
            });
        }

        return $handler->handle($request);
    }
}
