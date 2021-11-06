<?php
/**
 * Plugin Name: Xoom Express
 */

if (!function_exists("add_action")) die();  // stop direct access

require __DIR__. "/php/class/Express.php";
Express::start();