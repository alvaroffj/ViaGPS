<?php

class paginacion {

    private $P_actual;
    private $P_total;
    private $R_total;
    private $url_base;
    private $Pag;

    function __construct() {

    }

    function setPActual($n) {
        $this->P_actual = $n;
    }

    function setPTotal($n) {
        $this->P_total = $n;
    }

    function setRTotal($n) {
        $this->R_total = $n;
        $this->setPTotal(ceil($n / $this->getPag()));
    }

    function setPag($n) {
        $this->Pag = $n;
    }

    function getPActual() {
        return $this->P_actual;
    }

    function getPTotal() {
        return $this->P_total;
    }

    function getRTotal() {
        return $this->R_total;
    }

    function getPag() {
        return $this->Pag;
    }

    function setUrlBase($n = null) {
        $this->url_base = $n;
    }

    function getPaginacion() {
        $rt = $this->getRTotal();
        $pt = $this->getPTotal();
        $pa = $this->getPActual();

        if ($pt > 1) {
            if ($pt > 1 && $pa < $pt) {
                $psig = $pa + 1;
                $sig = "<a href='$this->url_base&pag=$psig'> Siguiente &gt;&gt;</a>";
            } else
                $sig = " <span> Siguiente &gt;&gt;</span>";

            if ($pa > 1) {
                $pant = $pa - 1;
                $ant = "<a href='$this->url_base&pag=$pant'>&lt;&lt; Anterior</a>";
            } else
                $ant = " <span>&lt;&lt; Anterior</span>";

            $pags = "";
            for ($i = 1; $i < $pt + 1; $i++) {
                if ($i == $pa) {
                    $pags .=" <span>$i</span>";
                } else {
                    $pags .=" <a href='$this->url_base&pag=$i'>$i</a>";
                }
            }
            $paghtml = "<div class = 'Estilo1'>$ant&nbsp;&nbsp;$pags&nbsp;&nbsp;$sig</div>";
        } else {
            $paghtml = "";
        }

        return $paghtml;
    }
}
?>