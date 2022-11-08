<?php
use Livro\Database\Record;

class TempresaNFE extends Record
{
    const TABLENAME = 'tempresa';
    const TABLEPREFIX = 'emp_';

    const CHECKARRAY = 
		[
			'nome',
			'nome_fant',
			'contato',
			'cpfcnpj',
			'endereco',
			'numero',
			'bairro',
			'cidade',
			'estado',
			'cep',
			'tel_comercial',
			'email',
		];

}
