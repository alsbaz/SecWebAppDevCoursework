<?php


namespace M2mService;


class M2MMessageHandler
{
    public function __construct() {}

    public function __destruct() {}

    public function splitMessageRegex($messages)
    {
        $pattern = "~(\<sourcemsisdn\>|\<destinationmsisdn\>|\<receivedtime\>|\<bearer\>|\<unique_id\>|\<message_content\>)(.+?)(?=\<\/|$)~";
        $subject = $messages;
        $counter = 0;
        foreach ($messages as $message) {
            preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);
            foreach ($matches as $item) {
                $item[1] = preg_replace("(\<|\>|\/)", "", $item[1]);
                $result_array[$counter][$item[1]] = $item[2];
            }
            $counter += 1;
        }
        return $result_array;
    }
}