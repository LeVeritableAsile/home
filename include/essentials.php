<?php

if (!isset($home_url))
{
    $home_url_guess = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://').preg_replace('/:80$/', '', $_SERVER['HTTP_HOST']).str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

    if (substr($home_url_guess, -1) == '/')
        $home_url_guess = substr($home_url_guess, 0, -1);

    $home_url = $home_url_guess;
}

require 'functions.php'

?>
