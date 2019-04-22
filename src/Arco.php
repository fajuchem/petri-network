<?php

namespace App;

class Arco
{
    public $peso;

    public $apontando;

    public function __construct($apontando, $peso)
    {
        $this->peso = $peso;
        $this->apontando = $apontando;
    }
}
