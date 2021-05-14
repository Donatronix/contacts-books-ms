<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\View;

class TestController extends Controller
{
    public static function viewMake($view)
    {
        if (isset($_SERVER['HTTPS']))
            if ($_SERVER['HTTPS'] == '')
                $http = 'http';
            else
                $http = 'https';
        else
            $http = 'http';

        return View::make($view, [
            'host' => $_SERVER['HTTP_HOST'],
            'http' => $http
        ]);
    }
}
