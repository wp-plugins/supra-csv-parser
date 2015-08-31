<?php
namespace SupraCsvFree;

require_once(dirname(__FILE__).'/SupraCsvPlugin.php');

class UploadCsv extends SupraCsvPlugin {

    private $success = false;
    private $error;
    private $preview_num = 200;

    function __construct($file = null) {
        parent::__construct();

        if(!empty($file['uploaded'])) {
            $this->processFile($file['uploaded']);
        }

        if(!file_exists($this->getCsvDir())) {
            mkdir($this->getCsvDir(),0777,true);
            chmod($this->getCsvDir(),0777);
        }
    }

    public function renderForms() { 
        echo '<div id="response">'.$this->getErrorMsg().'</div>'; 
        echo $this->getForm();
        echo $this->getUploads();
        echo '<div id="supra_csv_preview"></div>';
    }

    public function getSuccess() {
        return $this->success;
    }

    public function getErrorMsg() {
        return $this->error;
    }

    private function validateFileType($type) {
        return true;
    }

    private function processFile($file) {
        if($this->validateFileType($file['type'])) {
            $this->error = '<span class="error">Something went wrong.</span>';
            $target = $this->getCsvDir() . basename( $file['name']); 
 
            if(move_uploaded_file($file['tmp_name'], $target)) {
                $this->success = true;
                $this->error = '<span class="success">' . $file['name'] . " successfully uploaded</span>";
            }
        }
    }

    public function writeToFile($filename, $contents) {

       return file_put_contents($this->getCsvDir() . $filename, $contents);
    }

    private function getForm() {

            return '<form enctype="multipart/form-data" method="POST">
            Please choose a file: <input name="uploaded" type="file" />
            <input type="submit" value="Upload" />
            </form>';
    }

    private function getUploads() {

        $list = null;
        $files = $this->getUploadedFiles();
        
        foreach($files as $i=>$file) {
            $delete_button = '<button id="delete_upload" data-key="'.$i.'">Delete</button>';
            $download_button = '<button id="download_upload" data-file="'.$file.'">Preview / Download</button>';
            $debug_button = '<button id="debug_upload" data-file="'.$file.'">Debug</button>';
            $list .= '<li>'.$delete_button.$download_button.$debug_button.$file.'</li>';
        }

        return '<ul id="uploaded_files">'.$list.'</ul>'; 
    }

    public function getUploadedFiles() {
        return array_diff((array)scandir($this->getCsvDir()), array('..', '.'));
    }

    public function getFileByKey($key) {
        $files = $this->getUploadedFiles();
        return $files[$key];
    }

    public function deleteFileByKey($key) {

        $filename = $this->getFileByKey($key);

        $success = unlink($this->getCsvDir() . $filename);

        if($success)
            $this->error = '<span class="success">Successfully deleted ' . $filename . '</span>';
        else
            $this->error = '<span class="error">Error deleting ' . $filename . '</span>';

        $this->renderForms();
    }

    function debugFile($file)
    {
        $debug['csv_filename_abs'] = $this->getCsvDir() . $file;
        $debug['csv_filename_url'] = $this->getCsvDirUrl() . $file;


        $csv_mapping_file = file_get_contents($this->getPluginChunkDir() . $file . ".mapping");

        if(file_exists($csv_mapping_file)) {
            $debug['csv_mapping_file_contents'] = file_get_contents($csv_mapping_file);
        }

        $debug['settings'] = $this->getSettings();

        ob_start();
        phpinfo();
        $debug['phpinfo'] = strip_tags(ob_get_contents());
        ob_end_clean();

        $debug['phpinfo_cli'] = shell_exec("php --info");


        echo json_encode($debug, true);
    }


    function downloadFile($file) 
    {
        $filename_abs = $this->getCsvDir() . $file;
        $filename_url = $this->getCsvDirUrl() . $file;
        
        echo '<b>(showing First '.$this->preview_num.' lines)</b> or ' .
             '<a href="'.$filename_url.'" target="_blank">Download File</a>';
        $row = 1;

        $delimiter_tag = "th";

        echo '<table class="tablesorter-blue"><thead>';

        $csvLines = $this->parseLines($filename_abs);

        //$this->getLogger()->info(__METHOD__ . var_export($csvLines, true));

        foreach($csvLines as $data)
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
    }

    public function displayFileSelector() {

        $options = '<option value=""></option>'; 

        foreach($this->getUploadedFiles() as $key=>$file) {
            $options .= '<option value="'.$key.'">'.$file.'</option>';
        } 

        echo '<label for="select_csv_file">File To Ingest:</label><select id="select_csv_file">'.$options.'</select>';
    }
}
