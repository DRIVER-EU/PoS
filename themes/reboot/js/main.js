
(function ($) {
  Drupal.behaviors.myToggle = {
    attach: function(context, settings) {
        // var $body = $('#block-trialobjectivesinstructions');
        // $($body).hide();

        $('#block-languageswitcher').hide();
        $('#lang-toggle', context).css("cursor","pointer").click(function() {
          $(this).toggleClass('active');
          $('#block-languageswitcher').toggleClass('active').toggle('slow');
         
         
        });
        
        $('#nav-toggle', context).css("cursor","pointer").click(function() {
          $(this).toggleClass('active');
          $('#block-reboot-main-menu').toggleClass('active');
         
         
        });

        // $( "#nav-toggle", context ).css("cursor","pointer").on( "click", function() {
        //   // $( "#log" ).html( $( "input:checked" ).val() + " is checked!" );
        //   $(this).toggleClass('active');
        //   $('#block-reboot-main-menu').toggleClass('active');


          
          
         
          
        // });
       

        $('#region-help').hide();
        
      $('#btn-help, #user-menu .help', context).css("cursor","pointer").click(function() {
        $(this).toggleClass('active').parent();
        
                
         
         $('#region-help').toggleClass('active').toggle('500');
         event.preventDefault();
        // $(this).parent().toggleClass('active');
        // $($body).toggle('slow');
      });
     /*
      var theHidden;
      var myFeatured;
      $('#block-reboot-content .view-duplicated .view-content > div').each(function(index, value){
        
        myFeatured = theHidden;
        theHidden = $(this)[0].classList[0];
        
        if(theHidden == myFeatured){ 
          $(this).hide();	
        }
      });
      */
      
    }
  };
})(jQuery);