<?php
namespace codesync;

class CS
{
    const VER = "v2";
    const ROOT = "/";
    
    private static function getVersionList()
    {
        return array(
            "v1" => 10000,
            "v2" => 20000
        );
    }
    
    public static function getCompatibilityVersion($clientVersion)
    {
        $verlist = self::getVersionList();
        if(!array_key_exists(self::VER, $verlist))
        {
            echo "ERROR - Undefined server version";
            exit;
        }
        
        if(array_key_exists($clientVersion, $verlist))
        {
            if($verlist[$clientVersion] >= $verlist[self::VER])
            {
                return self::VER;
            }
            elseif($verlist[$clientVersion] < $verlist[self::VER])
            {
                return $clientVersion;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return self::VER;
        }
    }
    
    public static function getRoot()
    {
        return (self::ROOT == "/") ? __DIR__ : ROOT;
    }
}
?>