<?php
require 'vendor/autoload.php';
use carono\company\Egrul;
$file = __DIR__.'/tmp/1145958066737_996516269149226.pdf';


//$info = \carono\company\Kontur::findCompanies('1027700132195');

$info = \carono\company\Kontur::findCompanyByOgrn('1027700132195');
var_dump($info);
//var_dump($info['companies']);
//$data = \carono\company\Kontur::getCompanyByOgrn($info['companies'][0]['ogrn']);
//print_r($data);


//$x = Egrul::get('1027700132195');
//$x = Egrul::parseFile($file);
//var_dump($x);