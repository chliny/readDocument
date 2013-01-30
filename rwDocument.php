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

    public function __construct(){

    }

    public function __destruct(){
        $command = 'rm -rf /tmp/rwDocument/*';
        exec($command);
    }

    /*
     * get the type of the file
     */
    private function getFileType($escapeFile){
        if(empty($escapeFile)){
            echo "ERROR:no file input!\n";
            exit;
        }
        $command = "file -i $escapeFile";
        $fileMime = exec($command);
        $matches =  array();
        preg_match('/: ((\w|\/)*?);/',$fileMime,$matches);
        return $matches[1];
    }

    /*
     * read the content in the File
     * @inputPath: the file to be readed or written
     * @operation: read or write
     * @return the content of the file
     */
    public function read($file,$type=NULL){
        if(empty($type))
            $type = $this->getFileType($file);
        $content;
        if($type == "application/pdf"){
            $content = $this->readPDF($file);
        }else if($type == "application/msword"){
            $content = $this->readWord($file);
        }else if($type == "application/zip"){
            $content = $this->readZIP($file);
        }else if($type == "inode/directory"){
            $content = $this->readDIR($file);
        }else if($type == "text/plain"){
            $content = $this->readTXT($file);
        }


        return $content;   
    }


    /*
    * read txt document
    */
    private function readTXT($file){
        $content = file_get_contents($file);
        return $content;
    }
        

    /*
     * read document in the directory
     */
    private function readDIR($file){
        $file = trim($file);
        if($file[strlen($file)-1] != '/'){
            $file .= '/';
        }
        $dir = opendir($file);
        $content;
        while(false !== ($document = readdir($dir))){
            if($document[0] == '.')
                continue;
            $name = $file . $document;
            $result  = $this->read($name);
            /*if(is_array($result)){
                foreach($result as $reName => $reDocu){
                    $content[$document . '/' . $reName] = $result;
                }
            }else{
                $content[$document] = $result;
            }*/
            $content[$document] = $result;
        }
        closedir($dir);
        return $content;
    }

    /*
     * read ms word document;
     */
    private function readWord($file){
        $fileArr = explode('.',$file);
        $suffix = $fileArr[1];
        $suffix = trim($suffix);
        $content;
        if($suffix == "doc"){
            $content = $this->readDOC($file);
        }else if($suffix == "docx"){
            $content = $this->readDOCX($file);
        }else{
            echo "could mot confirm the document!\n";
            exit;
        }
        return $content;
    }

    /*
     * read ms word with DOC suffix
     */
    private function readDOC($file){
        $escapeFile = escapeshellarg($file);
        $command = "catdoc $escapeFile";
        return $this->writeContent($command);
    }

    /* 
     * read ms word with DOCX suffix
     */
    private function readDOCX($file){
        $docxZip = new ZipArchive;
        $docxZipRes = $docxZip->open($file);
        if($docxZipRes === true){
            $content = $docxZip->getFromName('word/document.xml');     
            $content = preg_replace('/\<\/w\:p\>/',"\n",$content);
            $content = strip_tags($content,"\n");
            $docxZip->close($file);
        }else{
            echo "ERROR: $docxZipRes\n";
            exit;
        }
        return $content;
    }

    /*
     * read zip document
     */
    private function readZIP($file){
        $zip = new ZipArchive;
        $zipRes = $zip->open($file);
        $thisContent;
        if($zipRes === true){
            $zipLen = $zip->numFiles;
            for($index=0;$index < $zipLen;++$index){
                $zipStat = $zip->statIndex($index);
                if($zipStat['comp_method'] == 0) //dir
                    continue;

                $name = $zipStat['name'];
                if(preg_match('/\.pdf|\.doc|\.xls\.xlsx/i',$name) > 0){
                    $extract = $zip->extractTo('/tmp/rwDocument/',$name);
                    $thisFile = "/tmp/rwDocument/$name";
                    $content = $this->read($thisFile);
                    $thisContent[$name] = $content;

                    if(preg_match('/\.docx/',$name) > 0){
                        ++$index;
                        $zipStat = $zip->statIndex($index);
                        while(preg_match('/\.xml|\.rels/',$zipStat['name']) > 0){
                            ++$index;
                            $zipStat = $zip->statIndex($index);
                        }
                        --$index;
                    }
                }else
                    $thisContent[$name] = $zip->getFromIndex($index);
            }
            $zip->close();
        }else{
            echo "ERROR: $zipRes\n";
            exit;
        }
        return $thisContent;
    }

    /*
     * read PDF document
     */
    private function readPDF($file){
        $escapeFile = escapeshellarg($file);
        $command = "pdftotext $escapeFile -";
        $this->writeContent($command);
    }

    /*
     * execute the $command and write the
     */
    private function writeContent($command){
        $output = array();
        exec($command,$output);
        $content = implode("\n",$output);
        return $content;
    }
}

?>
