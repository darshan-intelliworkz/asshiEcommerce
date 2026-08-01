<?php
$log = file_get_contents('c:\xampp\htdocs\asshiEcommerce\storage\logs\laravel.log');
$errors = explode('[202', $log);
$last = end($errors);
echo substr($last, 0, 1500);
