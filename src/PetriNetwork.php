<?php

namespace App;

use App\Lugar;
use App\Arco;
use App\Transicoes;
use LucidFrame\Console\ConsoleTable;
use Wujunze\Colors;

class PetriNetwork
{
    public $lugares;

    public $transicoes;

    public $lugaresAponta;

    public $tabela;

    public $tabelaTemHeader = false;

    public $tabelaLinha = 0;

    public function __construct($dados)
    {
        $this->table = new ConsoleTable();
        $this->colors = new Colors();

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
                    foreach ($lugares as $lugar) {
                        foreach ($this->lugares[$lugar]->arcos as $ar) {
                            $pes += $ar->peso;
                        }
                    }

                    $marcas = 0;
                    foreach ($lugares as $lugar) {
                        $arco = reset($this->lugares[$lugar]->arcos);
                        if ($pes > 0) {
                            $marcas = 1;
                        } else {
                            $marcas = $this->lugares[$lugar]->marcas;
                        }

                        if ($pes > 0) {
                            $this->lugares[$lugar]->marcas -= $arco->peso > 0 ? $arco->peso : 1;
                        } elseif ($arco->peso == 0) {
                            $this->lugares[$lugar]->marcas = 0;
                        } else {
                            $this->lugares[$lugar]->marcas -= $arco->peso;
                        }
                    }
                    foreach ($this->transicoes[$transicao]->arcos as $arco) {
                        $this->lugares[$arco->apontando]->marcas += $arco->peso > 0 ? $arco->peso : $marcas;
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
        $this->table->display();
    }

    private function adicionarParaTabela()
    {
        if (!$this->tabelaTemHeader) {
            $this->table->addHeader('Núm');
            foreach ($this->lugares as $lugar) {
                $this->table->addHeader($lugar->nome);
            }

            foreach ($this->transicoes as $transicao) {
                $this->table->addHeader($transicao->nome);
            }
            $this->tabelaTemHeader = true;
        }

        $row = $this->table->addRow();
        $row->addColumn($this->tabelaLinha++);
        foreach ($this->lugares as $lugar) {
            $row->addColumn($lugar->marcas > 0 ? $this->colors->getColoredString($lugar->marcas, 'yellow') : $lugar->marcas);
        }

        foreach ($this->transicoes as $transicao) {
            $row->addColumn($transicao->habilitado ? $this->colors->getColoredString('S', 'green') : $this->colors->getColoredString('N', 'red'));
        }
    }
}
