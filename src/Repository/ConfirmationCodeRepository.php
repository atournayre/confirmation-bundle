<?php

namespace Atournayre\Bundle\ConfirmationBundle\Repository;

use Atournayre\Bundle\ConfirmationBundle\Entity\ConfirmationCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConfirmationCode>
 *
 * @method ConfirmationCode|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConfirmationCode|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConfirmationCode[]    findAll()
 * @method ConfirmationCode[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConfirmationCodeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConfirmationCode::class);
    }

    public function save(ConfirmationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConfirmationCode $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
