<?php

//$a = "/[^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]/";
$name = 'bob89fdasFS_fad';

//$result = preg_match($a, $b);

//echo $result;
echo strlen($name);
if (preg_match("[^(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$]",$name) && strlen($name)<16)
{
    echo 'yes';
}
else
    {
        echo 'no';
    }