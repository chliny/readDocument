<?php
/*************************************************************************
 File Name: rwDocument.php
 Author: chliny
 mail: chliny11@gmail.com
 Created Time: 2013年01月27日 星期日 12时51分20秒
 ************************************************************************/
/*
 * depends on xpdf catdoc
 */
class rwDocument{
    /*
     * the path of the file to be readed or written
     */
    private $file;

    /*
     * $file escapeshellarg
     */
    private $escapeFile;

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
        $command = "file -i $this->escapeFile";
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
        $this->file = $inputPath;
        $this->escapeFile = escapeshellarg($inputPath);
        if(empty($this->type))
            $this->getFileType();

        if($this->type == "application/pdf"){
            $this->readPDF();
        }else if($this->type == "application/msword"){
            $this->readWord();
        }else if($this->type == "application/zip"){
            $this->readZIP();
        }

        return $this->content;   
    }

    /*
     * read ms word document;
     */
    private function readWord(){
        $fileArr = explode('.',$this->file);
        $suffix = $fileArr[1];
        $suffix = trim($suffix);
        if($suffix == "doc"){
            $this->readDOC();
        }else if($suffix == "docx"){
            $this->readDOCX();
        }else{
            echo "could mot confirm the document!\n";
            exit;
        }
    }

    /*
     * read ms word with DOC suffix
     */
    private function readDOC(){
        $command = "catdoc $this->escapeFile";
        $this->writeContent($command);

    }

    /* 
     * read ms word with DOCX suffix
     */
    private function readDOCX(){
        $this->readZIP();
        $this->content = $this->content['word/document.xml']; 
        $this->content = preg_replace('/\<\/w\:p\>/',"\n",$this->content);
        $this->content = strip_tags($this->content,"\n");
    }

    /*
     * read zip document
     */
    private function readZIP(){
        $zip = new ZipArchive;
        $zipRes = $zip->open($this->file);
        if($zipRes === true){
            $index = 0;
            $zipLen = $zip->numFiles;
            for(;$index < $zipLen;++$index){
                $zipStat = $zip->statIndex($index);
                if($zipStat['comp_method'] == 0) //dir
                    continue;
                $name = $zipStat['name'];
                $this->content[$name] = $zip->getFromIndex($index);
            }
            $zip->close();
        }else{
            echo "ERROR:" . $zipRes . "\n";
            exit;
        }
    }

    /*
     * read PDF document
     */
    private function readPDF(){
        $command = "pdftotext $this->escapeFile -";
        $this->writeContent($command);
    }

    /*
     * execute the $command and write the
     */
    private function writeContent($command){
        $output = array();
        exec($command,$output);
        $this->content = implode("\n",$output);
    }
}

?>
