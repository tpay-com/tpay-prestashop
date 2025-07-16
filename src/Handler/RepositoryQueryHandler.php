<?php

/**
 * NOTICE OF LICENSE
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 * You must not modify, adapt or create derivative works of this source code
 *
 * @author    Tpay
 * @copyright 2010-2022 tpay.com
 * @license   LICENSE.txt
 */

declare(strict_types=1);

namespace Tpay\Handler;

use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;

class RepositoryQueryHandler
{
    /**
     * @param QueryBuilder $qb
     * @param string $errorPrefix
     * @param string $type
     *
     * @return Statement|int
     * @throws BaseException
     * @throws RepositoryException
     */
    public function execute(QueryBuilder $qb, string $errorPrefix = 'SQL error', string $type = '')
    {
        try {
            if (method_exists($qb, 'executeQuery')) {
                switch ($type) {
                    case 'fetchColumn':
                        $statement = $qb->executeQuery()->fetchOne();
                        break;
                    case 'fetchAll':
                        $statement = $qb->executeQuery()->fetchAllAssociative();
                        break;
                    case 'fetch':
                        $statement = $qb->executeQuery()->fetchAssociative();
                        break;
                    default:
                        $statement = $qb->executeStatement();
                }
            } else {
                switch ($type) {
                    case 'fetchColumn':
                        $statement = $qb->execute()->fetchColumn();
                        break;
                    case 'fetchAll':
                        $statement = $qb->execute()->fetchAll();
                        break;
                    case 'fetch':
                        $statement = $qb->execute()->fetch();
                        break;
                    default:
                        $statement = $qb->execute();
                }
            }
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }

        if (method_exists(Statement::class, 'errorInfo')) {
            if ($statement instanceof Statement && !empty($statement->errorInfo())) {
                \PrestaShopLogger::addLog($errorPrefix, 3);
                throw new RepositoryException($errorPrefix . ': ' . var_export($statement->errorInfo(), true));
            }
        }

        return $statement;
    }
}
