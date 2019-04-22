<?php

namespace App;

class Lugar
{
    public $nome;

    public $marcas;

    public $arcos;

    public function __construct($nome, $marcas, $arcos)
    {
        $this->nome = $nome;
        $this->marcas = $marcas;
        $this->arcos = $arcos;
    }
}
