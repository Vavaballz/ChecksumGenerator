<?php
/**
 * @author Vavaballz
 * Class ChecksumGenerator
 */
class ChecksumGenerator{

    private $dir;
    private $filename;
    private $usedMethod;

    private $xml;
    private $json = array();
    private $array = array();

    const AS_XML = 0;
    const AS_JSON = 1;
    const AS_ARRAY = 2;


    public function __construct(){
        $this->filename = str_replace(".php", "", str_replace(__DIR__ . DIRECTORY_SEPARATOR, "", __FILE__));
    }

    /**
     * Set the dir path
     * @param $dir
     */
    public function setDir($dir){
        $this->dir = $dir;
    }

    /**
     * Set the file name
     * @param $filename
     */
    public function setFilename($filename){
        $this->filename = $filename;
    }

    public function setUsedMethod($method){
        $this->usedMethod = $method;
    }

    /**
     * This func is to generate a XML file
     * which contain files path, md5 checksum
     * and size.
     */
    public function generate(){
        $firstCall = false;
        if($this->usedMethod == Self::AS_XML && $this->xml == null){
            $this->xml = new SimpleXMLElement('<ListBucketResult/>');
            $firstCall = true;
        }else if($this->usedMethod == Self::AS_JSON && $this->json == null){
            $json_array = array();
            $firstCall = true;
        }else if($this->usedMethod == Self::AS_ARRAY && $this->array == null){
            $file_array = array();
            $firstCall = true;
        }
        $caller = debug_backtrace();
        if(isset($caller[1]) && func_get_args()){
            $args = func_get_args();
            $dir = $args[0];
        }else{
            $dir = $this->dir;
        }

        if(is_dir($dir)){
            if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    if ($file === '.' || $file === '..') continue;
                    if(filetype($dir.$file) == 'dir'){
                        $this->generate($dir.$file."/");
                    }else{
                        if($this->usedMethod == Self::AS_XML){
                            $f = $this->xml->addChild('Contents');
                            $f->addChild('Key', str_replace('/', '\\', '\\'.$dir.$file));
                            $f->addChild('ETag', "\"".md5_file($dir.$file)."\"");
                            $f->addChild('Size', filesize($dir.$file));
                        }else if($this->usedMethod == Self::AS_JSON){
                            $json_array = array(
                                'file' => array(
                                    'path' => str_replace('/', '\\', '\\'.$dir.$file),
                                    'md5'  => md5_file($dir.$file),
                                    'size' => filesize($dir.$file)
                                ),
                            );
                            array_push($this->json, $json_array);
                        }else if($this->usedMethod == Self::AS_ARRAY){
                            $file_array = array(
                                'file' => array(
                                    'path' => str_replace('/', '\\', '\\'.$dir.$file),
                                    'md5'  => md5_file($dir.$file),
                                    'size' => filesize($dir.$file)
                                ),
                            );
                            array_push($this->array, $file_array);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }

    /**
     * Save as file
     * The file type is set according
     * to the generating method used.
     */
    public function save(){
        if($this->usedMethod == Self::AS_XML){
            $this->xml->saveXML(__DIR__.DIRECTORY_SEPARATOR.$this->filename . ".xml");
            $this->xml = null;
        }else if($this->usedMethod == Self::AS_JSON){
            $json = json_encode($this->json);
            file_put_contents(__DIR__.DIRECTORY_SEPARATOR.$this->filename.'.json', $json);
            $this->array = null;
        }else if($this->usedMethod == Self::AS_ARRAY) {
            echo "You can't use the save method for the <b>ARRAY</b> method";
            $this->array = null;
        }
    }

    /**
     * Get the generated
     * into a var
     * @return mixed
     */
    public function get(){
        if($this->usedMethod == Self::AS_XML){
            $xml = $this->xml;
            $this->xml = null;
            return $xml;
        }else if($this->usedMethod == Self::AS_JSON){
            $json = json_encode($this->json);
            $this->json = null;
            return $json;
        }else if($this->usedMethod == Self::AS_ARRAY){
            $array = $this->array;
            $this->array = null;
            return $array;
        }
    }
}
