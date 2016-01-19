<?php
    require_once __DIR__ . "/cs.php";
    $cspath = __DIR__ . '/' . CS::getCompatibilityVersion($_REQUEST['ver']) . 'php';
    if(file_exists($cspath))
    {
        require_once $cspath;
    }
    else
    {
        echo "E\tCodesync version '{$_REQUEST['ver']}' handler has not been found on the server although it should be present\n";
        exit;
    }

    
    $query = array(
        'version' => CS::getCompatibilityVersion($_REQUEST['ver']),
        'device' => $_REQUEST['dev'],
        'operation' => $_REQUEST['op'],
        'subject' => $_REQUEST['sub'],
        'object' => $_REQUEST['obj']
    );

    $cs = new CodeSync($query);
    echo $cs->execute();
?>