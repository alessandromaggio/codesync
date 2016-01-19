<?php
    /*
     * CodeSync processor - v2
     */

    class CodeSync
    {
        const 
            type_SYSTEM = 1,
            type_VERSION = 2,
            type_PROJECT = 3,
            type_FOLDER = 4,
            type_FILE = 5;
        
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
                $this->data[$k] => $v;
            }
        }
        
        public function execute()
        {
            
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
                'type' => self::type_SYSTEM,
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
            
        }
        
        protected function getFolder()
        {
            
        }
        
        protected function getFile()
        {
            
        }
        
        protected function scanDir($dir)
        {
            
        }
    }
?>