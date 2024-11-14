<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, public PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @param string|null $search
     * @param array $pagination
     * @param array $sort
     * @param array|null $filter
     * @return array
     */
    public function productSearch(?string $search=null, array $pagination, array $sort, array $filter=null)
    {
        $qb = $this->createQueryBuilder('p');

        if (!is_null($search)) {
            $qb->andWhere('p.name LIKE :search')
                ->orWhere('p.description LIKE :search')
                ->setParameter('search', '%'.$search.'%');
        }

        if (!is_null($filter) && !is_null($filter['operator'])) {
            $operator = match ($filter['operator']) {
                'EQ' => '=',
                'LT' => '<',
                'GT' => '>',
                'BW' => 'BETWEEN'
            };

            if ($filter['operator'] === 'BW') {
                if ($filter['min'] && $filter['max']) {
                    $qb->andWhere('p.' . $filter['column'] . ' BETWEEN :lower AND :upper')
                        ->setParameter('lower', min($filter['min'], $filter['max']))
                        ->setParameter('upper', max($filter['min'], $filter['max']));
                }
            } else {
                $qb->andWhere('p.' . $filter['column'] . ' ' . $operator . ' :value')
                    ->setParameter('value', $filter['value'] ?? null);
            }
        }

        $page = $pagination['page'];
        $limit = $pagination['limit'];

        $qb->orderBy('p.'.$sort['column'], $sort['order']);

        $paginationData = $this->paginator->paginate(
            $qb->getQuery(),
            $page,
            $limit
        );

        return [
          'pagination' => [
              'total_items' => $paginationData->getTotalItemCount(),
              'total_pages' => ceil($paginationData->getTotalItemCount() / $limit),
              'current_page' => $page,
              'limit' => $limit
              ],
            'items' => $paginationData->getItems()
        ];
    }
}
