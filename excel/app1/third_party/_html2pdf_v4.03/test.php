<?php 
ob_start();
include __DIR__.'/sampleTemplate.tpl';
$content = ob_get_clean();

require_once(dirname(__FILE__).'/html2pdf.class.php');
try
{
    $html2pdf = new HTML2PDF('P', 'A4', 'fr');
    $html2pdf->writeHTML($content, isset($_GET['vuehtml']));
    $html2pdf->Output('test.pdf');
}
catch(HTML2PDF_exception $e) {
    echo $e;
    exit;
}
