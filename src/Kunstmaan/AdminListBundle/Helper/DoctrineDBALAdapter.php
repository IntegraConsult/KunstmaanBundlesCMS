<?php

namespace Kunstmaan\AdminListBundle\Helper;

use Doctrine\DBAL\Query\QueryBuilder;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Exception\LogicException;

/**
 * DoctrineDBALAdapter.
 *
 * @author Michael Williams <michael@whizdevelopment.com>
 *
 * @api
 */
class DoctrineDBALAdapter implements AdapterInterface
{
    private $queryBuilder;

    private $countField;

    private $useDistinct;

    /**
     * @var mixed|string
     */
    private $databasePlatform;

    /**
     * @param QueryBuilder $queryBuilder a DBAL query builder
     * @param string       $countField   Primary key for the table in query. Used in count expression. Must include table alias
     * @param bool         $useDistinct  when set to true it'll count the countfield with a distinct in front of it
     *
     * @api
     */
    public function __construct(QueryBuilder $queryBuilder, $countField, $useDistinct = true, $databasePlatform = 'mysql')
    {
        if (strpos($countField, '.') === false) {
            throw new LogicException('The $countField must contain a table alias in the string.');
        }

        if (QueryBuilder::SELECT !== $queryBuilder->getType()) {
            throw new LogicException('Only SELECT queries can be paginated.');
        }

        $this->queryBuilder = $queryBuilder;
        $this->countField = $countField;
        $this->useDistinct = $useDistinct;
        $this->databasePlatform = $databasePlatform;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder the query builder
     *
     * @api
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getNbResults()
    {
        $query = clone $this->queryBuilder;
        $distinctString = '';
        if ($this->useDistinct) {
            $distinctString = 'DISTINCT ';
        }

        if ($this->databasePlatform == 'postgresql') {
            //for a count the order of the results  form the original query does does not matter
            // and in postgres it is even detrimental when used in conjunction with other functions such as CONCAT
            $query->resetQueryPart('orderBy');
            $statement = $query->select('COUNT(' . $distinctString . $this->countField . ') AS total_results')
                ->execute();
        } else {
            $statement = $query->select('COUNT(' . $distinctString . $this->countField . ') AS total_results')
                ->orderBy($this->countField)
                ->setMaxResults(1)
                ->execute();
        }

        return ($results = $statement->fetchColumn()) ? $results : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getSlice($offset, $length)
    {
        $query = clone $this->queryBuilder;

        $result = $query->setMaxResults($length)
            ->setFirstResult($offset)
            ->execute();

        return $result->fetchAll();
    }
}
