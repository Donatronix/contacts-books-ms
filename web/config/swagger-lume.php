<?php

use Sumra\SDK\Helpers\Helper;

return (static function () {
    $settings = [
        //
    ];

    return array_merge(Helper::getConfig('swagger-lume'), $settings);
})();
