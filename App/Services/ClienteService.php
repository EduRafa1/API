<?php 
 use Livro\Database\Transaction;
	class ClienteService 
	{
		
		public function POST($dev,$cliente,$data){
			Transaction::open('banco_nfe_dev'); 
			$tempresanfe = new TempresaNFE;
			
			$checkarray = $tempresanfe->verifyCheckArray($data['cliente']);

			if (isset($checkarray['error']))
				return $checkarray['error'];
			
			Transaction::close(); 
		}





		public function GET($dev,$cliente,$data){
			return $data;
		}
		public function PUT($dev,$cliente,$data){
			return $data;
		}
		public function DELETE($dev,$cliente,$data){
			return $data;
		}
	}

 ?>