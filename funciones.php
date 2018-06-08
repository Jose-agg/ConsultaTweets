<?php
# ------------------------------------------------------------------ #
# -------------------------- FUNCIONES ----------------------------- #
# ------------------------------------------------------------------ #

# Comprobar si palabra es vacia o no
function isVacia($palabra)
{
	$palabrasVacias = array("&","^^","www", "http","com","","-","I","a","about","above","after","again","against","all","am","an","and","any","are","aren't","as","at","be","because","been","before","being","below","between","both","but","by","can't","cannot","could","couldn't","did","didn't","do","does","doesn't","doing","don't","down","during","each","few","for","from","further","had","hadn't","has","hasn't","have","haven't","having","he","he'd","he'll","he's","her","here","here's","hers","herself","him","himself","his","how","how's","i","i'd","i'll","i'm","i've","if","in","into","is","isn't","it","it's","its","itself","let's","me","more","most","mustn't","my","myself","no","nor","not","of","off","on","once","only","or","other","ought","our","ours","ourselves","out","over","own","same","shan't","she","she'd","she'll","she's","should","shouldn't","so","some","such","than","that","that's","the","their","theirs","them","themselves","then","there","there's","these","they","they'd","they'll","they're","they've","this","those","through","to","too","under","until","up","very","was","wasn't","we","we'd","we'll","we're","we've","were","weren't","what","what's","when","when's","where","where's","which","while","who","who's","whom","why","why's","with","won't","would","wouldn't","you","you'd","you'll","you're","you've","your","yours","yourself","yourselves");
	foreach ($palabrasVacias as $vacia){
		if($palabra==$vacia){
			return true;
		}
	}
    return false;
}

# Sacar las palabras que mas aparecen
function getPalabrasTweets($palabra,$client){
	# Consulta sobre palabra
	$consultaInicial=[
		'index'=>'2008-feb-02-04',
		'type' => 'tweet',
		'body' => [
			'from'=>0,
			'size'=>10000,
			'query'=>[
				'term'=>[
					'text'=> $palabra
				]
			]
		]
	];
	
	# JSON con todos los tweets
	$results = $client->search($consultaInicial);

	# Lista de JSON de cada tweet
	$allParams = $results['hits']['hits'];
	
	$textParam = array();
	foreach ($allParams as $value){
		array_push($textParam, $value['_source']['text']);
	}

	# Sacar la lista de palabras que hay en los tweets quitando los separadores
	$mapa= array();
	foreach ($textParam as $value){
		$split = preg_replace('/\b(https?):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i', '', $value);
		$splitValue = preg_split("/[\s,.,!,¡,?,¿,:,;,-,(,),\/,@,*]+/", $split);
		foreach ($splitValue as $var){
			$var=strtolower($var);
			if(!array_key_exists($var, $mapa) && !isVacia($var)){
				$mapa[$var] = 1;
			}
			else if (!isVacia($var)){
				$mapa[$var] = $mapa[$var]+1;
			}
			else{}
		}
	}
	arsort($mapa);
	return $mapa;
}

function sacarHits($palabraBasica, $palabraEncontrada,$client){
	$hitsPB = getHitsConsulta($palabraBasica, $client);
	$hitsPE = getHitsConsulta($palabraEncontrada, $client);
	
	$consultaComun=[
	'index'=>'2008-feb-02-04',
	'type' => 'tweet',
	'body' => [
		'from'=>0,
		'size'=>1000,
		'query'=>[
		    'match'=> [
		        'text'=>[
		          'query'=> $palabraBasica." ".$palabraEncontrada
		          , 'operator'=> 'and'
					]
				]
			]
		]
	];

	$results = $client->search($consultaComun);
	$hitsPC = $results['hits']['total'];
	$N = (($hitsPB + $hitsPE) - $hitsPC) * 35;
	return distanciaNGD($hitsPB, $hitsPE, $hitsPC, $N);
}

function getHitsConsulta($palabra,$client){
	$consulta=[
	'index'=>'2008-feb-02-04',
	'type' => 'tweet',
	'body' => [
		'from'=>0,
		'size'=>10000,
		'query'=>[
			'term'=>[
				'text'=> $palabra
				]
			]
		]
	];
	
	$results = $client->search($consulta);
	$hits = $results['hits']['total'];
	return $hits;
}

# Calcular la -Normalized Google distance-
function distanciaNGD($fx, $fy, $fxy, $N) {
	$ngd = (max(log($fx), log($fy)) - log($fxy)) / (log($N) - min(log($fx), log($fy)));
	return $ngd;
}

# Sacar las palabras que mas aparecen
function getPalabrasTweetsExtendida($palabra,$listaPalabras,$cantidadPalabrasExtendida,$client){
	
	# Consulta sobre palabra
	$listaPalabrasExtendidas ="";
	$contador = 0;
	print_r("------------------------------------------------------------------------------\n");
	print_r("La consulta con la palabra: ".$palabra." ha tenido ".getHitsConsulta($palabra, $client)." resultados.\n");
	foreach($listaPalabras as $key => $val){
		if($val>0 && $val<10 && $contador<$cantidadPalabrasExtendida){
			$listaPalabrasExtendidas=$listaPalabrasExtendidas." ".$key;
			hitsQueryExtendida($palabra,$listaPalabrasExtendidas,$client);
			$contador++;
		}
	}
}

function hitsQueryExtendida($palabra,$listaPalabrasExtendidas,$client){
	$consultaExtendida=[
	'index'=>'2008-feb-02-04',
	'type' => 'tweet',
	'body' => [
		'from'=>0,
		'size'=>1000,
		'query'=>[
		    'match'=> [
		        'text'=>[
		          'query'=> $palabra." ".$listaPalabrasExtendidas
		          , 'operator'=> 'or'
					]
				]
			]
		]
	];
	
	$results = $client->search($consultaExtendida);
	$hitsPCE = $results['hits']['total'];
	print_r("La consulta con las palabras: ".$palabra.$listaPalabrasExtendidas." ha tenido ".$hitsPCE." resultados.\n");
}	


?>