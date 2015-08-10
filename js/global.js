$ = jQuery.noConflict();

var preset_id, filename_key, preset_name, preset_type;

var Supra = {}

$(function() {

    Supra.Tooltips.bindTooltips();
});

Supra.Main = function() {

  _baseCall = function(dataCmd, dataArgs, cb) {

      $.ajax({
        type: 'POST',
        data: {'action':'supra_csv','command': dataCmd,'args': dataArgs},
        url: ajaxurl,
        success: function(msg){
          cb(msg);
        }
      });
  }

  _basePoll = function(dataCmd, dataArgs, cb) {

      $.ajax({ 
        type: 'POST',
        url: ajaxurl,
        data: {'action':'supra_csv','command': dataCmd,'args': dataArgs},
        success: function(msg){
          
          cb(msg);

          setTimeout(function() {

              Supra.poll();

          }, 5000);
        }, 
        dataType: "json", 
      });
  }

  return {
    baseCall: function(dataCmd, dataArgs, cb) {
      _baseCall(dataCmd, dataArgs, cb);
    }
   ,basePoll: function(dataCmd, dataArgs, cb) {
      _basePoll(dataCmd, dataArgs, cb);
    }
   ,scrollToEl: function(el, cb) {
      if(typeof el.offset() !== "undefined") {
        $('html, body').animate({
          scrollTop: el.offset().top
        }, 2000);
      }
      if(cb) cb(); 
    }
  }
}
