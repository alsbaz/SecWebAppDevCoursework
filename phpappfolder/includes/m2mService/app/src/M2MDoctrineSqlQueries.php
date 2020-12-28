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

        $queryBuilder = $queryBuilder->insert('m2m')
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
            ->select('m2m_pass_hash', 'm2m_id')
            ->from('m2m')
            ->where('m2m_username = ' . $queryBuilder->createNamedParameter($cleaned_param));

        $query = $queryBuilder->execute();

        return $query->fetchAll();
    }
}