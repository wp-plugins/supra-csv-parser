$(function() {

    var 
      sMain = Supra.Main()
    ;

    $('#extract_and_preview').click( function(e) {

        e.preventDefault();

        var data = $('#extraction_form').serialize();

        sMain.baseCall( 'extract_and_preview', data, function(msg) {
            $('#extracted_results').html(msg);
            sMain.scrollToEl($("#extracted_results"));
        });

    });

    $('#extract_and_export').click( function(e) {

        e.preventDefault();

        var data = $('#extraction_form').serialize();

        sMain.baseCall( 'extract_and_export', data, function(msg) {
        
          msg = $.parseJSON(msg);
          if(msg.success) {
              $('#extracted_results').html('<h3>' + msg.premium + '</h3><b>File created below: </b>'+msg.filename);
              sMain.scrollToEl($('ul#uploaded_files li').last(), function() {
                _refreshExtractedForm();
              }); 
          } 
          else {
              $('#extracted_results').html('<h3>Something went wrong</h3>');
          }
        });
    });

    _refreshExtractedForm = function() {
        sMain.baseCall( 'get_extracted_form', {}, function(msg) {
          result = $.parseJSON(msg);
          $('#supra_csv_extract_forms').html(result.html);
        });
    }
});
