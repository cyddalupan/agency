<?php
$hook['post_controller_constructor'][] = array(
    'function' => 'redirect_ssl',
    'filename' => 'sslfile.php',
    'filepath' => 'hooks'
);
?>