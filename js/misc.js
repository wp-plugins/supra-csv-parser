$(function() {

    var 
      sMain = Supra.Main()
    , downloadExtractElToggled = []
    , downloadUploadElToggled = [];

    $('#delete_upload').live('click', function() {

        filename_key = $(this).data('key');

        var answer = confirm("Are you sure you want to delete this item?"); 

        if( ! answer ) return ;

        sMain.baseCall( 'delete_file', filename_key, function(msg) {

          $('#supra_csv_upload_forms').html(msg);
        });
    });

    $('#extract_and_export').click( function(e) {

        downloadExtractElToggled = [];

    });

    $('#download_upload').live('click', function() {

        var file = $(this).data('file');

        el = $(this);

        elToggled = downloadUploadElToggled[file];

        if( typeof elToggled == "undefined" ) {

          sMain.baseCall('download_file', file, function(msg) {
            el.parent().append('<div id="previewToggle">' + msg + '</div>');
            downloadUploadElToggled[file] = true;
            $('.tablesorter-blue').tablesorter();
          });

        } else {
          el.parent().find('#previewToggle').toggle();
        }

    });

    $(document).on('click','#delete_extract', function() {

        filename_key = $(this).data('key');

        var answer = confirm("Are you sure you want to delete this item?");

        if( ! answer ) return ;

        sMain.baseCall( 'delete_extract_file', filename_key, function(msg) {
            $('#supra_csv_extract_forms').html(msg);
            downloadExtractElToggled = [];
        });
    });

    $(document).on('click','#download_extract', function() {

        file = $(this).data('file');

        el = $(this);

        elToggled = downloadExtractElToggled[file];

        if(typeof elToggled == "undefined" ) {

          sMain.baseCall('download_extract_file', file, function(msg) {
            el.parent().append('<div id="previewToggle">' + msg + '</div>');
            downloadExtractElToggled[file] = true;
            $('.tablesorter-blue').tablesorter();
          });

        } else {
          el.parent().find('#previewToggle').toggle();
        }
    });


    $('#select_csv_file').live('change', function() {
        filename_key = $(this).val();

        $('#supra_csv_ingestion_errors').html(null);

        $('#supra_csv_ingestion_log').html(null);

        if(filename_key) {
          sMain.baseCall( 'select_ingest_file', filename_key, function(msg) {
            msg = $.parseJSON(msg);
            $('#supra_csv_ingestion_mapper').html(msg.map);
            $('#supra_csv_mapping_preset').html(msg.preset);
            for(i in msg.error_tips)
            {
              error_tip = msg.error_tips[i];
              $('#supra_csv_ingestion_errors').append("<li>" + error_tip + "</li>");
            }
            clearMappingForm();
            Supra.Tooltips.bindTooltips();
          });
        }
    });

    $('#supra_csv_ingest_csv').live('click', function(e) {
        e.preventDefault();

        $('#supra_csv_ingestion_log').html(null);
        $('#patience').show();

        var data = $('#supra_csv_mapping_form').serialize();
        var filename = $('#supra_csv_mapping_form').data('filename');

        sMain.baseCall('ingest_file', {'data': data, 'filename':filename}, function(msg) {

          msg = $.parseJSON(msg);
          
          if(msg.result)
          {
              sMain.scrollToEl($('#supra_csv_ingestion_log'), function() {
                $('#supra_csv_ingestion_log').html(msg.result);
                $('#patience').hide();
              });
          }
          else if(msg.chunk_namespace)
          {
              Supra.chunk_namespace = msg.chunk_namespace;

              if(!Supra.isPolling)
              {
                Supra.poll();

                Supra.isPolling = true;
              }
          }
        });
    });

    Supra.poll = function() {
     
      sMain.basePoll('poll_ingestion_completion', {'data': Supra.chunk_namespace}, function(msg) {
            
        if(msg.output)
        {
          $('#supra_csv_ingestion_log').append(msg.output);
          $('#patience').hide();
        }
      });
    }
});
