<?php
require_once "../../../app/config/config.php";
require_once "../../../app/libraries/Utils.php";
require_once "../../fpdf183/fpdf.php";
require_once "sommaire.php";
require_once "projet.php";

class ProjetPdf extends Sommaire {
    public $projetModel;
    
    function __construct() {
        parent::__construct();
        $this->projetModel = new Projet();
    }
    
    function Header() {
        // // Logo
        // $this->Image('public/images/logo.png', 10, 6, 30);
        // // Police Arial gras 15
        // $this->SetFont('Arial', 'B', 15);
        // // Deplacement a droite
        // $this->Cell(80);
        // // Titre
        // $this->Cell(30, 10, 'Title', 1, 0, 'C');
        // // Saut de ligne
        // $this->Ln(20);
    }

    // function Footer() {
    //     // // Positionnement à 1,5 cm du bas
    //     // $this->SetY(-15);
    //     // // Police Arial italique 8
    //     // $this->SetFont('Arial', 'I', 8);
    //     // // Numéro de page
    //     // $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    // }

    function PageDeGarde($projet, $immeuble) {
        $photoImm = false;
        if ($immeuble && $immeuble->photoImmeuble != null && $immeuble->photoImmeuble !=  "" && file_exists("../../documents/immeuble/$immeuble->photoImmeuble")) {
            $photoImm = true;
        }

        if ($photoImm) {
            $this->SetY(35);
        } else {
            $this->SetY(($this->GetPageHeight() / 2) - 20);
        }

        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(0, 0, 0);
        $this->SetFillColor(255, 255, 255);
        $this->Cell(0, 25, $projet->nomProjet, 1, 0, 'C', true, '');

        if ($photoImm) {
            $this->SetY(65);
        } else {
            $this->SetY(($this->GetPageHeight() / 2) - 5);
        }
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(184, 0, 0);
        $this->SetX(60);
        $this->Cell(50, 8, "N° de l'immeuble", 0, 0, 'L', true, '');
        $this->SetX(105);
        $this->Cell(0, 8, $immeuble->numeroImmeuble, 0, 0, 'L', true, '');
        $this->Ln();

        if ($photoImm) {
            $this->Image(URLROOT  . "/public/documents/immeuble/$immeuble->photoImmeuble", 15, 75, 180, 120);
            $this->SetY(200);
        }

        $this->SetTextColor(0, 0, 0);
        $this->SetFont('Arial', 'BU', 12);
        $this->Cell(75, 10, 'Adresse :', 1, 0, 'J', true);
        $this->SetFont('Arial', '', 12);
        $this->Cell(115, 10, "$immeuble->adresse $immeuble->codePostal $immeuble->ville", 1, 0, 'J', true);
        $this->Ln();

    }

    function SectionTitre($num, $libelle, $fontSize=18, $x=0) {
        $niveau = 0;
        if ($num) {
            $niveau = substr_count($num, '.');
        }
        if ($niveau == 0) {
            $this->AddPage();
        }
        
        $this->EntrerDonnee($num . ' : ' . $libelle, $niveau, $this->GetY());
        $this->SetFont('Arial', 'B', $fontSize);
        if ($x != 0) {
            $this->setX($x);
        }
        $this->MultiCell(0, 10, $num.' : '.$libelle, 0, 'L');
    }
    
    function SectionContent($texte = '', $fontSize=14, $x=18) {
        $this->SetFont('Arial', '', $fontSize);
        $this->setX($x);
        $this->MultiCell(0, 6, $texte, 0, 'J');
        if ($texte != '') $this->Ln(3);
    }

    function ajouterSection($section, $fontSizeTitle=18, $fontSizeContent=14, $xTitle=0, $xContent=18) {
        $this->SectionTitre($section->numeroSection, $section->titreSection, $fontSizeTitle, $xTitle);
        if (trim($section->contenuSection) != "") {
            $this->SectionContent($section->contenuSection, $fontSizeContent, $xContent);
        }
    }
  
    // Fonction récursive pour gérer les sections
    function ajouterSectionsRecursives($projet, $sections, $niveau = 0) {
        $parametres = [
            0 => [16, 12, 00, 20],
            1 => [15, 12, 12, 20],
            2 => [14, 11, 15, 20],
            3 => [12, 11, 18, 20],
            4 => [12, 11, 20, 22]
        ];

        foreach ($sections as $section) {
            $params = $parametres[min($niveau, 4)];
            $this->ajouterSection($section, ...$params);
            
            $sous_sections = $this->projetModel->findSectionByIdSection($section->idSection);
            if (!empty($sous_sections)) {
                $this->ajouterSectionsRecursives($projet, $sous_sections, $niveau + 1);
            }
        }
    }
}
