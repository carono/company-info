<?php
require 'vendor/autoload.php';


$info = \carono\company\Kontur::findCompanies('сбер');
//$info = \carono\company\Kontur::findCompanyByOgrn('1027700132195');

print_r($info);
