<?php

require 'vendor/autoload.php';
include 'funciones.php';
$client = Elasticsearch\ClientBuilder::create()->build();
$palabraInicial=strtolower($argv[1]);
$cantidadPalabrasMostrar=$argv[2];
$cantidadPalabrasExtendida=$argv[3];
print_r("Palabra inicial: ".$palabraInicial."\n");
print_r("Cantidad de palabras a mostrar: ".$cantidadPalabrasMostrar."\n");
print_r("Cantidad de palabras con las que hacer la consulta extendida: ".$cantidadPalabrasExtendida."\n");

$mapaPalabras = getPalabrasTweets($palabraInicial,$client);
$palabrasTratar = array();
$cont=0;
foreach($mapaPalabras as $key => $val){
	if($cont<$cantidadPalabrasMostrar){
		$palabrasTratar[$key] = sacarHits($palabraInicial,$key,$client);
		$cont= $cont + 1;
	}
	else{
		break;
	}
}
asort($palabrasTratar);
print_r("-----------------------------------------\n");
print_r("Palabra -> NGD\n");
print_r("-----------------------------------------\n");
foreach($palabrasTratar as $key => $val){
	print_r($key." -> ".$val."\n");
}

getPalabrasTweetsExtendida($palabraInicial,$palabrasTratar,$cantidadPalabrasExtendida,$client);
?>