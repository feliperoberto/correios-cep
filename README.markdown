# API não oficial dos Correios
Pacote de classes responsáveis por fazer coleta e tratamento de dados obtidos diretamente nos sites dos Correios.
O consumo da api pode ser realizado diretamente em seu código PHP através de chamadas estáticas, ou via chamadas HTTP.

## Chamadas via PHP
``` php
\Correios\CEP::buscar($cep);
\Correios\Rastreio::localizar($codigo_rastreio);
```
O pacote também esta dispovível no https://packagist.org/packages/feliperoberto/non-official-correios-api e pode ser instalado via php composer
``` console
php composer.phar install feliperoberto/non-official-correios-api
```

### Exemplo de uso
``` php
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
```


## Chamadas via http
Você pode obter endereço estruturado através do CEP, localizar cep com endereço parcial ou consultar histórico de rastreamento de encomenda.

### Consulta de CEP
Envia-se o `string` do CEP desejado e é retornado um objeto com as propriedades do endereço.
Caso não seja localizado, será retornado `null`

``` console
curl -X GET http://127.0.0.1/?cep=01001001
```
```json
{
    "bairro": "S\u00e9\u00a0",
    "cep": "01001-001",
    "cidade": "S\u00e3o Paulo",
    "logradouro": "Pra\u00e7a da S\u00e9 - lado par\u00a0",
    "uf": "SP\u00a0"
}
```

### Pesquisa de endereço

Envia-se a string do endereço a ser procurado e será retornado uma lista de objetos de endereço.
Caso não há haja resultados a resposta será uma lista vazia.

``` console
curl -X GET http://127.0.0.1/?endereco=av%20paulista
```
```json
[
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
```

### Rastreamento de encomendas
``` console
curl -X GET http://127.0.0.1/?codigo_rastreio=OG490654336BR
```
```json
[
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
]
```

## Deploy on Heroku
De forma resumida.
```console
git clone https://github.com/feliperoberto/correios-cep.git
heroku login
git push heroku master
heroku open
```
Mais detalhes em https://devcenter.heroku.com/articles/getting-started-with-php?singlepage=true
