<?php

/**
 * This class focuses on the different SQL queries and makes sure
 * they are executed in a safe way.
 * Explanation of each query builder method can be found at
 * vendor/doctrine/dbal/lib/Doctrine/DBAL/Query/QueryBuilder.php
 */

namespace M2mService;

use M2mService\LoggerWrapper;
class M2MDoctrineSqlQueries
{
    public function __construct(){}

    public function __destruct(){}

    /**
     * @param object $queryBuilder Instance of doctrine query builder object
     * @param array $cleaned_params values we need to store
     * @return array
     * Builds query to store user data after registration.
     * Returns 0 if query failed, 1 on success and the SQL statement.
     */
    public static function queryStoreUserData($queryBuilder, array $cleaned_params)
    {
        $logger = new LoggerWrapper();
        $store_result = [];

        $queryBuilder = $queryBuilder->insert('m2m_users')
            ->values([
                'm2m_id' => ':id',
                'm2m_username' => ':username',
                'm2m_pass_hash' => ':pass',
                'm2m_email' => ':email',
            ])
            ->setParameters([
                ':id' => $cleaned_params['unique_id'],
                ':username' => $cleaned_params['username'],
                ':pass' => $password = $cleaned_params['password'],
                ':email' => $cleaned_params['email'],
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        $logger->logAction('Stored user data in database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $store_result;
    }

    /**
     * @param object $queryBuilder
     * @param array $cleaned_param
     * @return mixed
     * Retrieves user data from db to be handled on login attempt.
     * Use the create named parameter method to change variables into strings
     * acceptable by SQL.
     */
    public static function  queryRetrieveUserData($queryBuilder, $cleaned_param)
    {
        $logger = new LoggerWrapper();
        $queryBuilder
            ->select('m2m_pass_hash', 'm2m_id', 'm2m_email', 'm2m_admin')
            ->from('m2m_users')
            ->where('m2m_username = ' . $queryBuilder->createNamedParameter($cleaned_param));

        $query = $queryBuilder->execute();
        $logger->logAction('Retrieved user data database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $query->fetchAll();
    }

    /**
     * @param object $queryBuilder
     * @param array $params
     * @param object $queryBuilder2
     * @return array
     * Method to check if the current message has been stored.
     * If not stored, store it.
     * Used two instances of the query builder object for two consecutive queries.
     */
    public static function queryStoreM2mMessages($queryBuilder, $params, $queryBuilder2)
    {
        $logger = new LoggerWrapper();
        $result_stored = [];
        $counter = 0;

        foreach ($params as $param) {
            $queryBuilder
                ->select('message_content')
                ->from('m2m_messages')
                ->where('receivedtime = ' . $queryBuilder->createNamedParameter($param['receivedtime']) .
                    ' AND message_content = ' . $queryBuilder->createNamedParameter($param['message_content']));

            $query = $queryBuilder->execute();
            $result = $query->fetchAll();
            if($result == null) {
                $result_stored[$counter]['sourcemsisdn'] = $param['sourcemsisdn'];
                $result_stored[$counter]['receivedtime'] = $param['receivedtime'];
                $result_stored[$counter]['bearer'] = $param['bearer'];
                $result_stored[$counter]['username'] = $param['username'];
                $result_stored[$counter]['message_content'] = $param['message_content'];
                $counter += 1;
                $queryBuilder2 = $queryBuilder2->insert('m2m_messages')
                    ->values([
                        'sourcemsisdn' => ':source',
                        'receivedtime' => ':time',
                        'bearer' => ':bearer',
                        'username' => ':username',
                        'message_content' => ':content',
                    ])
                    ->setParameters([
                        ':source' => $param['sourcemsisdn'],
                        ':time' => $param['receivedtime'],
                        ':bearer' => $param['bearer'],
                        ':username' => $param['username'],
                        ':content' => $param['message_content'],
                    ]);

                $store_result['outcome'] = $queryBuilder2->execute();
                $store_result['sql_query'] = $queryBuilder2->getSQL();
            }
        }
        $logger->logAction('Stored m2m messages in database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $result_stored;
    }

    /**
     * @param object $queryBuilder
     * @param array $cleaned_param contains the search terms and values the user entered
     * @return mixed
     * This method dynamically changes the content of the WHERE clause depending on the values
     * passed into it. Used sprintf to allow placeholders and return the formatted string.
     * This method will have at least 1 parameter passed to it.
     */
    public static function  queryRetrieveM2mMessages($queryBuilder, array $cleaned_param)
    {
        $logger = new LoggerWrapper();
        $queryBuilder
            ->select('*')
            ->from('m2m_messages');

        if (count($cleaned_param) > 0) {
            $isFirst = true;
            foreach ($cleaned_param as $key => $value) {
                if ($isFirst) {
                    if ($key == 'receivedtime') {
                        $queryBuilder->where(sprintf('%1$s >= %2$s', $key, $queryBuilder->createNamedParameter($value)));
                    } else {
                        $queryBuilder->where(sprintf('%1$s = %2$s', $key, $queryBuilder->createNamedParameter($value)));
                    }
                    $isFirst = false;
                } elseif ($key == 'receivedtime') {
                    $queryBuilder->andWhere(sprintf('%1$s >= %2$s', $key, $queryBuilder->createNamedParameter($value)));
                } else {
                    $queryBuilder->andWhere(sprintf('%1$s = %2$s', $key, $queryBuilder->createNamedParameter($value)));
                }
            }
        }
        $query = $queryBuilder->execute();
        $logger->logAction('messages retrieved from database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $query->fetchAll();
    }

    /**
     * @param object $queryBuilder
     * @param array $array
     * @return mixed
     * Update the switch table in the db.
     * Only stores one value which is repeatedly updated.
     * More can be added in the case of more switchboards.
     */
    public static function queryUpdateM2mSwitch($queryBuilder, $array)
    {
        $logger = new LoggerWrapper();
        $queryBuilder = $queryBuilder->update('m2m_switch');
        foreach ($array as $key =>$value) {
            $queryBuilder->set($key, $queryBuilder->createNamedParameter($value));
        }
        $queryBuilder->where('switchboard_name = ' . $queryBuilder->createNamedParameter('main'));

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        $logger->logAction('m2m switch updated in database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $store_result;
    }

    /**
     * @param object $queryBuilder
     * @return mixed
     * Get switchboard status from db.
     * Doesn't get switchboard name, can be added if more switchboards are added.
     */
    public static function getSwitchboardState($queryBuilder)
    {
        $logger = new LoggerWrapper();
        $queryBuilder
            ->select('switch_timestamp', 'switch1', 'switch2', 'switch3', 'switch4', 'fan', 'heaterTemp', 'lastDigit')
            ->from('m2m_switch')
            ->where('switchboard_name = ' . $queryBuilder->createNamedParameter('main'));

        $query = $queryBuilder->execute();
        $logger->logAction('Switchboard state retrieved from database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $query->fetchAll();
    }

    /**
     * @param object $queryBuilder
     * @param int $limit specifies how many occurrences to pull from db
     * @return mixed
     * Pulls a set amount of messages, starting with the newest ones.
     * Use the received time value to determine newest message.
     */
    public static function queryRetrieveM2mMessagesLimit($queryBuilder, $limit)
    {
        $logger = new LoggerWrapper();
        $queryBuilder
            ->select('*')
            ->from('m2m_messages')
            ->orderBy('receivedtime', 'DESC')
            ->setMaxResults($limit);

        $query = $queryBuilder->execute();
        $logger->logAction('Retrieved m2m message limit from the database', $_SERVER['SERVER_ADDR'], 'INFO');

        return $query->fetchAll();
    }
}