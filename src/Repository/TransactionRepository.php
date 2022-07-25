<?php

namespace App\Repository;

use App\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Transaction>
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    public function add(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findUserTransactionsByFilters($user, array $filters): array
    {
        $query = $this->createQueryBuilder('transaction')
            ->leftJoin('transaction.Course', 'c')
            ->andWhere('transaction.userBilling = :id')
            ->setParameter('id', $user->getId())
            ->orderBy('transaction.$dateTimeTransaction', 'DESC');

        if ($filters['type']) {
            $query->andWhere('transaction.typeOperation = :typeOperation')
                ->setParameter('typeOperation', $filters['type']);
        }

        if ($filters['course_code']) {
            $query->andWhere('course.codeCourse = :codeCourse')
                ->setParameter('codeCourse', $filters['course_code']);
        }

        if ($filters['skip_expired']) {
            $query->andWhere('transaction.dateTimeEndTransaction IS NULL OR transaction.dateTimeEndTransaction >= :today')
                ->setParameter('today', new \DateTimeImmutable());
        }

        return $query->getQuery()->getResult();
    }

}