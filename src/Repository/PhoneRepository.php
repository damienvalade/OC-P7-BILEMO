<?php

namespace App\Repository;

use App\Entity\Phone;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;


/**
 * @method Phone|null find($id, $lockMode = null, $lockVersion = null)
 * @method Phone|null findOneBy(array $criteria, array $orderBy = null)
 * @method Phone[]    findAll()
 * @method Phone[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhoneRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Phone::class);
    }
}
