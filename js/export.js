$(function() { 

    $('#extract_and_preview').click( function(e) {

        e.preventDefault();

        var data = $('#extraction_form').serialize();

        $.ajax({
          type: 'POST',
          data: {'action':'supra_csv','command':'extract_and_preview','data':data},
          url: ajaxurl,
          success: function(msg){
              $('#extracted_results').html(msg);
          }
        });

    });

    $('#extract_and_export').click( function(e) {

        e.preventDefault();

        var data = $('#extraction_form').serialize();

        $.ajax({
          type: 'POST',
          data: {'action':'supra_csv','command':'extract_and_export','data':data},
          url: ajaxurl,
          success: function(msg){
              msg = $.parseJSON(msg);
              if(msg.success) {
                  $('#extracted_results').html('<h3>'+msg.premium+'</h3><b>File created below: </b>'+msg.filename);
                  refreshExtractedForm();
              } 
              else {
                  $('#extracted_results').html('<h3>Something went wrong</h3>');
              }
          }
        });
    });

    refreshExtractedForm = function() {
        $.ajax({
          type: 'POST',
          data: {'action':'supra_csv','command':'get_extracted_form'},
          url: ajaxurl,
          success: function(msg){
              result = $.parseJSON(msg);
              $('#supra_csv_extract_forms').html(result.html);
          }
        });
    }
});
