<?php

namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

class Paginator
{
    public function generate($query, int $limit, int $page = 1)
    {
        $paginator = new DoctrinePaginator($query);
        $total_pages = (int) ceil($paginator->count() / $limit);
        $page = $total_pages > 0 && $page > $total_pages ? $total_pages : $page;
        $paginator->getQuery()->setFirstResult($limit * ($page - 1))->setMaxResults($limit);

        return [
            'items' => $paginator,
            'total_pages' => $total_pages,
            'page' => $page
        ];
    }
}