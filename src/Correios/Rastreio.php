<?php
namespace Correios;
class Rastreio {

	static public function localizar($objecto){
		$ch = curl_init('https://www2.correios.com.br/sistemas/rastreamento/newprint.cfm');
		curl_setopt ($ch, CURLOPT_POST, 1);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, http_build_query([
			'objetos' => $objecto
		]));
		curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$html = curl_exec ($ch);

		\phpQuery::newDocumentHTML($html, $charset = 'utf-8');

		$etapas = array();
		foreach(pq('table.sro tr') as $tr){
			$response = [];
			$content = preg_replace('/\ {2,}/', ' ', preg_replace('/\t|\r|\n/', '', pq('td:nth-child(1)',$tr)->text()));
			preg_match_all('/([0-9]{2}\/[0-9]{2}\/[0-9]{4}) ([0-9]{2}:[0-9]{2}) (.*)\/(.*)/', $content, $response);
			$cidade = trim($response[3][0], " \t\n\r\0\x0B\xC2\xA0");
			$uf = trim($response[4][0], " \t\n\r\0\x0B\xC2\xA0");
			$data = date('Y-m-d H:i:s', strtotime(str_replace('/','-', $response[1][0]) . $response[2][0]));
			$status = pq('td:nth-child(2) strong', $tr)->text();
			$mensagem = trim(preg_replace('/\t|\r|\n|\ {2,}/', '', pq('td:nth-child(2)', $tr)->text()));

			$etapas[] = array(
				'data' => $data,
				'uf'=> $uf,
				'cidade'=> $cidade,
				'status'=> $status,
				'mensagem' => $mensagem
			);
		}
		if(!count($etapas))
			return null;
		return $etapas;
	}

}
