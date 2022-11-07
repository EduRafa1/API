<?php 
 
require_once('vendor/autoload.php');
use Livro\Database\Transaction;
use Livro\Database\Repository;
use Livro\Database\Criteria;
//require_once('App/Model/Desenvolvedores.php');
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


use PHPUnit\Framework\TestCase;

class DesenvolvedoresTestes extends TestCase
{
	/**
	* @test 
	* 
	*/	
	public function testCheckToken_ReturnVarConstant()
    {	
    	Transaction::open('banco_bol_dev'); 
    	$developer = new Desenvolvedores;
    	$developer->headers = ['Authorization' => 'M21lakQ5S2VWc2ttOVNySXo1NzhjOUtVaE9OWkNhRmU5cWxlSWd1cTlSYyw='];
    	$verified_developer = $developer->CheckToken('Authorization','dev_token');
    	$this->assertInstanceOf(Desenvolvedores::class,$verified_developer);	
    	Transaction::close();
    }
    
    /**
     *  
     */
    public function testCheckToken_ReturnNull()
    {	
    	Transaction::open('banco_bol_dev'); 
    	$developer = new Desenvolvedores;
    	$developer->headers = ['Authorization' => 'foo'];
    	$verified_developer = $developer->CheckToken('Authorization','dev_token');
    	$this->assertEquals('N',$verified_developer);
    	Transaction::close();
    }	
    /**
    * @expectedException
    */
    public function testCheckToken_ReturnExceptionIsNotTransaction()
    {	
    	$developer = new Desenvolvedores;
    	$developer->headers = ['Authorization' => '3mejD9KeVskm9SrIz578c9KUhONZCaFe9qleIguq9Rc,'];
    	$this->expectException(\Exception::class);
    	$verified_developer = $developer->CheckToken('Authorization','dev_token');
    }
}

/*
$testar = new RecordTestes;
echo "<pre>";
var_dump($testar);
$ret = $testar->testCheckToken_ReturnVarConstant();
var_dump($ret);
*/
//php vendor/bin/phpunit --colors PHPUnit/DesenvolvedoresTestes.php
//Executar o comando na pasta Raiz
 ?>