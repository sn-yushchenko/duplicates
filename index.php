<?php

class noDublicate extends Exception{}
class noDir extends Exception{}

class File
{
    private $path;//задаваемый путь
    private $duplicateFile;//файл для сохранения пути дубликатов
    public $arrayFiles;//содержит массив всех файлов проверяемого пути 
    
    public function __construct($path=null)
    {
        $file=getcwd().DIRECTORY_SEPARATOR."duplicates.txt";
        if(file_exists($file))
        {
            unlink($file);
        }
        $this->arrayFiles=array();
        $this->path=$path;
        $this->duplicateFile=$file;
        $this->arrayFiles=$this->allFiles();
    }
    
    private function allFiles($path=null)//метод выбирает все файлы каталогов указанной директории
    {
        static $array=array();
        if(!$path){$path=$this->path;}
        if(file_exists($path))
        {

            foreach(scandir($path) as $element)
            {
            if($element == "." || $element == "..") continue;
            if(is_dir($element))
            {
              $this->allFiles($path.DIRECTORY_SEPARATOR.$element);
            }
            else
            {
              $array[filesize($path.DIRECTORY_SEPARATOR.$element)][]=$path.DIRECTORY_SEPARATOR.$element;
            }
            }
        }
        else
        {
        throw new noDir('Неверно указан путь, проверьте правильность ввода!');
        }
        return $array;
    }
    
    public function selectDublicate()//метод выбирает дубликаты найденные по указанному пути
    {
        $arrayDublicate=$this->arrayFiles;
        $resultArray=array();
        
        foreach($arrayDublicate as $key=>$value)
        {
            if(count($value)==1)
            {
                unset($arrayDublicate[$key]);
            }
            else
            {
                foreach($value as $index=>$element)
                {
                    $counter=0;
                    $checkFile=fopen($element,'rb');

                    for($i=0;$i<count($value);$i++)
                    {
                        if($index==$i)
                        {
                            continue;
                        }
                        $compareFile=fopen($value[$i],'rb');
                        while($content=fread($checkFile,4))  
                        {
                            if($content==fread($compareFile,4))
                            {
                                $counter+=4;
                            }
                            else
                            {
                                fclose($compareFile);
                                break;
                            } 
                        }
                        if($counter>=filesize($element))
                        {
                            $resultArray[]=$element;
                        }
                    }
                    fclose($checkFile);
                }
            }
        }
        return $resultArray;
    }
    
    public function getDublicateFile()
    {
        $result=false;
        $dublicates=$this->selectDublicate();
        foreach($dublicates as $element)
        {
             $result=file_put_contents($this->duplicateFile,$element.PHP_EOL,FILE_APPEND);
        }
        if( $result==false)
        {
            throw new noDublicate('По заданному пути дубликатов нет!');
        }
    }
}

/*Проверяем наличие дубликатов*/
try {
    $path="C:\apache\htdocs\www/duplicates";
    $obj=new File($path);
    $obj->selectDublicate();
    $obj->getDublicateFile();
} catch (Exception $e) {
    echo $e->getMessage()."\n";
}

?>