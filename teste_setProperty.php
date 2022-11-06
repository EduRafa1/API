<?php 
	// Lib loader
	require_once 'Lib/Livro/Core/ClassLoader.php';
	$al= new Livro\Core\ClassLoader;
	$al->addNamespace('Livro', 'Lib/Livro');
	$al->register();
	
	// App loader
	require_once 'Lib/Livro/Core/AppLoader.php';
	$al= new Livro\Core\AppLoader;
	$al->addDirectory('App/Control');
	$al->addDirectory('App/Model');
	$al->register();

	

	use Livro\Database\Transaction;
	use Livro\Database\Repository;
	use Livro\Database\Criteria;

	//Banco de dados aberto	
	Transaction::open('banco_bol_dev');

	//Chama a classe repositório, Classe que faz consulta no db com WHERE	
	$repositorio = new Repository('Tsubcontas');
	//Cria a classe que gera os criterios a consulta do Repository
	$criterio = new Criteria;
	

	// GERAR UMA QUERY DE CONSULTA
	$criterio->add('car_sub_codigo', '=', '2');
	$criterio->add('sub_emp_codigo', '<', '20');

	//JUNTA TABELA
	$criterio->setProperty('inner join','tcartao','sub_codigo','car_sub_codigo');
	//$criterio->setProperty('inner join','vpbank_boletos','sub_codigo','bol_sub_codigo');
	
	
	//ADICIONA um SELECT
	$criterio->setProperty('max', 'car_parcela','total_valor');
	$criterio->setProperty('day', 'car_datavencimento','dia');
	$criterio->setProperty('month', 'car_datavencimento','mes');
	$criterio->setProperty('year', 'car_datavencimento','ano');
	
	//Separa por grupo
	$criterio->setProperty('group by', 'sub_emp_codigo,car_codigo');
	
	//Seleciona Valor Unitário
	$criterio->setProperty('valor', 'sub_emp_codigo');
	$criterio->setProperty('valor', 'sub_codigo');
	$criterio->setProperty('valor', 'car_sub_codigo');
	$criterio->setProperty('valor', 'car_tipo');
	$criterio->setProperty('valor', 'car_datavencimento');
	$criterio->setProperty('valor', 'car_vpb_taxa');

	//Chama o Loader passando os critérios
	//$contas = $repositorio->load($criterio);
	echo "<pre>";
	
	//print_r($contas);
	
	///Consulta simples em tsubcontas onde busta todos os registros da tabela
	$subconta = new Tsubcontas;
	$vet = $subconta->all();
	

	//print_r($vet);
	
	//Fechando a transação do banco de dados
	Transaction::close();
	

	
 ?>