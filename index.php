<?php

require_once 'settings.php';
require_once 'inc/include.php';
require_once 'inc/primitives.php';

Samara_Include('Controller', 'inc');
//$xslt = new XSLTProcessor();
//$xslt->importStylesheet(new SimpleXMLElement(file_get_contents('test.xsl')));
//print_r($_SERVER['REQUEST_URI']);//$xslt->transformToXml(new SimpleXMLElement(file_get_contents('test.xml')));

Controller::Render();
