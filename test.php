<?php
/*************************************************************************
 File Name: test.php
 Author: chliny
 mail: chliny11@gmail.com
 Created Time: 2013年01月27日 星期日 23时09分56秒
 ************************************************************************/
require "rwDocument.php";

$rwfile = new rwDocument();

$content = $rwfile->read("/home/chliny/study/Linux/Linux_CPU_Usage_Analysis.pdf");

echo $content . "\n";
?>
