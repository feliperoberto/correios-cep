<?php 
	header('Content-Type: text/html; charset=UTF-8');
/*
Até pouco tempo, tinhamos um site onde podiamos fazer consultas via get, ou até baixar a base de dados utilizada de forma gratuita. Mas seu maior problema para todos que já utilizaram tal base é o fato desta ser muito antiga. Com isso surgia o problema de ruas e bairros com nomes errados ou até mesmo a não existentes.
O tempo passou, a base ficou ainda mais velha e o site passou a cobrar pelos serviços.
Mas não tem problema, os correios que antes tinham um sistema que não permitia se aproveitar dos dados (gerava as respostas em imagens), hoje nos disponibiliza uma versão mobile que facilmente nos permite tratar as respostas.

Para criarmos nosso próprio webservice em PHP vamos simular o comportamento realizado pelo site através de requisições cURL
*/



/*
Aqui incluimos biblioteca phpQuery (http://code.google.com/p/phpquery/
que permite manipular conteúdo html atrvés de seleções tipo jquery/css)
*/
include('phpQuery-onefile.php');

/*criar uma função para fazermos requisições via cURL para depois tratarmos com o phpQuery*/
function simple_curl($url,$post=array(),$get=array()){
	$url = explode('?',$url,2);
	if(count($url)===2){
		$temp_get = array();
		parse_str($url[1],$temp_get);
		$get = array_merge($get,$temp_get);
	}
	//die($url[0]."?".http_build_query($get));
	$ch = curl_init($url[0]."?".http_build_query($get));
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query($post));
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	return curl_exec ($ch);
}


//depois de ambiente preparado, mãos as teclas.

//aqui esta o cep a ser consultado
$cep = $_REQUEST['cep'];

/*
fazemos uma chamada POST direta aos correios
http://m.correios.com.br/movel/buscaCepConfirma.do

para entender, acesse a url pelo navegador e faça uma consulta.
nosso webservice fará o mesmo, mas atráves do servidor.
*/

//aqui capituramos o HTML através da chamada cURL, enviando os parametros necessários.
$html = simple_curl('http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do',array(
	'Metodo'=>'listaLogradouro',
	'TipoConsulta'=>'relaxation',
	'StartRow'=>'1',
	'EndRow'=>'10',
	'relaxation'=>$cep
));
//die(print_r($html,true));

//fazemos o phpQuery ler o HTML capiturado
phpQuery::newDocumentHTML($html, $charset = 'utf-8');

/*
aqui tratamos os dados com o phpQuery
a função pq() é equivalente ao $() do jQuery
$(#id elemento.classe).html() em jQuery é em PHP pq(#id elemento.classe)->html()
*/

//então temos nosso logradouro, bairro, cidade/uf e cep
$dados = array();
$c = 0;
$t = count(pq('.ctrlcontent table tr'));
//die(print_r($t,true));
foreach(pq('.ctrlcontent table tr') as $tr){
	if($c > 1 && $c < ((int)$t - 1)){
		$dados[] = array(
			'logradouro'=> trim(pq($tr)->find('td:eq(0)')->text()),
			'bairro'=> trim(pq($tr)->find('td:eq(1)')->text()),
			'cidade'=> trim(pq($tr)->find('td:eq(2)')->text()),
			'uf'=> trim(pq($tr)->find('td:eq(3)')->text()),
			'cep'=> trim(pq($tr)->find('td:eq(4)')->text())
		);
	}
	$c += 1;
}
//die(print_r($dados,true));
if(count($dados)){
	$cep = str_replace('-','',trim($cep));
	if(8 === strlen($cep) && is_numeric($cep)){
		$dados = $dados[0];
	}
}
die(json_encode($dados));