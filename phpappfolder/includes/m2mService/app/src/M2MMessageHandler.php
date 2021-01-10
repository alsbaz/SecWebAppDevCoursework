<?php


namespace M2mService;


class M2MMessageHandler
{
    public function __construct() {}

    public function __destruct() {}

    /**
     * @param array $messages contains all the unhandled messages returned from the soap call.
     * @return null
     * Handles the splitting up of messages by patterns, using regex.
     */
    public function splitMessageRegex($messages)
    {
        // passes all the messages to be sorted by the ID
        $messages_sorted = $this->sortUniqueId($messages);

        // the patterns looks for these strings, saves the pattern and the following string until </ or end of line
        $pattern = "~(\<sourcemsisdn\>|\<receivedtime\>|\<bearer\>|\<message_content\>|\<username\>)(.+?)(?=\<\/|$)~";
        $subject = $messages_sorted;
        $counter = 0;
        $result_array = null;
        foreach ($subject as $message) {
            // remove any new line characters, as they break the storage
            $message = preg_replace('/\s+/', " ", $message);
            // looks for the pattern in message, saves the matches
            preg_match_all($pattern, $message, $matches, PREG_SET_ORDER);
            foreach ($matches as $item) {
                // sanitises tags and saves the matches to a multi-dimensional array
                $item[1] = preg_replace("(\<|\>|\/)", "", $item[1]);
                $result_array[$counter][$item[1]] = $item[2];
            }
            // sends each array to method bellow
            $result_array[$counter] = $this->sortDateFormat($result_array[$counter]);
            // this is to handle if someone sends an empty message somehow
            if($result_array[$counter]['message_content'] == '</message_content>') unset($result_array[$counter]['message_content']);
            $counter += 1;
        }
        return $result_array;
    }

    /**
     * @param array $messages_unsorted contains all messages not handled yet
     * @return array
     * Filters out all the messages not containing the specified ID.
     */
    private function sortUniqueId($messages_unsorted)
    {
        $messages_sorted = [];
        $counter = 0;
        foreach ($messages_unsorted as $message) {
            // uses regex to look for the ID
            if(preg_match("~(\<id\>20\-3110\-AD)~", $message) == 1){
                // if ID is found in the string, the whole string is added to the array of messages
                $messages_sorted[$counter] = $message;
                $counter += 1;
            }
        }
        // array of messages containing ID is returned
        return $messages_sorted;
    }

    /**
     * @param array $arrayWithDate
     * @return mixed
     * Takes the date value and reformats it to work with the SQL date format.
     */
    private function sortDateFormat($arrayWithDate)
    {
        // replace / character with -
        $arrayWithDate['receivedtime'] = preg_replace("(\/)", "-", $arrayWithDate['receivedtime']);
        // makes sure that the format of the date and time is correct
        $dateReformat = \DateTime::createFromFormat('d-m-Y H:i:s', $arrayWithDate['receivedtime']);
        $arrayWithDate['receivedtime'] = $dateReformat->format('Y-m-d H:i:s');
        // return the array with updated date
        return $arrayWithDate;
    }
}