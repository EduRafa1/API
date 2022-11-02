<?php 
namespace Livro\Database;

use PDO;
use Exception;

class Connection
{
	private function __construct(){}

	public static function open($name)
	{
		if (file_exists("App/Config/{$name}.ini")){ 
			$db = parse_ini_file("App/Config/{$name}.ini");
		}
		else
		{
			throw new Exception("Arquivo {$name} não encontrado");
		}
		$user = isset($db['user']) ? $db['user'] : null;
		$pass = isset($db['pass']) ? $db['pass'] : null;
		$host = isset($db['host']) ? $db['host'] : null;
		$port = isset($db['port']) ? $db['port'] : null;
		$name = isset($db['name']) ? $db['name'] : null;
		$type = isset($db['type']) ? $db['type'] : null;
		switch ($type) {
			case 'mysql':
				//$port = isset($db['port']) ? $db['port'] : '3306';
				echo "mysql:host={$host};dbname={$name}";
				echo ' -- '.$user . ' - ' . $pass;
				$conn = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass);
				var_dump($conn);
				break;

			case 'pgsql':

				break;
			
			case 'sqlite':
				break;
		}
		///$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $conn;
	}

}


 ?>