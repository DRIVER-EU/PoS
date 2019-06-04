Drupal.behaviors.example_hello = {
  attach: function (context, settings) {

    // Attach a click listener to the btnshowdifferences button.
    var showDiff = document.getElementById('btnshowdifferences');
    if (showDiff) {
    	showDiff.addEventListener('click', function() {
        	// Do something!
 
        	var element = document.getElementById("btnhidedifferences"); 
        	element.classList.remove("button");
        	element.classList.remove("visually-hidden");
        	 
        	var element2 = document.getElementById("btnshowdifferences"); 
        	element2.classList.add("visually-hidden"); 
        	
        	//return false;
        	
        	
    	}, false);
    }



  }
};