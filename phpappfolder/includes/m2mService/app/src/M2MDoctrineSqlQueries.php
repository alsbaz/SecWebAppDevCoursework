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
            ->select('m2m_pass_hash', 'm2m_id', 'm2m_email')
            ->from('m2m_users')
            ->where('m2m_username = ' . $queryBuilder->createNamedParameter($cleaned_param));

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }

    public static function queryStoreM2mMessages($queryBuilder, $params)
    {
        $store_result = [];
//var_dump($params);
        foreach ($params as $param) {
            $queryBuilder = $queryBuilder->insert('m2m_messages')
                ->values([
                    'sourcemsisdn' => ':source',
                    'destinationmsisdn' => ':destination',
                    'receivedtime' => ':time',
                    'bearer' => ':bearer',
                    'username' => ':username',
                    'email' => ':email',
                    'message_content' => ':content',
                ])
                ->setParameters([
                    ':source' => $param['sourcemsisdn'],
                    ':destination' => $param['destinationmsisdn'],
                    ':time' => $password = $param['receivedtime'],
                    ':bearer' => $param['bearer'],
                    ':username' => $param['username'],
                    ':email' => $param['email'],
                    ':content' => $param['message_content'],
                ]);

            $store_result['outcome'] = $queryBuilder->execute();
            $store_result['sql_query'] = $queryBuilder->getSQL();
        }
//var_dump($store_result);
        return $store_result;
    }

    public static function  queryRetrieveM2mMessages($queryBuilder, array $cleaned_param)
    {
        $retrieve_result = [];


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
}