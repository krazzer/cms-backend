<?php /** @noinspection PhpMultipleClassDeclarationsInspection */
// Todo: Vanwaar bovenstaande @noinspection gebruiken? Ik zie geen melding als ik die weghaal

namespace KikCMS\Entity\File;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

// Todo: Deze annotatie is overbodig
/**
 * @extends ServiceEntityRepository<File>
 */
class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }
}
