<?php

namespace App\Repository;

use App\Entity\FormDefinition;
use App\Entity\FormSubmission;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormSubmission>
 */
class FormSubmissionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormSubmission::class);
    }

    public function save(FormSubmission $submission, bool $flush = true): FormSubmission
    {
        $this->getEntityManager()->persist($submission);

        if ($flush) {
            $this->getEntityManager()->flush();
        }

        return $submission;
    }

    public function buildSelectQuery(FormDefinition $formDefinition): QueryBuilder
    {
        return $this->createQueryBuilder('s')
            ->select('s', 'f')
            ->join('s.form', 'f')
            ->where('f.id = :form')
            ->setParameter('form', $formDefinition)
            ->orderBy('s.createdAt', 'DESC')
        ;
    }

    /**
     * Find submissions by IDs that belong to the given form
     *
     * @param array<int> $ids
     * @return list<FormSubmission>
     */
    public function findByIdsAndForm(array $ids, FormDefinition $formDefinition): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('s')
            ->where('s.id IN (:ids)')
            ->andWhere('s.form = :form')
            ->setParameter('ids', $ids)
            ->setParameter('form', $formDefinition)
            ->getQuery()
            ->getResult();
    }
}
