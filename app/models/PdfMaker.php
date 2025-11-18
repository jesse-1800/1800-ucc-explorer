<?php namespace App\Models;

use Mpdf\Mpdf;
use Mpdf\MpdfException;
use Mpdf\Output\Destination;
use Mpdf\Config\FontVariables;
use Mpdf\Config\ConfigVariables;
use Mpdf\HTMLParserMode;
class PdfMaker
{
    public $instance;

    /**
     * @throws MpdfException
     */
    public function __construct($margin = 0)
    {
        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $this->instance = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => $margin,
            'margin_right' => $margin,
            'margin_top' => $margin,
            'margin_bottom' => $margin,
            'fontDir' => array_merge($fontDirs, [__DIR__ . '/../../assets/fonts']),
            'fontdata' => $fontData + [
                    'outfit' => [
                        'R' => 'Outfit-Regular.ttf',
                        'B' => 'Outfit-Bold.ttf',
                        'L' => 'Outfit-Light.ttf',
                    ],
                ],
            'default_font' => 'outfit',
        ]);

        $this->instance->shrink_tables_to_fit = 1;
        $this->instance->use_kwt = true;
        $this->instance->autoPageBreak = true;

        return $this->instance;
    }

    /**
     * @throws MpdfException
     */
    public function add_css($path)
    {
        $style = file_get_contents($path);
        $this->instance->WriteHTML($style,HTMLParserMode::HEADER_CSS);
    }

    /**
     * @throws MpdfException
     */
    public function add_page($html) {
        $this->instance->WriteHTML($html);
    }

    /**
     * Types:
     * - 'download' (returns download file)
     * - 'attachment' returns a softfile that can be attached
     * - 'default' just return a pdf viewed in a browser
     *
     * @param string $type
     * @param string $name
     * @return string
     * @throws MpdfException
     */
    public function export($type = 'default', $name = "Default"): string
    {
        $this->instance->SetTitle($name);

        if ($type == 'download') {
            return $this->instance->Output("{$name}.pdf", Destination::DOWNLOAD);
        }
        else if ($type == 'attachment') {
            return $this->instance->Output("{$name}.pdf", Destination::STRING_RETURN);
        }
        else {
            return $this->instance->Output("{$name}.pdf", Destination::INLINE); // Just view it
        }
    }
}
