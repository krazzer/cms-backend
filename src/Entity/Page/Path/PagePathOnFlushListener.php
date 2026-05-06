<?php

namespace KikCMS\Entity\Page\Path;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use KikCMS\Entity\Page\Page;

/**
 * Doctrine listener for handling onFlush events related to the Page entity.
 *
 * This listener is responsible for updating entity paths and handling changes when
 * Page entities are inserted or updated. It uses the PathService to update the
 * path of the current entity and its children, if applicable.
 *
 * Responsibilities:
 * - For updated Page entities:
 *   - Checks if specific fields (slug or parents) have been modified.
 *   - Updates the path of the entity
 *   - Updates the paths of child entities
 * - For inserted Page entities:
 *   - Updates the path of the entity and computes its change set.
 */
#[AsDoctrineListener(event: Events::onFlush)]
readonly class PagePathOnFlushListener
{
    public function __construct(
        private PathService $pathService,
    ) {}

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em   = $args->getObjectManager();
        $uow  = $em->getUnitOfWork();
        $meta = $em->getClassMetadata(Page::class);

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if ( ! $entity instanceof Page) {
                continue;
            }

            $changeset = $uow->getEntityChangeSet($entity);

            if ( ! isset($changeset[Page::FIELD_SLUG]) && ! isset($changeset[Page::FIELD_PARENTS])) {
                continue;
            }

            $this->pathService->updatePath($entity);
            $uow->recomputeSingleEntityChangeSet($meta, $entity);

            $children = $this->pathService->updateChildren($entity);

            foreach ($children as $child) {
                $em->persist($child);
                $uow->recomputeSingleEntityChangeSet($meta, $child);
            }
        }
    }
}