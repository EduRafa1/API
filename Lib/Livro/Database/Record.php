<?php
namespace Livro\Database;

use Exception;

abstract class Record implements RecordInterface
{
    protected $data; // array contendo os dados do objeto
    
    /**
     * Instancia um Active Record. Se passado o $id, já carrega o objeto
     * @param [$id] = ID do objeto
     */
    public function __construct($id = NULL)
    {
        if ($id) // se o ID for informado
        {
            // carrega o objeto correspondente
            $object = $this->load($id);
            if ($object)
            {
                $this->fromArray($object->toArray());
            }
        }
    }
    
    /**
     * Limpa o ID para que seja gerado um novo ID para o clone.
     */
    public function __clone()
    {
        unset($this->data['id']);
    }
    
    /**
     * Executado sempre que uma propriedade for atribuída.
     */
    public function __set($prop, $value)
    {
        // verifica se existe método set_<propriedade>
        if (method_exists($this, 'set_'.$prop))
        {
            // executa o método set_<propriedade>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {
            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }
    
    /**
     * Executado sempre que uma propriedade for requerida
     */
    public function __get($prop)
    {
        // verifica se existe método get_<propriedade>
        if (method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<propriedade>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // retorna o valor da propriedade
            if (isset($this->data[$prop]))
            {
                return $this->data[$prop];
            }
        }
    }
    
    /**
     * Retorna se a propriedade está definida
     */
    public function __isset($prop)
    {
        return isset($this->data[$prop]);
    }
    /**
    *   Retorna o nome da coluda indice código
    */
    public function PrefixCodigo(){
        $class = get_class($this);
        return constant("{$class}::TABLEPREFIX");
    }
    /**
     * Retorna o valor da constant CheckArray
     */
    private function getCheckArray(){
        $class = get_class($this);
        return constant("{$class}::CHECKARRAY");
    }
    /**
     * Retorna o nome da entidade (tabela)
     */
    private function getEntity()
    {
        // obtém o nome da classe
        $class = get_class($this);

        return constant("{$class}::TABLENAME");
    }
    public function verifyCheckArray($data)
    {
        $response = [];
        //Pega variavel CHECKARRAY
        $CheckArray = $this->getCheckArray();
        foreach ($CheckArray as $chave => $valor) {
            if (!array_key_exists($valor,$data) || $data[$valor] == '') { // Valores Incorretos no Array Recebido
                $response['error']['Message'] = '';
                $response['error']['Validation']['data'][] = $valor;
            }
        }
        //Verifica o response pro retorno Sucesso ou Error.
        if (count($response) <= 0) 
            $response['sucesso'] = 'S'; // Tudo OK

        if (!isset($response['sucesso'])) 
            $response['error']['Message'] = utf8_encode('Dados para inserção incorreto. Verifique o Validation para mais detalhe!');
        
        return $response;
    }
    /**
     * Preenche os dados do objeto com um array
     */
    public function fromArray($data)
    {
        $this->data = $data;
    }
    
    /**
     * Retorna os dados do objeto como array
     */
    public function toArray()
    {
        return $this->data;
    }
    
    /**
     * Armazena o objeto na base de dados
     */
    public function store()
    {
        $prepared = $this->prepare($this->data);
        $prefix_id = $this->PrefixCodigo().'codigo';
        // verifica se tem ID ou se existe na base de dados
        if (empty($this->data[$prefix_id]) or (!$this->load($this->$prefix_id)))
        {
            // incrementa o ID

            if (empty($this->data[$prefix_id]))
            {
                $this->$prefix_id = $this->getLast() +1;
                $prepared[$prefix_id] = $this->$prefix_id;
            }
            
            // cria uma instrução de insert
            $sql = "INSERT INTO {$this->getEntity()} " . 
                   '('. implode(', ', array_keys($prepared))   . ' )'.
                   ' values ' .
                   '('. implode(', ', array_values($prepared)) . ' )';
        }
        else
        {
            // monta a string de UPDATE
            $sql = "UPDATE {$this->getEntity()}";
            // monta os pares: coluna=valor,...
            if ($prepared) {
                foreach ($prepared as $column => $value) {
                    if ($column !== 'id') {
                        $set[] = "{$column} = {$value}";
                    }
                }
            }
            $sql .= ' SET ' . implode(', ', $set);
            $sql .= ' WHERE id=' . (int) $this->data['id'];
        }
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // faz o log e executa o SQL
            Transaction::log($sql);
            $result = $conn->exec($sql);
            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /*
     * Recupera (retorna) um objeto da base de dados pelo seu ID
     * @param $id = ID do objeto
     */
    public function load($id,$prefix = null)
    {   
        // instancia instrução de SELECT

        $sql = "SELECT * FROM {$this->getEntity()}";
        
        //Prefix modifica o indice da busca.
        
        $identification = $prefix != '' ? $prefix : $this->PrefixCodigo()."codigo";
        
        $sql .= " WHERE {$identification} = '{$id}'";
    
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // cria mensagem de log e executa a consulta
            try {
                $result= $conn->query($sql);
                if ($result)
                {
                    // retorna os dados em forma de objeto
                    $object = $result->fetchObject(get_class($this));
                }

            } catch (Exception $e) {
                throw new Exception('Erro na Consulta do banco de dados. Class = Record->Load :: Erro :: ');
            }

            return $object; 
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Exclui um objeto da base de dados através de seu ID.
     * @param $id = ID do objeto
     */
    public function delete($id = NULL)
    {
        // o ID é o parâmetro ou a propriedade ID
        $id = $id ? $id : $this->id;
        
        // monsta a string de UPDATE
        $sql  = "DELETE FROM {$this->getEntity()}";
        $sql .= ' WHERE id=' . (int) $this->data['id'];
        
        // obtém transação ativa
        if ($conn = Transaction::get())
        {
            // faz o log e executa o SQL
            Transaction::log($sql);
            $result = $conn->exec($sql);
            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Retorna o último ID
     */
    private function getLast()
    {
        // inicia transação
        if ($conn = Transaction::get())
        {
            // instancia instrução de SELECT
            $sql  = "SELECT max({$this->PrefixCodigo()}codigo) FROM {$this->getEntity()}";
            
            // cria log e executa instrução SQL
            Transaction::log($sql);
            $result= $conn->query($sql);
            
            // retorna os dados do banco
            $row = $result->fetch();
            return $row[0];
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }
    
    /**
     * Retorna todos objetos
     */
    public static function all()
    {
        $classname = get_called_class();
        $rep = new Repository($classname);
        return $rep->load(new Criteria);
       
         
    }
    
    /**
     * Busca um objeto pelo id
     */
    public static function find($id)
    {
        $classname = get_called_class();
        $ar = new $classname;
        return $ar->load($id);
    }
    
    public function prepare($data)
    {
        $prepared = array();
        foreach ($data as $key => $value)
        {
            if (is_scalar($value))
            {
                $prepared[$key] = $this->escape($value);
            }
        }
        return $prepared;
    }
    
    public function escape($value)
    {
        // verifica se é um dado escalar (string, inteiro, ...)
        if (is_scalar($value))
        {
            if (is_string($value) and (!empty($value)))
            {
                // adiciona \ em aspas
                $value = addslashes($value);
                // caso seja uma string
                return "'$value'";
            }
            else if (is_bool($value))
            {
                // caso seja um boolean
                return $value ? 'TRUE': 'FALSE';
            }
            else if ($value!=='')
            {
                // caso seja outro tipo de dado
                return $value;
            }
            else
            {
                // caso seja NULL
                return "NULL";
            }
        }
    }
}
