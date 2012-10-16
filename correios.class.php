<?php
	include('phpQuery-onefile.php');
	

class Correios {
	
	static public function cep($cep){
		//capituramos o HTML através da chamada cURL, enviando os parametros necessários.
		$html = self::simple_curl('http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do',array(
			'Metodo'=>'listaLogradouro',
			'TipoConsulta'=>'relaxation',
			'StartRow'=>'1',
			'EndRow'=>'10',
			'relaxation'=>$cep
		));

		//fazemos o phpQuery ler o HTML capiturado
		phpQuery::newDocumentHTML($html, $charset = 'utf-8');


		$dados = array();
		$c = 0;
		$t = count(pq('.ctrlcontent table tr'));
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
		if(count($dados)){
			//A pedido de Michel Isoton e Luciano Oliveira Borges agora aléde busca de CEP a busca pode ser feita por endereço
			$cep = str_replace('-','',trim($cep));
			if(8 === strlen($cep) && is_numeric($cep)){
				$dados = $dados[0];
			}
		}
		
		return $dados;
	}
	
	static public function rastreio($codigo){
		$html = self::simple_curl('http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$codigo);
		phpQuery::newDocumentHTML($html, $charset = 'utf-8');

		$rastreamento = array();
		$c = 0;
		foreach(pq('tr') as $tr){$c++;
			if(count(pq($tr)->find('td')) == 3 && $c > 1)
				$rastreamento[] = array('data'=>pq($tr)->find('td:eq(0)')->text(),'local'=>pq($tr)->find('td:eq(1)')->text(),'status'=>pq($tr)->find('td:eq(2)')->text());
		}
		if(!count($rastreamento))
			return false;
		return $rastreamento;
	}
	
	static public function simple_curl($url,$post=array(),$get=array()){
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
	
}