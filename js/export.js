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
              if(msg.success)
                  $('#extracted_results').html('<h3>'+msg.premium+'</h3><b>File created: </b> Upload >> '+msg.filename);
              else
                  $('#extracted_results').html('<h3>Something went wrong</h3>');
     
          }
        });
    });
});
