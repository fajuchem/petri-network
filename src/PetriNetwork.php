<?php

namespace App;

use App\Lugar;
use App\Arco;
use App\Transicoes;

class PetriNetwork
{
    public $lugares;

    public $transicoes;

    public $lugaresAponta;

    public $tabela;

    public function __construct($dados)
    {
        foreach ($dados['lugares'] as $lugar) {
            $arcos = [];
            foreach ($lugar['arcos'] as $arco) {
                $ar = new Arco($arco['apontando'], $arco['peso']);
                $arcos[$arco['apontando']] = $ar;
                if (!empty($arco['apontando'])) {
                    $this->lugaresAponta[$arco['apontando']][] = $lugar['nome'];
                }
            }
            $this->lugares[$lugar['nome']] = new Lugar($lugar['nome'], $lugar['marcas'], $arcos);
        }

        foreach ($dados['transicoes'] as $transicao) {
            $arcos = [];
            foreach ($transicao['arcos'] as $arco) {
                $arcos[] = new Arco($arco['apontando'], $arco['peso']);
            }
            $this->transicoes[$transicao['nome']] = new Transicao($transicao['nome'], $arcos);
        }
    }

    public function run()
    {
        $this->atualizarTransicoes();
        $this->adicionarParaTabela();

        while ($this->existeTransicaoHabilitada()) {
            foreach ($this->lugaresAponta as $transicao => $lugares) {
                if ($this->transicoes[$transicao]->habilitado) {
                    $pes = 0;
                    $marcas = 0;
                    foreach ($lugares as $lugar) {
                        foreach ($this->lugares[$lugar]->arcos as $ar) {
                            $pes += $ar->peso;
                        }
                        $marcas += $this->lugares[$lugar]->marcas;
                        $arco = reset($this->lugares[$lugar]->arcos);
                        if ($pes > 0) {
                            $this->lugares[$lugar]->marcas -= $arco->peso ? $arco->peso : 1;
                        } elseif ($arco->peso == 0) {
                            $this->lugares[$lugar]->marcas = 0;
                        } else {
                            $this->lugares[$lugar]->marcas -= $arco->peso;
                        }
                    }
                    foreach ($this->transicoes[$transicao]->arcos as $arco) {
                        $this->lugares[$arco->apontando]->marcas = $pes > 0 ? 1 : $marcas;
                    }
                }
            }
            $this->atualizarTransicoes();
            $this->adicionarParaTabela();
        }

        $this->imprimirTabela();
    }

    private function atualizarTransicoes()
    {
        $statusTransicoes = [];

        foreach ($this->lugaresAponta as $transicao => $lugares) {
            $status = true;
            foreach ($lugares as $lugar) {
                if ($this->lugares[$lugar]->marcas == 0 || $this->lugares[$lugar]->marcas < $this->lugares[$lugar]->arcos[$transicao]->peso) {
                    $status = false;
                }
            }
            $this->transicoes[$transicao]->habilitado = $status;
        }
    }

    private function existeTransicaoHabilitada()
    {
        foreach ($this->transicoes as $transicao) {
            if ($transicao->habilitado) {
                return $transicao->habilitado;
            }
        }
    }

    private function imprimirTabela()
    {
        $header = 'n | ';

        foreach ($this->lugares as $lugar) {
            $header .= $lugar->nome . ' ';
        }

        foreach ($this->transicoes as $transicao) {
            $header .= $transicao->nome . ' ';
        }

        echo $header . PHP_EOL;

        foreach ($this->tabela as $key => $linha) {
            echo "$key | $linha";
        }
    }

    private function adicionarParaTabela()
    {
        $linha = '';

        foreach ($this->lugares as $lugar) {
            $linha .= $lugar->marcas . '  ';
        }

        foreach ($this->transicoes as $transicao) {
            $linha .= $transicao->habilitado ? 'S  ' : 'N  ';
        }

        $this->tabela[] = $linha . PHP_EOL;
    }
}
