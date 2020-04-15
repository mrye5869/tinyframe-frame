<?php
//应用中间件


use og\facade\Env;
return [
    'create_path'   =>  Env::get('app_path').'middleware',
    'namespace'     => 'app\\middleware',

];