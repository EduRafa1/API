<?php

    //include_once('Lib/Livro/Legado/ecffuncoes.php');
    header('Content-type: application/json');
    use Livro\Database\Transaction;
    use Livro\Database\Repository;
    use Livro\Database\Criteria;
    
    $url = isset($_GET['url']) ? explode('/', $_GET['url']) : NULL;  //Verifica se existe url

    $api = $url != NULL ? $url[0] : 'error'; // Verifica e atribui o valor da URL na variavel API;
    if ($api === 'api') {
        array_shift($url);
        //AUTOLOADER NAMESPACE
        require_once 'Lib/Livro/Core/ClassLoader.php';
        $al= new Livro\Core\ClassLoader;
        $al->addNamespace('Livro', 'Lib/Livro');
        $al->register();
        
        //Carrega as classes no diretorio App/Services
        require_once 'Lib/Livro/Core/AppLoader.php';
        $al= new Livro\Core\AppLoader;
        $al->addDirectory('App/Services');
        $al->addDirectory('App/Trait');
        $al->addDirectory('App/Model');
        $al->register();
        try {
            ///Abrindo transação com o banco de dados
            Transaction::open('banco_bol_dev');    
            //Pegando dados do Cabeçalho
            $headers = getallheaders();
            //Iniciando o banco de desenvolvedores
            $developer = new Desenvolvedores;
            $developer->headers = $headers;
            //Verifica chave token do desenvolvedor
            $verified_developer = $developer->CheckToken('Authorization','dev_token');
            if ($verified_developer != 'N') {
                //Iniciando banco de Cliente
                $client = new Tsubcontas;    
                $client->headers = $headers;
                //Verifica chave token do cliente do desenvolvedor
                $verified_customer = $client->CheckToken('TokenCliente','sub_vpbanktoken');
                if ($verified_customer != 'N') {
                    //Tokens Verificados
                    $method = strtoupper($_SERVER['REQUEST_METHOD']); // GET - POST - PUT - DELETE  

                    switch ($method) { //HTTP RESPONSE                         
                        case 'POST':
                            http_response_code(201);
                            break;
                        case 'GET':
                        case 'PUT':
                        case 'DELETE':
                            http_response_code(200);
                        break;
                    }
                    if (isset($url[0]))
                        $service = ucfirst($url[0]).'Service'; // SERVICE
                    else
                        throw new Exception('Rota está vazio!');

                    $obj  = json_decode(file_get_contents('php://input'),true); // DATA 
                    
                    if (class_exists($service)) 
                        $stdClass = new $service;  // NEW CLASS
                    else
                        throw new Exception('Rota "'.ucfirst($url[0]).'" Incorreto!'); 

                    $response = $stdClass->$method($verified_developer,$verified_customer,$obj); // CHAMADA DA CLASSE PELO METHODO.  
                    echo json_encode(array('status'=>'success','data'=>$response)); // RETORNO DA RESPOSTA  :: SUCESSO ::
                    exit;
                }else
                    throw new Exception('Token do Cliente incorreto!');
            }else
                throw new Exception('Token de Authorization Incorreto!');
            //Fechando a transação do banco de dados
            Transaction::close();

        } catch (Exception $e) {
            http_response_code(404);
            echo json_encode(array('status'=>'error','data'=>$e->getMessage()),JSON_UNESCAPED_UNICODE); // RETORNO DA RESPOTA :: ERRO ::
        }
    
    }else{
        http_response_code(204);
    }
 ?>