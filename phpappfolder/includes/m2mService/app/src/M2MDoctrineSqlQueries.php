<?php

/**
 * This class focuses on the different SQL queries and makes sure
 * they are executed in a safe way.
 */

namespace M2mService;


class M2MDoctrineSqlQueries
{
    public function __construct(){}

    public function __destruct(){}

    /**
     * @param instance $queryBuilder Instance of doctrine query builder
     * @param array $cleaned_params values we need to store
     * @return array
     * Builds query to store user data after registration.
     * Explanation of each query builder function can be found at
     * vendor/doctrine/dbal/lib/Doctrine/DBAL/Query/QueryBuilder.php
     */
    public static function queryStoreUserData($queryBuilder, array $cleaned_params)
    {
        $store_result = [];

        $queryBuilder = $queryBuilder->insert('m2m_users')
            //Passing in the values with placeholders, eliminating SQL
            //injection vulnerabilities.
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

        //execute returns 1 on success, 0 on failure.
        $store_result['outcome'] = $queryBuilder->execute();
        //getSQL returns the query which was executed.
        //Can be analyzed in a readable format.
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /**
     * @param instance $queryBuilder
     * @param array $cleaned_param
     * @return mixed
     * Retrieves user data from db to be handled on login attempt.
     */
    public static function  queryRetrieveUserData($queryBuilder, $cleaned_param)
    {
        $queryBuilder
            ->select('m2m_pass_hash', 'm2m_id', 'm2m_email', 'm2m_admin')
            ->from('m2m_users')
            //createNamedParameter is used to change variables into parameters
            //for the SQL code.
            ->where('m2m_username = ' . $queryBuilder->createNamedParameter($cleaned_param));

        $query = $queryBuilder->execute();

        //Return the results of the SQL command.
        return $query->fetchAll();
    }

    /**
     * @param instance $queryBuilder
     * @param array $params
     * @param instance $queryBuilder2
     * @return array
     * Method to check if the current message has been stored.
     * If not stored, store it.
     */
    public static function queryStoreM2mMessages($queryBuilder, $params, $queryBuilder2)
    {
        $result_stored = [];
        /** @param string $counter is used to keep track of which message we checking.
         *There can be many.
         */
        $counter = 0;

        //Cycle through the messages.
        foreach ($params as $param) {
            //Build a select statement to check if message has been saved before.
            $queryBuilder
                ->select('message_content')
                ->from('m2m_messages')
                ->where('receivedtime = ' . $queryBuilder->createNamedParameter($param['receivedtime']) .
                    ' AND message_content = ' . $queryBuilder->createNamedParameter($param['message_content']));

            $query = $queryBuilder->execute();
            $result = $query->fetchAll();
            //If nothing found, $result will be null.
            //For these, the second query builder is used to craft and execute a new SQL statement.
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
        //Only return the new values that have been stored, if no new messages,
        //return null.
        return $result_stored;
    }

    /**
     * @param instance $queryBuilder
     * @param array $cleaned_param contains the search terms and values the user entered
     * @return mixed
     * This method is dynamically changing depending what values
     * are passed to it.
     */
    public static function  queryRetrieveM2mMessages($queryBuilder, array $cleaned_param)
    {
        //Splitting the creation of the query in two.
        $queryBuilder
            ->select('*')
            ->from('m2m_messages');

        //Checking if more than 0 parameters are passed in array.
        if (count($cleaned_param) > 0) {
            //The first value is handled differently, so it is marked by $isFirst
            $isFirst = true;
            //Looping through each search term and grabbing the key and the value both.
            foreach ($cleaned_param as $key => $value) {
                //First parameter has to be added with the where method.
                if ($isFirst) {
                    //Special case for the timestamp
                    if ($key == 'receivedtime') {
                        //Tried out using sprintf, it replaces placeholders.
                        $queryBuilder->where(sprintf('%1$s >= %2$s', $key, $queryBuilder->createNamedParameter($value)));
                    } else {
                        $queryBuilder->where(sprintf('%1$s = %2$s', $key, $queryBuilder->createNamedParameter($value)));
                    }
                    $isFirst = false;
                //Rest of the values added with andWhere.
                } elseif ($key == 'receivedtime') {
                    $queryBuilder->andWhere(sprintf('%1$s >= %2$s', $key, $queryBuilder->createNamedParameter($value)));
                } else {
                    $queryBuilder->andWhere(sprintf('%1$s = %2$s', $key, $queryBuilder->createNamedParameter($value)));
                }
            }
        }
        $query = $queryBuilder->execute();

        //Return the results of the SQL command.
        return $query->fetchAll();
    }

    /**
     * @param instance $queryBuilder
     * @param array $array
     * @return mixed
     * Update the switch table in the db.
     * Only stores one value which is repeatedly updated.
     * More can be added in the case of more switchboards.
     */
    public static function queryUpdateM2mSwitch($queryBuilder, $array)
    {
        $queryBuilder = $queryBuilder->update('m2m_switch');
        foreach ($array as $key =>$value) {
            $queryBuilder->set($key, $queryBuilder->createNamedParameter($value));
        }
        $queryBuilder->where('switchboard_name = ' . $queryBuilder->createNamedParameter('main'));

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();
        return $store_result;
    }

    /**
     * @param instance $queryBuilder
     * @return mixed
     * Get switchboard status from db.
     */
    public static function getSwitchboardState($queryBuilder)
    {
        $queryBuilder
            ->select('switch_timestamp', 'switch1', 'switch2', 'switch3', 'switch4', 'fan', 'heaterTemp', 'lastDigit')
            ->from('m2m_switch')
            ->where('switchboard_name = ' . $queryBuilder->createNamedParameter('main'));

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }

    /**
     * @param instance $queryBuilder
     * @param int $limit specifies how many occurences to pull from db
     * @return mixed
     * Pulls a set amount of messages, starting with the newest ones.
     */
    public static function queryRetrieveM2mMessagesLimit($queryBuilder, $limit)
    {
        $queryBuilder
            ->select('*')
            ->from('m2m_messages')
            //We get the most recent messages by setting the order to be descending on timestamp.
            ->orderBy('receivedtime', 'DESC')
            //Restricts how many values to get.
            ->setMaxResults($limit);

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }
}