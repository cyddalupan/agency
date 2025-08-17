<?php

$html = file_get_contents( __DIR__.'/template.php' );

require_once __DIR__.'/../mpdf.php';

$mpdf=new mPDF(); 

$mpdf->SetDisplayMode('fullpage');

$mpdf->WriteHTML($html);

$mpdf->Output(); 

exit;

//==============================================================
//==============================================================
//==============================================================


?>