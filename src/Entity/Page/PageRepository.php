<?php /** @noinspection PhpMultipleClassDeclarationsInspection */

namespace KikCMS\Entity\Page;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Page>
 */
class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function findByMenuIdentifier(string $identifier = 'main', ?int $maxLevel = null): array
    {
        if ( ! $menu = $this->findOneBy(['identifier' => $identifier])) {
            return [];
        }

        return $this->findByParent($menu, $maxLevel);
    }

    public function findByMenuId(int $id, ?int $maxLevel = null): array
    {
        if ( ! $menu = $this->find($id)) {
            return [];
        }

        return $this->findByParent($menu, $maxLevel);
    }

    public function findByParent(Page $parent, ?int $maxLevel = null): array
    {
        $startLevel = $parent->getParents() ? count($parent->getParents()) : 0;

        $qb = $this->createQueryBuilder('p');

        $qb->where('JSON_CONTAINS(p.parents, :value) = 1')->setParameter('value', $parent->getId());

        if ($maxLevel !== null) {
            $qb->andWhere('JSON_LENGTH(p.parents) <= :count')->setParameter('count', $startLevel + $maxLevel);
        }

        $qb->orderBy('p.display_order', 'ASC');

        return $qb->getQuery()->getResult();
    }
}
