<?php
require('fpdf.php');

require_once('./qrcode/qrcode.class.php');
//require('code128.php');

define('EURO', chr(128));
define('EURO_VAL', 6.55957);

define('COL', chr(162));
define('COL_VAL', 1);

define('USD', "$");
define('USD_VAL', 1);


class Invoice extends FPDF
{
// private variables
    var $colonnes;
    var $format;
    var $angle = 0;


//barcode USAGE:
    protected $T128;                                         // Tableau des codes 128
    protected $ABCset = "";                                  // jeu des caractères éligibles au C128
    protected $Aset = "";                                    // Set A du jeu des caractères éligibles
    protected $Bset = "";                                    // Set B du jeu des caractères éligibles
    protected $Cset = "";                                    // Set C du jeu des caractères éligibles
    protected $SetFrom;                                      // Convertisseur source des jeux vers le tableau
    protected $SetTo;                                        // Convertisseur destination des jeux vers le tableau
    protected $JStart = array("A" => 103, "B" => 104, "C" => 105); // Caractères de sélection de jeu au début du C128
    protected $JSwap = array("A" => 101, "B" => 100, "C" => 99);   // Caractères de changement de jeu


    function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {

        parent::__construct($orientation, $unit, $format);

        $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composition des caractères
        $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
        $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
        $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
        $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
        $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
        $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
        $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
        $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
        $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
        $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
        $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
        $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
        $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
        $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
        $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
        $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
        $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
        $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
        $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
        $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
        $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
        $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
        $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
        $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
        $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
        $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
        $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
        $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
        $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
        $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
        $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
        $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
        $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
        $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
        $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
        $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
        $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
        $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
        $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
        $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
        $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
        $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
        $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
        $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
        $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
        $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
        $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
        $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
        $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
        $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
        $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
        $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
        $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
        $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
        $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
        $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
        $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
        $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
        $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
        $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
        $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
        $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
        $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
        $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
        $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
        $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
        $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
        $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
        $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
        $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
        $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
        $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
        $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
        $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
        $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
        $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
        $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
        $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
        $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
        $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
        $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
        $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
        $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
        $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
        $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
        $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
        $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
        $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
        $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
        $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
        $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
        $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
        $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
        $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
        $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
        $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
        $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
        $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
        $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
        $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]
        $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
        $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
        $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
        $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
        $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
        $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
        $this->T128[] = array(2, 1);                       //107 : [END BAR]

        for ($i = 32; $i <= 95; $i++) {                                            // jeux de caractères
            $this->ABCset .= chr($i);
        }
        $this->Aset = $this->ABCset;
        $this->Bset = $this->ABCset;

        for ($i = 0; $i <= 31; $i++) {
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
        }
        for ($i = 96; $i <= 127; $i++) {
            $this->ABCset .= chr($i);
            $this->Bset .= chr($i);
        }
        for ($i = 200; $i <= 210; $i++) {                                           // controle 128
            $this->ABCset .= chr($i);
            $this->Aset .= chr($i);
            $this->Bset .= chr($i);
        }
        $this->Cset = "0123456789" . chr(206);

        for ($i = 0; $i < 96; $i++) {                                                   // convertisseurs des jeux A & B
            @$this->SetFrom["A"] .= chr($i);
            @$this->SetFrom["B"] .= chr($i + 32);
            @$this->SetTo["A"] .= chr(($i < 32) ? $i + 64 : $i - 32);
            @$this->SetTo["B"] .= chr($i);
        }
        for ($i = 96; $i < 107; $i++) {                                                 // contrôle des jeux A & B
            @$this->SetFrom["A"] .= chr($i + 104);
            @$this->SetFrom["B"] .= chr($i + 104);
            @$this->SetTo["A"] .= chr($i);
            @$this->SetTo["B"] .= chr($i);
        }
    }


//________________ Fonction encodage et dessin du code 128 _____________________
    function Code128($x, $y, $code, $w, $h)
    {
        $Aguid = "";                                                                      // Création des guides de choix ABC
        $Bguid = "";
        $Cguid = "";
        for ($i = 0; $i < strlen($code); $i++) {
            $needle = substr($code, $i, 1);
            $Aguid .= ((strpos($this->Aset, $needle) === false) ? "N" : "O");
            $Bguid .= ((strpos($this->Bset, $needle) === false) ? "N" : "O");
            $Cguid .= ((strpos($this->Cset, $needle) === false) ? "N" : "O");
        }

        $SminiC = "OOOO";
        $IminiC = 4;

        $crypt = "";
        while ($code > "") {
            // BOUCLE PRINCIPALE DE CODAGE
            $i = strpos($Cguid, $SminiC);                                                // forçage du jeu C, si possible
            if ($i !== false) {
                $Aguid [$i] = "N";
                $Bguid [$i] = "N";
            }

            if (substr($Cguid, 0, $IminiC) == $SminiC) {                                  // jeu C
                $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);  // début Cstart, sinon Cswap
                $made = strpos($Cguid, "N");                                             // étendu du set C
                if ($made === false) {
                    $made = strlen($Cguid);
                }
                if (fmod($made, 2) == 1) {
                    $made--;                                                            // seulement un nombre pair
                }
                for ($i = 0; $i < $made; $i += 2) {
                    $crypt .= chr(strval(substr($code, $i, 2)));                          // conversion 2 par 2
                }
                $jeu = "C";
            } else {
                $madeA = strpos($Aguid, "N");                                            // étendu du set A
                if ($madeA === false) {
                    $madeA = strlen($Aguid);
                }
                $madeB = strpos($Bguid, "N");                                            // étendu du set B
                if ($madeB === false) {
                    $madeB = strlen($Bguid);
                }
                $made = (($madeA < $madeB) ? $madeB : $madeA);                         // étendu traitée
                $jeu = (($madeA < $madeB) ? "B" : "A");                                // Jeu en cours

                $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]); // début start, sinon swap

                $crypt .= strtr(substr($code, 0, $made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversion selon jeu

            }
            $code = substr($code, $made);                                           // raccourcir légende et guides de la zone traitée
            $Aguid = substr($Aguid, $made);
            $Bguid = substr($Bguid, $made);
            $Cguid = substr($Cguid, $made);
        }                                                                          // FIN BOUCLE PRINCIPALE

        $check = ord($crypt[0]);                                                   // calcul de la somme de contrôle
        for ($i = 0; $i < strlen($crypt); $i++) {
            $check += (ord($crypt[$i]) * $i);
        }
        $check %= 103;

        $crypt .= chr($check) . chr(106) . chr(107);                               // Chaine cryptée complète

        $i = (strlen($crypt) * 11) - 8;                                            // calcul de la largeur du module
        $modul = $w / $i;

        for ($i = 0; $i < strlen($crypt); $i++) {                                      // BOUCLE D'IMPRESSION
            $c = $this->T128[ord($crypt[$i])];
            for ($j = 0; $j < count($c); $j++) {
                $this->Rect($x, $y, $c[$j] * $modul, $h, "F");
                $x += ($c[$j++] + $c[$j]) * $modul;
            }
        }
    }


// private functions
    function RoundedRect($x, $y, $w, $h, $r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if ($style == 'F')
            $op = 'f';
        elseif ($style == 'FD' || $style == 'DF')
            $op = 'B';
        else
            $op = 'S';
        $MyArc = 4 / 3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
        $xc = $x + $w - $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));

        $this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
        $xc = $x + $w - $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x + $r;
        $yc = $y + $h - $r;
        $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
        $this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
        $xc = $x + $r;
        $yc = $y + $r;
        $this->_out(sprintf('%.2F %.2F l', ($x) * $k, ($hp - $yc) * $k));
        $this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);

    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c ', $x1 * $this->k, ($h - $y1) * $this->k,
            $x2 * $this->k, ($h - $y2) * $this->k, $x3 * $this->k, ($h - $y3) * $this->k));
    }

    function Rotate($angle, $x = -1, $y = -1)
    {
        if ($x == -1)
            $x = $this->x;
        if ($y == -1)
            $y = $this->y;
        if ($this->angle != 0)
            $this->_out('Q');
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }


    function _endpage()
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

// public functions
    function sizeOfText($texte, $largeur)
    {
        $index = 0;
        $nb_lines = 0;
        $loop = TRUE;
        while ($loop) {
            $pos = strpos($texte, "\n");
            if (!$pos) {
                $loop = FALSE;
                $ligne = $texte;
            } else {
                $ligne = substr($texte, $index, $pos);
                $texte = substr($texte, $pos + 1);
            }
            $length = floor($this->GetStringWidth($ligne));
            if ($length > 0 && $largeur > 0)
                $res = 1 + floor($length / $largeur);
            else
                $res = 1;
            $nb_lines += $res;
        }
        return $nb_lines;
    }


    function addlogo($logopath)
    {
        $x1 = 10;
        $y1 = 0;
        $this->Image($logopath, $x1, $y1, 40, 20);

    }


// Company
    function addSociete($nom, $adresse)
    {
        $x1 = 10;
        $y1 = 20;
        //Positionnement en bas
        $this->SetXY($x1, $y1);
        $this->SetFont('Arial', 'B', 12);
        $length = $this->GetStringWidth($nom);
        $this->Cell($length, 2, $nom);
        $this->SetXY($x1, $y1 + 4);
        $this->SetFont('Arial', '', 10);
        $length = $this->GetStringWidth($adresse);
        //Coordonnées de la société
        $lignes = $this->sizeOfText($adresse, $length);
        $this->MultiCell($length, 4, $adresse);
    }

// Label and number of invoice/estimate
    function fact_dev($libelle, $num)
    {
        $r1 = $this->w - 80;
        $r2 = $r1 + 68;
        $y1 = 6;
        $y2 = $y1 + 2;
        $mid = ($r1 + $r2) / 2;

        $texte = $libelle . " : " . $num;
        $szfont = 12;
        $loop = 0;

        while ($loop == 0) {
            $this->SetFont("Arial", "B", $szfont);
            $sz = $this->GetStringWidth($texte);
            if (($r1 + $sz) > $r2)
                $szfont--;
            else
                $loop++;
        }

        $this->SetLineWidth(0.1);
        $this->SetFillColor(192);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
        $this->SetXY($r1 + 1, $y1 + 2);
        $this->Cell($r2 - $r1 - 1, 5, $texte, 0, 0, "C");
    }

// Estimate
    function addDevis($numdev)
    {
        $string = sprintf("DEV%04d", $numdev);
        $this->fact_dev("Devis", $string);
    }

// Invoice
    function addFacture($numfact)
    {
        $string = sprintf("FA%04d", $numfact);
        $this->fact_dev("Facture", $string);
    }

    function addDate($date)
    {
        $r1 = $this->w - 61;
        $r2 = $r1 + 30;
        $y1 = 17;
        $y2 = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "FECHA", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }

    function addClient($ref)
    {
        $r1 = $this->w - 31;
        $r2 = $r1 + 19;
        $y1 = 17;
        $y2 = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "CLIENTE", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $ref, 0, 0, "C");
    }

    function addPageNumber($page)
    {
        $r1 = $this->w - 80;
        $r2 = $r1 + 19;
        $y1 = 17;
        $y2 = $y1;
        $mid = $y1 + ($y2 / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 3.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 3);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 5, "PAG", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 9);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $page, 0, 0, "C");
    }

// Client address
    function addClientAdresse($adresse)
    {
        $r1 = $this->w - 80;
        $r2 = $r1 + 68;
        $y1 = 40;
        $this->SetXY($r1, $y1);
        $this->MultiCell(60, 4, $adresse);
    }

// Mode of payment
    function addReglement($mode)
    {
        $r1 = 10;
        $r2 = $r1 + 60;
        $y1 = 80;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "FORMA DE PAGO", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $mode, 0, 0, "C");
    }

// Expiry date
    function addEcheance($date)
    {
        $r1 = 80;
        $r2 = $r1 + 40;
        $y1 = 80;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 1);
        $this->SetFont("Arial", "B", 10);
        $this->Cell(10, 4, "VENCIMIENTO", 0, 0, "C");
        $this->SetXY($r1 + ($r2 - $r1) / 2 - 5, $y1 + 5);
        $this->SetFont("Arial", "", 10);
        $this->Cell(10, 5, $date, 0, 0, "C");
    }

// VAT number

    function adddescripcion($tva)
    {
        $this->SetFont("Arial", "B", 10);
        $r1 = 128;
        $y1 = 40;
        $r2 = $r1 + 70;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1) + 20, 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + 16, $y1 + 1);
        $this->Cell(40, 4, "DESCRIPCION", '', '', "C");
        $this->SetFont("Arial", "", 10);
        $this->SetXY($r1 + 10, $y1 + 5);
        //   $this->Cell(40, 5, $tva, '', '', "C");
        if (isset($adresse))
            $length = $this->GetStringWidth($adresse);
        else
            $length = 0;
        $lignes = $this->sizeOfText($tva, $length);
        $this->MultiCell($length, 4, $tva);
    }


    function addcliente($nombre, $cedula = '', $correo = '', $telefono = '')
    {
        $this->SetFont("Arial", "B", 10);
        $r1 = 10;
        $y1 = 40;
        $r2 = $r1 + 100;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1) + 20, 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + 16, $y1 + 1);
        $this->Cell(40, 4, "CLIENTE", '', '', "C");
        $this->SetFont("Arial", "", 10);
        $this->SetXY($r1 + 10, $y1 + 5);

        $length = 0;

        $this->MultiCell($length, 4, ' ');

        $this->MultiCell($length, 4, trim($nombre));
        $this->MultiCell($length, 4, $cedula);
        $this->MultiCell($length, 4, $correo);
        $this->MultiCell($length, 4, $telefono);


    }



    function addTipoCambio($tipoCambio)
    {
        $this->SetFont("Arial", "B", 10);
        $r1 = $this->w - 80;
        $r2 = $r1 + 70;
        $y1 = 80;
        $y2 = $y1 + 10;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1, $mid, $r2, $mid);
        $this->SetXY($r1 + 16, $y1 + 1);
        $this->Cell(40, 4, "Tipo de Cambio", '', '', "C");
        $this->SetFont("Arial", "", 10);
        $this->SetXY($r1 + 16, $y1 + 5);
        $this->Cell(40, 5, $tipoCambio, '', '', "C");
    }

    function addReference($ref)
    {
        $this->SetFont("Arial", "", 10);
        $length = $this->GetStringWidth("" . $ref);
        $r1 = 10;
        $r2 = $r1 + $length;
        $y1 = 92;
        $y2 = $y1 + 5;
        $this->SetXY($r1, $y1);
        $this->Cell($length, 4, "" . $ref);
    }

    function addCols($tab)
    {
        global $colonnes;
        $r1 = 10;
        $r2 = $this->w - ($r1 * 2);
        $y1 = 100;
        $y2 = $this->h - 70 - $y1;
        $this->SetXY($r1, $y1);
        $this->Rect($r1, $y1, $r2, $y2, "D");
        $this->Line($r1, $y1 + 6, $r1 + $r2, $y1 + 6);
        $colX = $r1;
        $colonnes = $tab;
        // print_r($tab);exit;
        while (list($lib, $pos) = each($tab)) {
            $this->SetXY($colX, $y1 + 2);
            $this->Cell($pos, 1, $lib, 0, 0, "C");
            $colX += $pos;
            $this->Line($colX, $y1, $colX, $y1 + $y2);
        }
    }

    function addLineFormat($tab)
    {
        global $format, $colonnes;

        while (list($lib, $pos) = each($colonnes)) {
            if (isset($tab["$lib"]))
                $format[$lib] = $tab["$lib"];
        }
    }

    function lineVert($tab)
    {
        global $colonnes;

        reset($colonnes);
        $maxSize = 0;

        while (list($lib, $pos) = each($colonnes)) {
            $texte = $tab[$lib];
            $longCell = $pos - 2;
            $size = $this->sizeOfText($texte, $longCell);
            if ($size > $maxSize)
                $maxSize = $size;
        }

        return $maxSize;
    }

// add a line to the invoice/estimate
    /*    $ligne = array( "REFERENCE"    => $prod["ref"],
                          "DESIGNATION"  => $libelle,
                          "QUANTITE"     => sprintf( "%.2F", $prod["qte"]) ,
                          "P.U. HT"      => sprintf( "%.2F", $prod["px_unit"]),
                          "MONTANT H.T." => sprintf ( "%.2F", $prod["qte"] * $prod["px_unit"]) ,
                          "TVA"          => $prod["tva"] );
    */
    function addLine($ligne, $tab)
    {
        global $colonnes, $format;

        $ordonnee = 10;
        $maxSize = $ligne;

        reset($colonnes);

        while (list($lib, $pos) = each($colonnes)) {
            $longCell = $pos - 2;
            $texte = $tab[$lib];
            $length = $this->GetStringWidth($texte);
            $tailleTexte = $this->sizeOfText($texte, $length);
            $formText = $format[$lib];
            $this->SetXY($ordonnee, $ligne - 1);

            $this->MultiCell($longCell, 4, $texte, 0, $formText);
            if ($maxSize < ($this->GetY()))
                $maxSize = $this->GetY();
            $ordonnee += $pos;
        }
        return ($maxSize - $ligne);
    }

    function addLineTotal($ligne, $tab)
    {
        global $colonnes, $format;


        $maxSize = $ligne;

        reset($colonnes);

        $this->SetXY(150, $ligne);
        $this->Cell(10, 4, "" . $tab["DESCRIPCION"]);
        $this->SetXY(185, $ligne);
        $this->Cell(10, 4, "" . $tab["VALOR"]);


        //  $this->MultiCell( 2, 4 , $texte, 0, $formText);


        /*
                while ( list( $lib, $pos ) = each ($colonnes) )
                {
                    $longCell  = $pos ;
                    $texte     = $tab[ $lib ];
                    $length    = $this->GetStringWidth( $texte );
                    $tailleTexte = $this->sizeOfText( $texte, $length );
                    $formText  = $format[ $lib ];
                    $this->SetXY( $ordonnee, $ligne-1);

                    $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
                    if ( $maxSize < ($this->GetY()  ) )
                        $maxSize = $this->GetY() ;
                    $ordonnee += $pos;
                }

                */
        return (3);
    }


    function addRemarque($remarque)
    {
        $this->SetFont("Arial", "", 6);
        $length = $this->GetStringWidth("Nota : " . $remarque);
        $r1 = 10;
        $r2 = $r1 + $length;
        $y1 = $this->h - 45.5;
        $y2 = $y1 + 5;
        $this->SetXY($r1, $y1);
        $this->Cell($length, 4, "" . $remarque);
    }


    function agregar4lineasAbajo($texto1, $texto2, $texto3, $texto4)
    {

        $this->SetFont("Arial", "", 8);
        $length = $this->GetStringWidth($texto1);

        $this->SetXY(15, 228);
        $this->Cell($length, 4, "" . $texto1);
        $this->SetXY(15, 233);
        $this->Cell($length, 4, "" . $texto2);
        $this->SetXY(15, 238);
        $this->Cell($length, 4, "" . $texto3);
        $this->SetXY(15, 243);
        $this->Cell($length, 4, "" . $texto4);


    }


    function agregarpie($texto)
    {

        $this->SetFont("Arial", "", 6);
        $length = $this->GetStringWidth($texto);

        $this->SetXY(15, 248);
        $this->Cell($length, 4, "" . $texto);
    }


    function ClaveHacienda($numero, $clave)
    {
        $this->SetFont("Arial", "", 6);
        $this->SetXY(15, 251);
        $this->Cell(10, 4, "Numero:" . $numero . " Clave Hacienda: " . $clave);

    }

    function hechoPor($texto)
    {
        $this->SetFont("Arial", "", 8);
        $length = $this->GetStringWidth($texto);
        $r1 = 5;
        $r2 = $r1 + $length;
        $y1 = $this->h - 45.5;
        $y2 = $y1 + 5;
        $this->SetXY(139, 272);
        $this->Cell($length, 4, $texto);
    }

    function MarcoTotales($total, $esdolares)
    {
        $r1 = $this->w - 70;
        $r2 = $r1 + 60;
        $y1 = $this->h - 40;
        $y2 = $y1 + 12;
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1), 2.5, 'D');
        $this->Line($r1 + 20, $y1, $r1 + 20, $y2); // avant EUROS
        $this->Line($r1 + 20, $y1 + 4, $r2, $y1 + 4); // Sous Euros & Francs
        $this->Line($r1 + 38, $y1, $r1 + 38, $y2); // Entre Euros & Francs
        $this->SetFont("Arial", "B", 8);
        $this->SetXY($r1 + 22, $y1);
        $this->Cell(15, 4, "USD", 0, 0, "C");

        $this->SetFont("Arial", "", 8);
        $this->SetXY($r1 + 42, $y1);
        $this->Cell(15, 4, "COL", 0, 0, "C");
        $this->SetFont("Arial", "B", 10);
        $this->SetXY($r1, $y1 + 5);
        $this->Cell(20, 4, "TOTAL ", 0, 0, "C");
        $this->SetTextColor(224, 27, 27);
        if ($esdolares == 1) {
            $monto = USD . "$total";

            $this->Cell(18, 5, $monto, 0, 0, "C");
        } else {

            $monto = COL . "$total";
            $this->Cell(55, 5, $monto, 0, 0, "C");
        }
        $this->SetTextColor(0, 0, 0);
    }


    function temporaire($texte)// WATER MARK
    {
        $this->SetFont('Arial', 'B', 50);
        $this->SetTextColor(203, 203, 203);
        $this->Rotate(45, 55, 190);
        $this->Text(55, 190, $texte);
        $this->Rotate(0);
        $this->SetTextColor(0, 0, 0);
    }


    function title($texte)
    {
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(203, 203, 203);
        //$this->Rotate(45,55,190);
        $this->Text(50, 20, $texte);
        //$this->Rotate(0);
        $this->SetTextColor(0, 0, 0);
    }


    function setbarcode($codetext)
    {
        $offsetybar = 236;
        $offsetxbar = 70;
        $codetext = str_pad($codetext, 12, "0", STR_PAD_LEFT);
        $this->Code128($offsetxbar, 20 + $offsetybar, $codetext, 60, 26);
    }

    function addrecibido()
    {
        $this->SetFont("Arial", "B", 7);
        $r1 = 55;
        $y1 = 177;
        $r2 = $r1 + 90;
        $y2 = $y1 + 15;
        $mid = $y1 + (($y2 - $y1) / 2);
        $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2 - $y1) + 20, 2.5, 'D');

        $this->SetXY($r1 + 3, $y1 + 0);
        $this->Cell(40, 4, "Acepto conforme la cantidad de", '', '', "L");
        $this->SetXY($r1 + 3, $y1 + 2);

        $this->Cell(40, 4, "bultos y mercancia entregada", '', '', "L");
        $this->SetFont("Arial", "", 7);

        $this->Line($r1, $mid - 1, $r2, $mid - 1);


        $this->SetXY($r1 + 0, $y1 + 5);

        $this->Cell(40, 7, "Firma:", '', '', "L");


        $this->Line($r1, $mid + 9, $r2, $mid + 9);
        $this->Line($r1, $mid + 18, $r2, $mid + 18);

        $this->Line($r1, $mid + 26, $r2, $mid + 26);


        $this->SetXY($r1 + 0, $y1 + 16);

        $this->Cell(40, 4, "Cedula:", '', '', "L");
        $this->SetXY($r1 + 0, $y1 + 26);

        $this->Cell(40, 4, "Fecha:", '', '', "L");

    }


    function mostrarModoPago()
    {
        $r1 = 80;
        $y1 = 217;
        $this->SetXY($r1+5 , $y1);
        $this->Cell(50, 4, "Forma de Pago:", '', '', "L");
        $this->SetXY($r1 - 20, $y1 + 5);

        $this->RoundedRect($r1 - 25 , $y1+ 5, 4 , 4, 0.5, 'D');
        $this->RoundedRect($r1 - 5 , $y1+ 5, 4 , 4, 0.5, 'D');
        $this->RoundedRect($r1 + 26 , $y1+ 5, 4 , 4, 0.5, 'D');
        $this->RoundedRect($r1 + 48 , $y1+ 5, 4 , 4, 0.5, 'D');

        $this->SetXY($r1 - 21, $y1 + 5);

        $this->Cell(45, 4, "Tarjeta         Transferencia          Efectivo         Credito    ", '', '', "");
    }


    function QR($code, $x = 25, $y = 256, $size = 30)
    {
        // QR
        $qrcode = new QRcode($code);
        $qrcode->disableBorder();
        // X pos, Y pos, Size of the QR code
        $qrcode->displayFPDF($this, $x, $y, $size);
    }


}

