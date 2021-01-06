<?php


namespace M2mService;


class M2MMessageHandler
{
    public function __construct() {}

    public function __destruct() {}

    public function splitMessageRegex($messages)
    {
        $messages_sorted = $this->sortUniqueId($messages);

        $pattern = "~(\<sourcemsisdn\>|\<receivedtime\>|\<bearer\>|\<message_content\>|\<username\>)(.+?)(?=\<\/|$)~";
        $subject = $messages_sorted;
        $counter = 0;
        $result_array = null;
        foreach ($subject as $message) {
            preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);
            foreach ($matches as $item) {
                $item[1] = preg_replace("(\<|\>|\/)", "", $item[1]);
                $result_array[$counter][$item[1]] = $item[2];
            }
            $result_array[$counter] = $this->sortDateFormat($result_array[$counter]);
            if($result_array[$counter]['message_content'] == '</message_content>') unset($result_array[$counter]['message_content']);
            $counter += 1;
        }
        return $result_array;
    }

    private function sortUniqueId($messages_unsorted)
    {
        $messages_sorted = [];
        $counter = 0;
        foreach ($messages_unsorted as $message) {
            if(preg_match("~(\<id\>20\-3110\-AD)~", $message) == 1){
                $messages_sorted[$counter] = $message;
                $counter += 1;
            }
        }
        return $messages_sorted;
    }

    private function sortDateFormat($arrayWithDate)
    {
        $arrayWithDate['receivedtime'] = preg_replace("(\/)", "-", $arrayWithDate['receivedtime']);
        $dateReformat = \DateTime::createFromFormat('d-m-Y H:i:s', $arrayWithDate['receivedtime']);
        $arrayWithDate['receivedtime'] = $dateReformat->format('Y-m-d H:i:s');
        return $arrayWithDate;
    }
}