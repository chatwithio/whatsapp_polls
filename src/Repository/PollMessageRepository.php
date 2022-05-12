<?php

namespace App\Repository;

use App\Entity\PollMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PollMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method PollMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method PollMessage[]    findAll()
 * @method PollMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PollMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PollMessage::class);
    }

    // /**
    //  * @return PollMessage[] Returns an array of PollMessage objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PollMessage
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getExpiredMessages(){
        return $this->createQueryBuilder('p')
            ->andWhere('p.messagesent != true')
            ->andWhere('p.created <= :val')
            ->setParameter('val', new \DateTime ('-10 seconds')) //comprobar
            ->getQuery()
            ->execute()
        ;
    }

    public function checkSentMessage($value, $valuepoll) {

         $result = $this->createQueryBuilder('p')
        ->andWhere('p.wa_id = :val')
        ->setParameter('val', $value)
        ->andWhere('p.pollid = :valpoll')
        ->setParameter('valpoll', $valuepoll)    
        ->orderBy('p.created', 'DESC') 
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult()
        ;

        //dump($result);
        return $result->getMessageSent();
        
    }
}
