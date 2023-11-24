<?php

require_once('entity_manager.php');

function log_event($user, $activity)
{
    $data = [
        'user_id' => $user,
        'activity' => $activity
    ];
    create('audit_log', $data);
}

?>