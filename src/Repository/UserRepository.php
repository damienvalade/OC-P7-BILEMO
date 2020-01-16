<?php

namespace App\Repository;

use App\Entity\AccessToken;
use App\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param UserInterface $user
     * @param string $newEncodedPassword
     * @throws ORMException
     * @throws OptimisticLockException
     */

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    /**
     * @param AccessToken $client
     * @param string $order
     * @param int $limit
     * @param int $offset
     * @return Pagerfanta | bool
     */
    public function search(AccessToken $client, $order = 'asc', $limit = 20, $offset = 0)
    {

        $qb = $this->getUserInfo($client, $order);

        $paginate = $this->paginate($qb, $limit, $offset);

        if(empty($paginate->getNbResults())){
            return false;
        }
        return $this->paginate($qb, $limit, $offset);
    }

    /**
     * @param AccessToken $client
     * @param string $order
     * @return QueryBuilder
     */
    public function getUserInfo(AccessToken $client, string $order)
    {
        $clientObject = $client->getClient();

        return $this->createQueryBuilder('u')
            ->select('u')
            ->orderBy('u.username', $order)
            ->where('u.client = :client')
            ->setParameter('client', $clientObject)
            ;
    }

    /**
     * @param int $user
     * @return int
     */
    public function getContractedUserInfo(int $user)
    {
        return $this->createQueryBuilder('u')
            ->select('u.username, u.usernameCanonical, u.email, u.enabled, u.roles, u.lastLogin')
            ->where('u.id = :id')
            ->setParameter('id', $user)
            ->getQuery()
            ->getResult()
            ;
    }
}
