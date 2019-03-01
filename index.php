<?php
require 'vendor/autoload.php';
if(isset($_GET['cep'])){
	header('Content-Type: text/json; charset=UTF-8');
	$endereco = \Correios\CEP::buscar(preg_replace('/[^0-9]/', '', $_GET['cep']));
	die(json_encode($endereco ? $endereco[0] : null));
}elseif(isset($_GET['endereco'])){
	header('Content-Type: text/json; charset=UTF-8');
	die(json_encode(\Correios\CEP::buscar($_GET['endereco'])));
}elseif(isset($_GET['codigo_rastreio'])){
	header('Content-Type: text/json; charset=UTF-8');
	die(json_encode(\Correios\Rastreio::localizar($_GET['codigo_rastreio'])));
}
header('Content-type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Non-oficial Correios API</title>
</head>
<body>
	<h1>API não oficial dos Correios</h1>
	<p>Servidor rest para web scrap desenvolvido em PHP para consulta de cep, enderço, e encomenda direto no site dos Correios.</p>

	<h2>Consulta de CEP</h2>
	<p>Envia-se o <code>string</code> do CEP desejado e é retornado um objeto com as propriedades do endereço. <br>Caso não seja localizado, será retornado <code>null</code></p>
	<pre>curl -X GET http://127.0.0.1/?cep=01001001</pre>
	<pre>{
    "bairro": "S\u00e9\u00a0",
    "cep": "01001-001",
    "cidade": "S\u00e3o Paulo",
    "logradouro": "Pra\u00e7a da S\u00e9 - lado par\u00a0",
    "uf": "SP\u00a0"
}</pre>

	<h2>Pesquisa de endereço</h2>
	<p>Envia-se a <code>string</code> do endereço a ser procurado e será retornado uma lista de objetos de endereço.<br> Caso não há haja resultados a resposta será uma lista vazia.</p>
	<pre>curl -X GET http://127.0.0.1/?endereco=av%20paulista</pre>
	<pre>[
    {
        "bairro": "Alvorada\u00a0",
        "cep": "45820-839",
        "cidade": "Eun\u00e1polis",
        "logradouro": "Avenida Paulista\u00a0",
        "uf": "BA\u00a0"
    },
    {
        "bairro": "Calabet\u00e3o\u00a0",
        "cep": "41227-025",
        "cidade": "Salvador",
        "logradouro": "Avenida Paulista\u00a0",
        "uf": "BA\u00a0"
    },
    ...,
    {
        "bairro": "Jardim Panorama\u00a0",
        "cep": "13504-654",
        "cidade": "Rio Claro",
        "logradouro": "Avenida Paulista - de 2500/2501 a 2998/2999\u00a0",
        "uf": "SP\u00a0"
    }
]
</pre>
	<h2>Rastreamento de encomendas</h2>
	<pre>curl -X GET http://127.0.0.1/?codigo_rastreio=OG490654336BR</pre>
	<pre>[
    {
        "cidade": "RIO DE JANEIRO",
        "data": "2019-03-01 01:57:00",
        "mensagem": "Objeto encaminhado de Unidade de Tratamento em RIO DE JANEIRO / RJ para Unidade de Distribui\u00e7\u00e3o em Rio De Janeiro / RJ",
        "status": "Objeto encaminhado",
        "uf": "RJ"
    },
    {
        "cidade": "RIO DE JANEIRO",
        "data": "2019-02-28 17:50:00",
        "mensagem": "Objeto encaminhado de Ag\u00eancia dos Correios em RIO DE JANEIRO / RJ para Unidade de Tratamento em RIO DE JANEIRO / RJ",
        "status": "Objeto encaminhado",
        "uf": "RJ"
    },
    {
        "cidade": "RIO DE JANEIRO",
        "data": "2019-02-28 17:15:00",
        "mensagem": "Objeto postado",
        "status": "Objeto postado",
        "uf": "RJ"
    }
]</pre>

</body>
</html>
