<?php
namespace Livro\Database;

use Exception;

/**
 * Manipular coleções de objetos.
 */
final class Repository
{
    private $activeRecord; // classe manipulada pelo repositório
    
    /**
     * Instancia um Repositório de objetos
     * @param $class = Classe dos Objetos
     */
    function __construct($class)
    {
        $this->activeRecord = $class;
    }
    
    /**
     * Carrega um conjunto de objetos (collection) da base de dados
     * @param $criteria = objeto do tipo TCriteria
     */
    function load(Criteria $criteria)
    {
        // instancia a instrução de SELECT
        $sql = "SELECT ";



        $crits = $criteria->getProperty('crit');

        static $pri = TRUE;
        if ($crits != null) {
            foreach ($crits as $chave =>$valor) {

                if ($pri) {
                    $sql.= strtoupper($valor['property'])."({$valor['valor']}) as {$valor['sufix']}";
                    $pri = FALSE;
                }
                else
                    $sql.= ', '.strtoupper($valor['property'])."({$valor['valor']}) as {$valor['sufix']} ";
            }
        }
        $valores = $criteria->getProperty('valor');
        if ($valores != null) {
            foreach ($valores as $key => $value) {
                $pri2 = TRUE;
                if ($pri2 == TRUE) {
                    if ($pri == TRUE) {
                        $sql.= " {$value} ";
                        $pri = FALSE;
                    }else{
                        $sql.= ", {$value} ";
                    }
                        $pri2 = FALSE;
                }else{
                    $sql.= ", {$value} ";
                }


            }
        }
       
        //Verifica se não tem nenhum filtro de busca
        if ($crits == '' && $valores == '') 
            $sql.= " * ";


        $sql .= " FROM " . constant($this->activeRecord.'::TABLENAME');
        $join = $criteria->getProperty('join');
        if ($join != '') {
            foreach ($join as $chave => $valor) {
                $sql.= ' '.strtoupper($valor['property']) . " {$valor['banco']} ON ({$valor['criterio1']} = {$valor['criterio2']}) ";
            }    
        }
        // obtém a cláusula WHERE do objeto criteria.
        if ($criteria)
        {
            $expression = $criteria->dump();
            if ($expression)
            {
                $sql .= ' WHERE ' . $expression;
            }
            
            // obtém as propriedades do critério
            $order = $criteria->getProperty('order');
            $limit = $criteria->getProperty('limit');
            $offset= $criteria->getProperty('offset');
            $group=  $criteria->getProperty('group by');
            
            // obtém a ordenação do SELECT
      
            if ($group) {
                $sql .= ' GROUP BY ' . $group;
            }

            if ($order) {
                $sql .= ' ORDER BY ' . $order;
            }

            if ($limit) {
                $sql .= ' LIMIT ' . $limit;
            }
            
            if ($offset) {
                $sql .= ' OFFSET ' . $offset;
            }
            
        }
         echo $sql;
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            
            // executa a consulta no banco de dados
            $result= $conn->query($sql);
            $results = array();
            if ($result)
            {
                // percorre os resultados da consulta, retornando um objeto
                while ($row = $result->fetchObject($this->activeRecord))
                {
                    // armazena no array $results;
                    $results[] = $row;
                }
            }
            //var_dump($results);
            return $results;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Excluir um conjunto de objetos (collection) da base de dados
     * @param $criteria = objeto do tipo Criteria
     */
    function delete(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $sql = "DELETE FROM " . constant($this->activeRecord.'::TABLENAME');
        if ($expression)
        {
            $sql .= ' WHERE ' . $expression;
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            // executa instrução de DELETE
            $result = $conn->exec($sql);
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
            
        }
    }
    
    /**
     * Retorna a quantidade de objetos da base de dados
     * que satisfazem um determinado critério de seleção.
     * @param $criteria = objeto do tipo TCriteria
     */
    function count(Criteria $criteria)
    {
        $expression = $criteria->dump();
        $sql = "SELECT count(*) FROM " . constant($this->activeRecord.'::TABLENAME');
        if ($expression)
        {
            $sql .= ' WHERE ' . $expression;
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // registra mensagem de log
            Transaction::log($sql);
            
            // executa instrução de SELECT
            $result= $conn->query($sql);
            if ($result)
            {
                $row = $result->fetch();
            }
            // retorna o resultado
            return $row[0];
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
}
