<?php

namespace App\Repository;

use App\Dto\FormEndpointDto;
use App\Entity\FormDefinition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FormDefinition>
 */
class FormDefinitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FormDefinition::class);
    }

    /**
     * @return FormEndpointDto[]
     */
    public function getEndpoints(): array
    {
        return $this->createQueryBuilder('f')
            ->select('NEW App\Dto\FormEndpointDto(f.id, f.name, f.enabled, COUNT(s.id) as submissionsCount)')
            ->leftJoin('f.submissions', 's')
            ->groupBy('f.id')
            ->getQuery()
            ->getResult();
    }
}
