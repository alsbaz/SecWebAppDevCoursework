<?php


namespace M2mService;


class M2MDoctrineSqlQueries
{
    public function __construct(){}

    public function __destruct(){}

    public static function queryStoreUserData($queryBuilder, array $cleaned_params) //$hashed_password
    {
        $store_result = [];
//        $id = session_id();
//        $id = $_SESSION['unique_id']

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
//var_dump($store_result);
        return $store_result;
    }

    public static function  queryRetrieveUserData($queryBuilder, $cleaned_param)
    {
        $retrieve_result = [];

        $queryBuilder
            ->select('m2m_pass_hash', 'm2m_id', 'm2m_email', 'm2m_admin')
            ->from('m2m_users')
            ->where('m2m_username = ' . $queryBuilder->createNamedParameter($cleaned_param));

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }

    public static function queryStoreM2mMessages($queryBuilder, $params, $queryBuilder2)
    {
        $result_stored = [];
        $counter = 0;
//var_dump($params);
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
                        //                    'destinationmsisdn' => ':destination',
                        'receivedtime' => ':time',
                        'bearer' => ':bearer',
                        'username' => ':username',
//                        'email' => ':email',
                        'message_content' => ':content',
                    ])
                    ->setParameters([
                        ':source' => $param['sourcemsisdn'],
                        //                    ':destination' => $param['destinationmsisdn'],
                        ':time' => $param['receivedtime'],
                        ':bearer' => $param['bearer'],
                        ':username' => $param['username'],
//                        ':email' => $param['email'],
                        ':content' => $param['message_content'],
                    ]);

                $store_result['outcome'] = $queryBuilder2->execute();
                $store_result['sql_query'] = $queryBuilder2->getSQL();
            }
        }
//var_dump($store_result);
        return $result_stored;
    }

    public static function  queryRetrieveM2mMessages($queryBuilder, array $cleaned_param)
    {
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

//        $store_result['outcome'] = $queryBuilder->execute();
//        $store_result['sql_query'] = $queryBuilder->getSQL();
//var_dump($store_result);

        return $query->fetchAll();
    }

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

    public static function getSwitchboardState($queryBuilder)
    {
        $queryBuilder
            ->select('switch_timestamp', 'switch1', 'switch2', 'switch3', 'switch4', 'fan', 'heaterTemp', 'lastDigit')
            ->from('m2m_switch')
            ->where('switchboard_name = ' . $queryBuilder->createNamedParameter('main'));

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }


    public static function queryRetrieveM2mMessagesLimit($queryBuilder, $limit)
    {
        $queryBuilder
            ->select('*')
            ->from('m2m_messages')
            ->orderBy('receivedtime', 'DESC')
            ->setMaxResults($limit);

        $query = $queryBuilder->execute();
//var_dump($query);

        return $query->fetchAll();
    }
}