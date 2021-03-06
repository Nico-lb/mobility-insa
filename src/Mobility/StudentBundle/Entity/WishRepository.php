<?php

namespace Mobility\StudentBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * WishRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WishRepository extends EntityRepository {
    public function count() {
        $qb = $this->createQueryBuilder('w')
                ->select('count(w)');
        
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function maxChoices($year) {
        $qb = $this->createQueryBuilder('w')
                ->select('count(w)')
                ->leftJoin('w.student', 's')
                ->where('s.year = :year')
                ->setParameter('year', $year)
                ->groupBy('w.student');

		$res = $qb->getQuery()->getScalarResult();
		$max = 0;
		foreach ($res as $count) {
			if ($count[1] > $max) $max = $count[1];
		}

		return $max;
    }
}
