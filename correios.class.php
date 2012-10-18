<?php
	include('phpQuery-onefile.php');
	

class Correios {
	
	static public function cep($cep){
		/*
			consulta realizada na versão mobile dos sites dos correios
			pode-se buscar CEPs ou Endereços
		*/
		//capituramos o HTML através da chamada cURL, enviando os parametros necessários.
		$html = self::simple_curl('http://m.correios.com.br/movel/buscaCepConfirma.do',array(
			'cepEntrada'=>$cep,
			'tipoCep'=>'',
			'cepTemp'=>'',
			'metodo'=>'buscarCep'
		));
		//die($html);
		//fazemos o phpQuery ler o HTML capiturado
		phpQuery::newDocumentHTML($html, $charset = 'utf-8');


		$dados = array();
		$c = 0;
		$t = count(pq('.caixacampobranco'));
		foreach(pq('.caixacampobranco') as $tr){
				$dados[$c] = 
				array(
					'cliente'=> trim(pq('.caixacampobranco .resposta:contains("Cliente: ") + .respostadestaque:eq(0)')->html()),
					'endereco'=> trim(pq('.caixacampobranco .resposta:contains("Endereço: ") + .respostadestaque:eq(0)')->html()),
					'logradouro'=> trim(pq('.caixacampobranco .resposta:contains("Logradouro: ") + .respostadestaque:eq(0)')->html()),
					'bairro'=> trim(pq('.caixacampobranco .resposta:contains("Bairro: ") + .respostadestaque:eq(0)')->html()),
					'cidade/uf'=> trim(pq('.caixacampobranco .resposta:contains("Localidade") + .respostadestaque:eq(0)')->html()),
					'cep'=> trim(pq('.caixacampobranco .resposta:contains("CEP: ") + .respostadestaque:eq(0)')->html())
				);

				$dados[$c]['cidade/uf'] = explode('/',$dados[$c]['cidade/uf']);
				$dados[$c]['cidade'] = trim($dados[$c]['cidade/uf'][0]);
				$dados[$c]['uf'] = trim($dados[$c]['cidade/uf'][1]);
				unset($dados[$c]['cidade/uf']);
				if($dados[$c]['endereco']){
					$dados[$c]['logradouro'] = $dados[$c]['endereco'];
				}
				unset($dados[$c]['endereco']);
		}
		//A pedido de Michel Isoton e Luciano Oliveira Borges agora além de busca de CEP a busca pode ser feita por endereço, 
			//para manter compatibilidade com versões anteriores é feito a validação a baixo para transformar em array de objeto ou objeto unico.
		if(count($dados)){
			$cep = str_replace('-','',trim($cep));
			if(8 === strlen($cep) && is_numeric($cep)){
				$dados = $dados[0];
			}
		}
		
		return $dados;
	}
	
	/*
		foi substituido pela self::cep($cep);
		para adequar a ceps que retorne o campo cliente. Ex: 12230-901;
		Bug reportado por:
		Leonardo Augusto Testoni Robles leoimortal@gmail.com;
		Versão backup caso os Correios alterem o funcionamento;
	static public function cepDesktop($cep){

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
		//die(pq('.ctrlcontent table')->html());
		foreach(pq('.ctrlcontent table tr') as $tr){
			if($c > 1 && $c < ((int)$t - 1)){
				//echo pq($tr)->html();
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
		//die();
		if(count($dados)){
			//A pedido de Michel Isoton e Luciano Oliveira Borges agora aléde busca de CEP a busca pode ser feita por endereço
			$cep = str_replace('-','',trim($cep));
			if(8 === strlen($cep) && is_numeric($cep)){
				$dados = $dados[0];
			}
		}
		
		return $dados;
	}
	*/
	
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