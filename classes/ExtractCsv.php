<?php
require_once(dirname(__FILE__).'/SupraCsvPlugin.php');
class ExtractCsv extends SupraCsvPlugin {

    private $mimes   = array("text/csv","text/comma-separated-values",'application/vnd.ms-excel','text/plain','text/tsv');
    private $success = false;
    private $error;
    private $preview_num = 200;

    function __construct($file = null) {
        parent::__construct();

        if(!file_exists($this->getExtractedCsvDir())) {
            mkdir($this->getExtractedCsvDir(),0777,true);
            chmod($this->getExtractedCsvDir(), 0777);
        }
    }
 
    public function getForms() {

        $form = '<div id="response">'.$this->getErrorMsg().'</div>' . 
          $this->getExtracts() . 
          '<div id="supra_csv_preview"></div>';

        return $form;
    }   

    public function renderForms() { 
        echo $this->getForms(); 
    }

    public function getSuccess() {
        return $this->success;
    }

    public function getErrorMsg() {
        return $this->error;
    }

    private function getExtracts() {
        $files = $this->getExtractedFiles();

        $list = null;
        
        foreach($files as $i=>$file) {
            $delete_button = '<button id="delete_extract" data-key="'.$i.'">Delete</button>';
            $download_button = '<button id="download_extract" data-file="'.$file.'">Preview / Download</button>';
            $list .= '<li>'.$delete_button.$download_button.$file.'</li>';
        }

        return '<ul id="uploaded_files">'.$list.'</ul>'; 
    }

    public function getExtractedFiles() {
        return array_diff((array)scandir($this->getExtractedCsvDir()), array('..', '.'));
    }

    public function getFileByKey($key) {
        $files = $this->getExtractedFiles();
        return $files[$key];
    }

    public function writeToFile($filename, $contents) {

       return file_put_contents($this->getExtractedCsvDir() . $filename, $contents);
    }

    public function deleteFileByKey($key) {

        $filename = $this->getFileByKey($key);

        $success = unlink($this->getExtractedCsvDir() . $filename);

        if($success)
            $this->error = '<span class="success">Successfully deleted ' . $filename . '</span>';
        else
            $this->error = '<span class="error">Error deleting ' . $filename . '</span>';

        $this->renderForms();
    }

    function downloadFile($file) {
        $filename_abs = $this->getExtractedCsvDir() . $file;
        $filename_url = $this->getExtractedCsvDirUrl() . $file;

        echo '<b>(showing First '.$this->preview_num.' lines)</b> or ' .
             '<a href="'.$filename_url.'" target="_blank">Download File</a>';
        $row = 1;

        $csv_settings = get_option('scsv_csv_settings');

        $delimiter_tag = "th";

        echo '<table class="tablesorter-blue"><thead>';

        if (($handle = fopen($filename_abs, "r")) !== FALSE)
        {
            while (($data = $this->parseNextLine($handle,$csv_settings)) !== FALSE)
            {
                $row++;

                $delimiter = "</{$delimiter_tag}><{$delimiter_tag}>";

                echo "<tr><{$delimiter_tag}>" . implode($delimiter,$data) . "</{$delimiter_tag}></tr>";

                if($row==$this->preview_num) break;

                if($row == 2)
                {
                    $delimiter_tag = "td";

                    echo "</thead><tbody>";
                }
            }

            echo "</tbody></table>";

            fclose($handle);
        }
    }


    private function parseNextLine($handle,$csv_settings) {
        if (strnatcmp(phpversion(),'5.3') >= 0) { 
            return fgetcsv($handle,1000,stripslashes($csv_settings['delimiter']),stripslashes($csv_settings['enclosure']),stripslashes($csv_settings['escape']));
 
        } 
        else { 
            return fgetcsv($handle,1000,stripslashes($csv_settings['delimiter']),stripslashes($csv_settings['enclosure']));
        } 
    }
}
