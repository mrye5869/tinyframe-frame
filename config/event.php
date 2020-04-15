<?php
//应用事件

use og\facade\Env;
return [
    'create_path'   =>  Env::get('app_path').'event',
    'namespace'     => 'app\\event',
];


