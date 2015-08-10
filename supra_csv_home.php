<div class="wrap">
  <div style="width: 630px">
  <h2>Supra Csv</h2>
  <div style="float: left; width: 300px;">
<style type="text/css">
#limited_offer {
background-color: darkred;
color: white;
padding: 10px;
}
#limited_offer span {
    
    font-weight: bold;
    font-size:22px;
}
</style>

    <h3>Description</h3>
    <p>
      The purpose of this plugin is to parse uploaded csv files into any type of
      post. Themes or plugin store data in posts and this plugin provides the functionality
      to upload data from the csv file to the records that the theme or plugin creates.
      Manage existing csv files and promote ease of use by creating presets for both postmeta
      and ingestion mapping. For more infomation on how to obtain the necessary info watch the
      detailed tutorials. To ingest csv files into custom posts or extract posts into csv files
      you must upgrade to the premium version of the plugin.
    </p>
    <h3>Steps to Ingest</h3>
    <ol>
      <li>configure in 'Configuration'</li>
       <li>upload file in 'Upload'</li>
      <li>define postmeta in 'Post Info'</li>
      <li>map the data and import in 'Ingestion'</li>
      <li>save postmeta and mapping presets wherever necessary</li>
    </ol>
    <h3>Importing Terms by Taxonomy</h3>
    <p>provide a comma separated value in the <a href="#custom_terms">custom terms</a> input below<br />
      Exa: enginesize,pricerange<br />
      The mapping selectors will dynamically appear in the ingest page.
    </p>
 
    <h3>Importing complex categories</h3>
    <p>If you desire to import subcategories and deatiled info about the category such
       as the slug, description and parent mark the checkbox in the <a href="#compex_categories">complex categories</a>
    </p>
  </div>
  <div style="float: right;width: 300px;">
    <h3>Rapid Releases</h3>
    <p>There are times when new releases are available and may contain bugs. if you encounter any issues with the plugin ingestion be sure to toggle ingestion debugging by checking the <a href="#ingestion_debugging">box</a> and provide the debug output in the <a href="http://wordpress.org/support/plugin/supra-csv-parser" target="_blank">support forum</a> to get the problem solved quickly.</p>
    <h3>Issue Reporting</h3>
    <p>
      If you are experiencing issues be sure to turn the ingestion debugger on as mentioned above.
      There is an area where you can copy the Plugin Settings on the configuration page. Please provide these settings and the CSV file that is causing issues. It would also be very helpful to provide the error log for the day the issues were experiened.
    </p>
    <h3>Error Logging</h3>
    <p>
      All errors are logged in a log file for each days. These log files are located in the logs directory at the root of the plugin. Regardless of whether or not you have php error reporting enabled or disabled all php error will show here.
    </p>
    
    <h3>Donations</h3>
    <p>Additional requests or feeling generous, feel free to donate!</p>
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="hosted_button_id" value="CLC8GNV7TRGDU">
    <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
    </form>

    <h3>Video Tutorials</h3>
    <p>Feel free to browse through the video tutorial series 
    <a href="http://www.supraliminalsolutions.com/blog/supra-csv-tutorials/" target="_blank">here</a>.</p>
  </div>
  <div style="clear: both"></div>
  <div id="installation_errors">
  
  <?php

    $pluginErr = (array) get_option('supracsvplugin_error');

    if(array_key_exists('details',$pluginErr)) {
        if(!empty($pluginErr['details']))
        {  
            echo "<h2>Plugin Installation Errors (".$pluginErr['date'].")</h2>";
            echo "<p>".$pluginErr['details']."</p>";
        }
    }
  ?>

  </div>
  </div>
</div>
