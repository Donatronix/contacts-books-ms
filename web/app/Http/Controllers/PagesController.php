<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class PagesController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $routeCollection = Route::getRoutes();

        $urls = [];

        foreach ($routeCollection as $value) {
            $uri = strstr($value['uri'], '/tests/');

            if ($uri) {
                $urls[] = $uri;
            }
        }

        sort($urls);

        $group = "&nbsp;";
        $subgroup = "&nbsp;";

        $re2 = '/\/tests\/contacts\/([^\/]+)/u';

        $re3 = '/\/tests\/contacts\/[^\/]+\/([^\/]+)(\/[^\/]+)?/u';

        $new_urls = [];

        foreach ($urls as $uri) {
            preg_match($re2, $uri, $m);
            if (isset($m[1])) {
                $new_group = $m[1];
                if ($new_group != $group) {
                    $new_urls[] = "h2$new_group";
                    $group = $new_group;
                }
            } else
                continue;

            if (preg_match($re3, $uri, $m)) {
                $new_subgroup = $m[1];

                if ($new_subgroup != $subgroup) {
                    if (
                        ($new_subgroup != 'store')
                        && ($new_subgroup != 'show')
                        && ($new_subgroup != 'index')
                        && ($new_subgroup != 'update')
                        && ($new_subgroup != 'destroy')
                    ) {
                        $new_urls[] = "h3$new_subgroup";
                    } else {
                        $new_urls[] = "-";
                    }
                    $subgroup = $new_subgroup;
                }
            }

            $new_urls[] = $uri;
        }

        return View::make('tests.index', ['urls' => $new_urls]);
    }
}
