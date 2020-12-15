<?php
    header("Content-Type: text/plain");

    define("USER",     "p3tuser");
    define("PASSWORD", "p3tpassword");
    define("DATABASE", "mysql:host=localhost;dbname=p3ttestdb");
    define("QUERY",    'SELECT column1, NOW() as "now" FROM message');

    try
    {
        echo("Connection parameters: \n");
        echo("  database = [" . DATABASE . "]\n");
        echo("      user = [" . USER . "]\n");
        echo("  password = [**********]\n");

        echo("Attempting to connect using PDO ...\n");
        $myPDOobject = new PDO(DATABASE, USER, PASSWORD);

        echo("Attempting to run query [" . QUERY . "] ...\n");
        $resultStatement = $myPDOobject->query(QUERY);

        echo("Retrieving the query result ... \n");
        $resultArray = $resultStatement->fetch(PDO::FETCH_ASSOC);

        echo("The returned text is [" . $resultArray['column1'] . "]\n");
        echo("Query run at [" . $resultArray['now'] . "]\n");

        echo("Releasing PDO resources ...\n");
        $myPDOobject = null;
    }
    catch (PDOException $e)
    {
        echo("Error occurred, details as follows~~~~~~~~~~~~~~~~:\n");
        echo( $e->getMessage() . "\n");
        echo("End of error details~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~\n");
    }