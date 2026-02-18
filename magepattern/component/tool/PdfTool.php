<?php

namespace Magepattern\Component\Tool;

use Dompdf\Dompdf;
use Dompdf\Options;
use Magepattern\Bootstrap;

class PdfTool
{
    /**
     * Génère un PDF à partir d'un contenu HTML
     * @param string $html     Le code HTML
     * @param string $filename Nom du fichier en sortie
     * @param bool   $stream   Si true, affiche (inline), sinon retourne le binaire
     * @param string $paper    Format (A4, Letter, etc.)
     * @param string $orient   Orientation (portrait ou landscape)
     */
    public static function generate(
        string $html,
        string $filename = 'document.pdf',
        bool $stream = true,
        string $paper = 'A4',
        string $orient = 'portrait'
    ) {
        Bootstrap::getInstance()->load('dompdf');

        $rootDir = dirname(__DIR__, 3);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');
        $options->set('fontCache', $rootDir . '/var/pdf/fonts');

        // Sécurité : On autorise Dompdf à lire les fichiers locaux à partir de la racine
        $options->set('chroot', $rootDir);

        // Vérification du dossier de cache polices
        if (!is_dir($options->getFontCache())) {
            mkdir($options->getFontCache(), 0775, true);
        }

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orient);

        $dompdf->render();

        if ($stream) {
            // "Attachment" => false permet d'ouvrir dans le navigateur sans forcer le téléchargement
            $dompdf->stream($filename, ["Attachment" => false]);
            exit;
        }

        return $dompdf->output();
    }
}