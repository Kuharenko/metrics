<?php

require 'classes/Models/Metric.php';

class Metrics
{
    public static function update()
    {
        $params = $_SERVER;

        Metric::sync([
            'ip_address' => $params['REMOTE_ADDR'],
            'user_agent' => $params['HTTP_USER_AGENT'],
            'view_date' => date("Y-m-d H:i:s"),
            'page_url' => $params['HTTP_REFERER']
        ]);
    }
}