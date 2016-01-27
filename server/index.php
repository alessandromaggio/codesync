<?php
    namespace codesync;

    require_once __DIR__ . "/cs.php";
    
    $inp = array(
        'ver' => 'Unknown',
        'dev' => null,
        'op' => null,
        'sub' => null,
        'obj' => null
    );

    foreach($_REQUEST as $ind =>$req)
    {
        $inp[$ind] = $req;
    }

    $ver = CS::getCompatibilityVersion($inp['ver']);

    if($ver === false)
    {
        echo "E\tCodesync version '{$inp['ver']}' does not exist.\n";
        exit;
    }

    $cspath = __DIR__ . '/' . $ver . '.php';

    if(file_exists($cspath))
    {
        require_once $cspath;
    }
    else
    {
        echo "E\tCodesync version '{$inp['ver']}' handler has not been found on the server although it should be present\n";
        exit;
    }

    
    $query = array(
        'version' => $ver,
        'device' => $inp['dev'],
        'operation' => $inp['op'],
        'subject' => $inp['sub'],
        'object' => $inp['obj']
    );

    $cs = new CodeSync($query);
    echo $cs->execute();
?>