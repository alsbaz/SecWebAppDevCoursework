<?php
    header("Content-Type: text/plain");

    // define constants for this database / query
    define("USER",     "p3tuser");
    define("PASSWORD", "p3tpassword");
    define("HOST",     "localhost");
    define("PORT",     "3306");
    define("DATABASE", "p3ttestdb");
    define("QUERY",    "SELECT column1 FROM message");

    echo("Connection parameters: \n");
    echo("      host = [" . HOST . "]\n");
    echo("      port = [" . PORT . "]\n");
    echo("  database = [" . DATABASE . "]\n");
    echo("      user = [" . USER . "]\n");
    echo("  password = [**********]\n");

    echo("Connecting to mysql server\n");
    $connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE, PORT);

    echo("Checking the connection\n");
    if ($connection)
    {
        echo("Preparing the statement for the query [" . QUERY . "]\n");
        $statement = mysqli_prepare($connection, QUERY);

        echo("Checking the statement\n");
        if ($statement)
        {
            echo("Performing the query\n");
            mysqli_stmt_execute($statement);

            echo("Binding the query's result to php variable\n");
            mysqli_stmt_bind_result($statement, $resultColumnOne);

            echo("The query result is: \n");
            if (mysqli_stmt_fetch($statement))
            {
                echo("[". $resultColumnOne . "]\n");
            }

            echo("Closing the statement\n");
            mysqli_stmt_close($statement);
            echo("Statement closed.\n");
        }
        else
        {
            echo("Prepare statement failed: " . mysqli_connect_error() . "\n");
        }

        echo("Closing the connection\n");
        mysqli_close($connection);
        echo("Connection closed.\n");
    }
    else
    {
        echo("Connect failed: " . mysqli_connect_error() . "\n");
    }

    $now = date("Y-m-d H:i:s");
    echo("page produced: ".$now."\n");
