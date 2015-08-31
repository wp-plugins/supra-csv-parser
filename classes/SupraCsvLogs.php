<?php
namespace SupraCsvFree;

require_once(dirname(__FILE__).'/SupraCsvPlugin.php');

class SupraCsvLogs extends SupraCsvPlugin {

    private $success = false;
    private $error;
    private $preview_num = 200;

    public function renderForms() { 
        echo $this->getLogs();
        echo '<div id="supra_csv_preview"></div>';
    }

    private function getLogs() {

        $list = null;
        $files = $this->getLogFiles();
        
        foreach($files as $i=>$file) {
            $delete_button = '<button id="delete_log" data-key="'.$i.'">Delete</button>';
            $download_button = '<button id="download_log" data-file="'.$file.'">Preview / Download</button>';
            $list .= '<li>'.$delete_button.$download_button.$file.'</li>';
        }

        return '<ul id="log_files">'.$list.'</ul>'; 
    }

    public function getLogFiles() {
        return array_diff((array)scandir($this->getPluginLogsDir()), array('..', '.'));
    }

    public function getFileByKey($key) {
        $files = $this->getLogFiles();
        return $files[$key];
    }

    public function deleteFileByKey($key) {

        $filename = $this->getFileByKey($key);

        $success = unlink($this->getPluginLogsDir() . $filename);

        if($success)
            $this->error = '<span class="success">Successfully deleted ' . $filename . '</span>';
        else
            $this->error = '<span class="error">Error deleting ' . $filename . '</span>';

        $this->renderForms();
    }

    function downloadFile($file) 
    {
        $filename_abs = $this->getPluginLogsDir() . $file;
        $filename_url = $this->getPluginLogsDirUrl() . $file;
        
        echo '<b>(showing First '.$this->preview_num.' lines)</b> or ' .
             '<a href="'.$filename_url.'" target="_blank">Download File</a>';

        $textFile = file_get_contents($filename_abs);

        $lineFromText = explode("\n", $textFile);

        $row = 0;

        echo "<br /><br />";       
 
        foreach($lineFromText as $line)
        {
            if($row <= $this->preview_num )
            {
                echo $line;
            }   
        }

    }
}
