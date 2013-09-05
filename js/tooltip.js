$(function() {

    $.ajax({
      type: 'POST',
      data: {'action':'supra_csv','command':'get_tooltips','args':null},
      url: ajaxurl,
      success: function(msg){
          binding(msg); 
      }
    });

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
        'reportissues_tt':['report_issues'],
        'specialchar_tt':['special_char'],
        'csvsettings_tt':['csv_settings + ol'],
        'maxchar_tt':['max_char']
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
});
