
(function ($) {
  Drupal.behaviors.reloadH5P = {
    attach: function(context, settings) {
        
        setTimeout(function () {
          // tell H5p to init on loaded content for somer reason it works only wrapped in an timepout
          $('.h5p-iframe-wrapper').each(function(){
            H5P.init($(this));
          });
        },10);
    }
  };
})(jQuery);