<?php
    namespace codesync;

    require_once __DIR__ . "/cs.php";
    
    $inp = array(
        'ver' => 'Unknown',
        'dev' => null,
        'op' => null,
        'sub' => null,
        'obj' => null,
        'files' => null
    );

    if(!isset($_REQUEST) || count($_REQUEST) == 0)
    {
        header("Content-Type: text/plain");
        echo "CodeSync server is currently working. This server supports the following versions:\n";
        
        foreach(scandir(__DIR__) as $k => $v )
        {
            $m = array();
            if(preg_match("/v(.+).php/", $v, $m))
            {
                echo " - Version {$m[1]} \n";
            }
        }
        exit;
    }

    foreach($_REQUEST as $ind =>$req)
    {
        $inp[$ind] = $req;
    }

    if(isset($_FILES))
    {
        $inp['files'] = $_FILES;
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