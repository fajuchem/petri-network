<?php

namespace App;

class Transicao
{
    public $nome;

    public $arcos;

    public $habilitado;

    public function __construct($nome, $arcos)
    {
        $this->nome = $nome;
        $this->arcos = $arcos;
        $this->habilitado = false;
    }
}
