<?php 
Trait Token
{
	public function CheckToken($key,$indice){
		$hash = base64_decode($this->headers[$key]);

		$token = $this->load($hash,$indice);
		
		return $token != '' ? $token : 'N';
	}	
}

 ?>