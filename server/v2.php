<?php
    /*
     * CodeSync processor - v2
     */

    namespace codesync;

    require_once __DIR__ . "/cs.php";

    class CodeSync
    {
        const 
            type_ERROR = -1,
            type_SYSTEM = 1,
            type_VERSION = 2,
            type_PROJECT = 3,
            type_FOLDER = 4,
            type_FILE = 5;
        
        const
            header_CAN_DOWNLOAD = 1,
            header_MUST_READ = 2;
        
        private $data = array(
                'version' => null,
                'device' => null,
                'operation' => null,
                'subject' => null,
                'object' => null,
                'pushData' => null
            );
        
        function __construct($qry)
        {
            foreach($qry as $k => $v)
            {
                $this->data[$k] = $v;
            }
        }
        
        public function execute()
        {
            $out = array();
            $out[] = $this->getResponder();
            $out[] = $this->getResponseVersion();
            $out[] = null;
            
            if($this->data['subject'] == 'file')
            {
                $this->setHeader(self::header_CAN_DOWNLOAD);
               
                echo $this->getWhatAsked();
            }
            else
            {
                $this->setHeader(self::header_MUST_READ);
                foreach($this->getWhatAsked() as $r)
                {
                    $out[] = $r;
                }
                foreach($out as $line)
                {
                    echo $this->lineNormalizer($line);
                }
            }
        }
        
        protected function browserIsCodeSyncClient()
        {
            switch($_SERVER['HTTP_USER_AGENT'])
            {
                case "CS-Client 2.0":
                    return true;
                    break;
            }
            
            return false;
        }
        
        protected function setHeader($purpose)
        {
            switch($purpose)
            {
                case self::header_CAN_DOWNLOAD:
                    if($this->browserIsCodeSyncClient())
                    {
                        header('Content-Type: application/octet-stream');
                    }
                    else
                    {
                         header("Content-Type: text/plain");
                    }
                    break;
                case self::header_MUST_READ:
                    header("Content-Type: text/plain");
                    break;
            }
        }
        
        protected function lineNormalizer($lnobj)
        {
            if(!is_array($lnobj) || !isset($lnobj['type']))
            {
                return "\n";
            }
            
            $output = "\n";
            
            switch($lnobj['type'])
            {
                case self::type_SYSTEM:
                    $output = "S\t";
                    break;
                case self::type_VERSION:
                    $output = "V\t";
                    break;
                case self::type_PROJECT:
                    $output = "P\t";
                    break;
                case self::type_FOLDER:
                    $output = "D+\t";
                    break;
                case self::type_FILE:
                    $output = "F\t";
                    break;
                case self::type_ERROR;
                    $output = "E\t";
                default: break;
            }
            
            if($output == "\n")
            {
                return $output;
            }
            
            return $output . implode("\t", $lnobj['content']) . "\n";
        }
        
        protected function getResponder()
        {
            return array(
                'type' => self::type_SYSTEM,
                'content' => array(
                    $_SERVER['HTTP_HOST']
                )
            );
        }
        
        protected function getResponseVersion()
        {
            return array(
                'type' => self::type_VERSION,
                'content' => array(
                    $this->data['version']
                )
            );
        }
        
        protected function getWhatAsked()
        {
            switch($this->data['subject'])
            {
                case 'project':
                    return $this->getProject();
                    break;
                case 'folder':
                    return $this->getFolder();
                    break;
                case 'file':
                    return $this->getFile();
                    break;
                case null:
                    break;
            }
            
            return null;
        }
        
        protected function getProject()
        {
            return $this->getFolder();
        }
        
        protected function getFolder()
        {
            return $this->scanDir($this->getInputAsPath());
        }
        
        protected function getFile()
        {
            if(file_exists($this->getInputAsPath()))
            {
                $fh = fopen($this->getInputAsPath(), "r");
                $fbytes = fread($fh, filesize($this->getInputAsPath()));
                fclose($fh);
                return $fbytes;
            }
            else
            {
                return null;
            }
        }
        
        protected function getInputAsPath()
        {
            return CS::getRoot() . '/' . $this->data['device'] . '/' . $this->data['object'];
        }
        
        protected function remoteAccessiblePath($path, $type)
        {
            return str_replace(CS::getRoot() . '/' . $this->data['device'], 
                               'http://' . $_SERVER['HTTP_HOST'] . '/' . $this->data['version'] . '/device:' . $this->data['device'] . '/pull/' . $type, 
                               $path);
        }
        
        protected function scanDir($dir)
        {
            if(!file_exists($dir))
            {
                return array(
                        array(
                            'type' => self::type_ERROR,
                            'content' => array(
                                    '101',
                                    "Directory '$dir' does not exists'."
                                )
                        )
                    );
            }
            
            $c = scandir($dir);
            $out = array();
            foreach($c as $k => $v)
            {
                $el = array();
                $path = $dir .'/' . $v;
                if($v == '.' || $v == '..')
                {
                    continue;
                }
                
                if( is_dir($path) )
                {
                    $el[] = date("r", filemtime($path));
                    $el[] = $this->remoteAccessiblePath($path, 'folder');
                    
                    $out[] = array(
                            'type' => self::type_FOLDER,
                            'content' => $el
                        );
                    
                    foreach($this->scanDir($path) as $r)
                    {
                        $out[] = $r;
                    }
                }
                else
                {
                    $el[] = date("r", filemtime($path));
                    $el[] = $this->remoteAccessiblePath($path, 'file');
                    
                    $out[] = array(
                            'type' => self::type_FILE,
                            'content' => $el
                        );
                }
            }
            
            return $out;
        }
    }
?>