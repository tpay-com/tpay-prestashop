<?php

/**MIT License

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.*/

declare(strict_types=1);

namespace Tpay\Handler;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use Exception;
use PrestaShopLogger;
use Tpay\Exception\BaseException;
use Tpay\Exception\RepositoryException;

class RepositoryQueryHandler
{
    /**
     * @throws BaseException
     * @throws RepositoryException
     *
     * @return int|Statement
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
                        // @phpstan-ignore-next-line
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
        } catch (Exception $exception) {
            PrestaShopLogger::addLog($exception->getMessage(), 3);
            throw new BaseException($exception->getMessage());
        }

        // @phpstan-ignore-next-line
        if (method_exists(Statement::class, 'errorInfo')) {
            if ($statement instanceof Statement && !empty($statement->errorInfo())) {
                PrestaShopLogger::addLog($errorPrefix, 3);
                throw new RepositoryException($errorPrefix . ': ' . var_export($statement->errorInfo(), true));
            }
        }

        return $statement;
    }
}
