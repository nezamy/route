<?php declare(strict_types=1);
! defined('DS') && define('DS', DIRECTORY_SEPARATOR);

//=============[ pre() is print array ]==============
function pre($arr, $title = null, $theme = 1)
{
    ob_start();
    switch ($theme) {
        case 2: $color = '#e4e7e7'; $background = '#2295bc'; break;
        case 3: $color = '#064439'; $background = '#51bba8'; break;
        case 4: $color = '#efc75e'; $background = '#324d5b'; break;
        case 5: $color = '#000000'; $background = '#b1eea1'; break;
        case 6: $color = '#fff'; $background = '#e2574c'; break;
        default:    $color = '#2295bc'; $background = '#e4e7e7';
    }
    if ($title) { ?>
        <div style="border:1px solid rgba(0,0,0,0.1);border-bottom:0;color:#2e3436;position: relative;padding: 6px 40px;font-weight:500;font-family: Monaco,Consolas, 'Lucida Console',monospace;letter-spacing:1px;font-size:14px;display: inline-block;width: auto;left: 40px;bottom: -30px;"><?php echo $title; ?></div>
    <?php } ?>
    <pre style="direction: ltr;background:<?php echo $background; ?>;color:<?php echo $color; ?>;
        width: calc(100% - 122px);margin: 30px auto;overflow:auto;
        font-family: Monaco,Consolas, 'Lucida Console',monospace;font-size: 14px;padding: 20px;border: 1px solid rgba(0,0,0,0.1)"><?php print_r($arr); ?></pre>
    <?php
    response()->write(ob_get_clean());
}

function dpre()
{
    call_user_func_array('pre', func_get_args());
    response()->end(response()->body());
}

function container()
{
    return \Just\DI\Container::instance();
}

function response(): Just\Http\Response
{
    return container()->get(\Just\Http\Response::class);
}

function request(): Just\Http\Request
{
    return container()->get(\Just\Http\Request::class);
}

function auth(): Just\Http\Auth
{
    return container()->get(\Just\Http\Auth::class);
}
