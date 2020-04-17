<?php

namespace App\Repository;

use App\Entity\Client\Client;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /***
     * UserRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
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
     * @param $client
     * @param $user_id
     * @return mixed
     */
    public function getClientUsers($client, $user_id)
    {
        $qb =
            $this->createQueryBuilder('u')
                ->innerJoin('u.team', 't')
                ->where('t.client = :client')
                ->andWhere('u.id <> :user_id')
                ->setParameter('client', $client)
                ->setParameter('user_id', $user_id)
                ->orderBy('u.username', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Client $client
     * @return mixed
     */
    public function getClientEmployees(Client $client)
    {
        $qb = $this->createQueryBuilder('u')
                ->innerJoin('u.team', 't')
                ->where('t.client = :client')
                ->andWhere('u.id <> :user_id')
                ->setParameter('client', $client)
                ->setParameter('user_id', $client->getOwner()->getId())
                ->orderBy('u.username', 'ASC');

        $users = $qb->getQuery()->getResult();

        foreach ($users as $key => $user) {
            if (in_array('ROLE_OWNER', $user->getRoles())) {
                unset($users[$key]);
            }
        }

        return $users;
    }

    /**
     * @param $emailOrUsername
     * @return mixed|string
     */
    public function findUserByEmailOrUsername($emailOrUsername)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.email = :email')
            ->orWhere('u.username = :username')
            ->setParameter('email', $emailOrUsername)
            ->setParameter('username', $emailOrUsername);

        try {
            $result = $qb->getQuery()->getOneOrNullResult();
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }
}
