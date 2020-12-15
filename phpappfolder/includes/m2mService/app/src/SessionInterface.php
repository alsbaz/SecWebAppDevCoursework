<?php

namespace M2mService;

interface SessionInterface
{
    public function setSessionVar($session_key, $session_value_to_set);

    public function getSessionVar($session_key);

    public function unsetSessionVar($session_key);

    public function setLogger();

}