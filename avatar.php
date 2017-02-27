<?php

if (empty($_GET['profile'])) {
    return;
}

$profileID = $_GET['profile'];
$fn = dirname(__FILE__).'/cache/'.$profileID.'.jpg';
if (!file_exists($fn)) {
    file_put_contents($fn, file_get_contents(sprintf('https://pbfa.memberclicks.net/membership/profile/%s/avatar.jpg', $profileID)));
}

header('Content-Type: image/jpeg');
echo file_get_contents($fn);

// If older than an hour, remove it.
if (filemtime($fn)+(3600*24) < time()) {
    unlink($fn);
}
