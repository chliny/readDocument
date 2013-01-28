<?php
/*************************************************************************
 File Name: rwDocument.php
 Author: chliny
 mail: chliny11@gmail.com
 Created Time: 2013年01月27日 星期日 12时51分20秒
 ************************************************************************/
class rwDocument{
    /*
     * the path of the file to be readed or written
     */
    private $file;

    /*
     * the content in the file
     */
    public $content;

    /*
     * the type of the file
     */
    private $type;

    /*
     * initiates a new rwDocument 
     */
    public function __construct(){
        
    }
        
    /*
     * get the type of the file
     */
    private function getFileType(){
        if(empty($this->file)){
            echo "ERROR:no file input!\n";
            exit;
        }
        $command = "file -i $this->file";
        $fileMime = exec($command);
        $matches =  array();
        preg_match('/: ((\w|\/)*?);/',$fileMime,$matches);
        $this->type = $matches[1];
    }

    /*
     * read the content in the File
     * @inputPath: the file to be readed or written
     * @operation: read or write
     * @return the content of the file
     */
    public function read($inputPath){
        $this->file = escapeshellarg($inputPath);
        if(empty($this->type))
            $this->getFileType();

        if($this->type == "application/pdf"){
            $this->readPDF();
        }

        return $this->content;   
    }

    /*
     * read PDF document
     */
    private function readPDF(){
        $command = "pdftotext $this->file -";
        echo $command ."\n";
        $output = array();
        exec($command,$output);
        $this->content = implode("\n ",$output);
    }
}

?>
