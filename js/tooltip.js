Supra.Tooltips = (function() { 

    var bindTooltips = function() {

        $.ajax({
            type: 'POST',
            data: {'action':'supra_csv','command':'get_tooltips','args':null},
            url: ajaxurl,
            success: function(msg){
                binding(msg); 
            }
        });
    }

    var binding_mapping = {
        'usersettings_tt':['user_settings'],
        'postsettings_tt':['post_settings'],
        'autopublish_tt':['auto_publish'],
        'posttype_tt':['post_type'],
        'customposttype_tt':['custom_post_type'],
        'postdefaults_tt':['post_defaults'],
        'customterms_tt':['custom_terms', 'custom_terms + ol'],
        'parsecomplex_tt':['parse_complex_categories'],
        'debugingestion_tt':['debug_ingestion'],
        'specialchar_tt':['special_char'],
        'csvsettings_tt':['csv_settings + ol'],
        'maxchar_tt':['max_char'],
        'extractedfilemgmt_tt':['extracted_crud_management'],
        'postinfo_tt':['post_info'],
        'pmpresets_tt':['post_meta_presets'],
        'pmmapping_tt':['post_meta_mapping'],
        'pmsuggest_tt':['post_meta_suggestions'],
        'filemgmt_tt':['upload'],
        'selectfile_tt':['select_a_file'],

        'custompostmeta_tt':['custom_postmeta'],
        'customterms_tt':['custom_terms'],
        'ingest_tt':['ingest'],
        'ingestionpredefined_tt':['ingestion_predefined','ingestion_predefined + ol'],
        'hooking_tt':['hooking'],
        'activatehooking_tt':['activate_hooking'],
        'arerevisionsskipped_tt':['arerevisionsskipped'],
        'isingestionchunked_tt':['isingestionchunked'],
        'chunkbynrows_tt':['chunkbynrows'],
        'isusingmultithreads_tt':['isusingmultithreads'],
        'pluginsettings_tt':['pluginsettings']
    }

    var binding = function(docs) { 

        $.map(binding_mapping, function(page_elem,tip_elem) { 
 
            tip = ""; 

            $.map(page_elem, function(sel) { 

                tip += $(docs).find('#' + sel).html();
            });
 
           $('#' + tip_elem).qtip({ content: tip });

        })
    }

    return {
        bindTooltips: function() {
            bindTooltips();
        }
    }
})();
