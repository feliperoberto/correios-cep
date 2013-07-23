<?php
class Correios {

	static public function frete($dados)
	{
		$endpoint = 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?WSDL';
		$tipos = array(
			'sedex'=>'40010',
			'sedex_a_cobrar'=>'40045',
			'sedex_10'=>'40215',
			'sedex_hoje'=>'40290',
			'pac'=>'41106'
		);
		$formatos = array(
			'caixa'=>1,
			'rolo'=>2,
			'envelope'=>3,
		);
		
		$dados['tipo'] = $tipos[$dados['tipo']];
		$dados['formato'] = $formatos[$dados['formato']];
		/* dados[tipo]
		40010 SEDEX Varejo
		40045 SEDEX a Cobrar Varejo
		40215 SEDEX 10 Varejo
		40290 SEDEX Hoje Varejo
		41106 PAC Varejo
		*/
		
		/*
		1 – Formato caixa/pacote
		2 – Formato rolo/prisma
		3 - Envelope
		*/
		$dados['cep_destino'] = eregi_replace("([^0-9])",'',$dados['cep_destino']);
		$dados['cep_origem'] = eregi_replace("([^0-9])",'',$dados['cep_origem']);

		$soap = @new SoapClient($endpoint, array(
			'trace' => true,
			'exceptions' => true,
			'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
			'connection_timeout' => 1000
		));

		$params = array(
			'nCdEmpresa'=>(isset($dados['empresa']) ? $dados['empresa'] : ''),
			'sDsSenha'=>(isset($dados['senha']) ? $dados['senha'] : ''),
			
			'nCdServico'=>$dados['tipo'],
			'sCepOrigem'=>$dados['cep_origem'],
			'sCepDestino'=>$dados['cep_destino'],
			'nVlPeso'=>$dados['peso'],
			'nCdFormato'=>$dados['formato'],
			'nVlComprimento'=>$dados['comprimento'],
			'nVlAltura'=>$dados['altura'],
			'nVlLargura'=>$dados['largura'],
			'nVlDiametro'=>$dados['diametro'],
			'sCdMaoPropria'=>(isset($dados['mao_propria']) && $dados['mao_propria'] ? 'S' : 'N'),
			'nVlValorDeclarado'=>(isset($dados['valor_declarado']) ? $dados['valor_declarado'] : 0),
			'sCdAvisoRecebimento'=>(isset($dados['aviso_recebimento']) && $dados['aviso_recebimento'] ? 'S' : 'N'),
			'sDtCalculo'=>date('d/m/Y'),
		);
		//die(print_r($params,true));
		$CalcPrecoPrazoData = $soap->CalcPrecoPrazoData($params);
		$resultado = $CalcPrecoPrazoData->CalcPrecoPrazoDataResult->Servicos->cServico;
		if(!is_array($resultado))
			$resultado = array($resultado);
		$dados = array();
		foreach($resultado as $consulta){ $consulta = (array) $consulta;
			$dados[$consulta['Codigo']] = array(
				'codigo'=>$consulta['Codigo'],
				'valor'=>(float) str_replace(',','.',$consulta['Valor']),
				'prazo'=>(int) str_replace(',','.',$consulta['PrazoEntrega']),
				'mao_propria'=>(float) str_replace(',','.',$consulta['ValorMaoPropria']),
				'aviso_recebimento'=>(float) str_replace(',','.',$consulta['ValorAvisoRecebimento']),
				'valor_declarado'=>(float) str_replace(',','.',$consulta['ValorValorDeclarado']),
				'entrega_domiciliar'=>($consulta['EntregaDomiciliar'] === 'S' ? true : false),
				'entrega_sabado'=>($consulta['EntregaSabado'] === 'S' ? true : false),
				'erro'=> array('codigo'=> (real)$consulta['Erro'],'mensagem'=>$consulta['MsgErro']),
			);
		}
		return $dados;
	}
	
	static public function endereco($cep){
		$cep = eregi_replace("([^0-9])",'',$cep);
		$resultado = self::cep($cep);
		if(count($resultado))
			return $resultado[0];
		else
			return null;
	}
	
	static public function cep($endereco){
		include('phpQuery-onefile.php');
		$html = self::simpleCurl('http://m.correios.com.br/movel/buscaCepConfirma.do',array(
			'cepEntrada'=>$endereco,
			'tipoCep'=>'',
			'cepTemp'=>'',
			'metodo'=>'buscarCep',
		));
		phpQuery::newDocumentHTML($html, $charset = 'utf-8');
		$pq_form = pq('');
		//$pq_form = pq('.divopcoes,.botoes',$pq_form)->remove();
		$pesquisa = array();
		foreach(pq('#frmCep > div') as $pq_div){
			if(pq($pq_div)->is('.caixacampobranco') || pq($pq_div)->is('.caixacampoazul')){
				$dados = array();
				$dados['cliente'] = trim(pq('.resposta:contains("Cliente: ") + .respostadestaque:eq(0)',$pq_div)->text());
				
				if(count(pq('.resposta:contains("Endereço: ") + .respostadestaque:eq(0)',$pq_div)))
					$dados['logradouro'] = trim(pq('.resposta:contains("Endereço: ") + .respostadestaque:eq(0)',$pq_div)->text());
				else
					$dados['logradouro'] = trim(pq('.resposta:contains("Logradouro: ") + .respostadestaque:eq(0)',$pq_div)->text());
				$dados['bairro'] = trim(pq('.resposta:contains("Bairro: ") + .respostadestaque:eq(0)',$pq_div)->text());
				
				$dados['cidade/uf'] = trim(pq('.resposta:contains("Localidade") + .respostadestaque:eq(0)',$pq_div)->text());
				$dados['cep'] = trim(pq('.resposta:contains("CEP: ") + .respostadestaque:eq(0)',$pq_div)->text());
				
				$dados['cidade/uf'] = explode('/',$dados['cidade/uf']);
				
				$dados['cidade'] = trim($dados['cidade/uf'][0]);
				
				$dados['uf'] = trim($dados['cidade/uf'][1]);
				unset($dados['cidade/uf']);
				
				$pesquisa[] = $dados;
			}
		}
		return $pesquisa;
	}
	
	static public function rastreio($codigo){
		$html = self::simpleCurl('http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI='.$codigo);
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

	static private function simpleCurl($url,$post=array(),$get=array()){
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