Entity Browser ad Contextual Filter
===========

Makes some basic contextual Filters available for entity browsers.

Instructions
------------

Unpack in the *modules* folder (currently in the root of your Drupal 8
installation) and enable in `/admin/modules`.

To implement a contextual filter for an entitybrowser:

* add a contextual filter to the display
* set "WHEN THE FILTER VALUE IS NOT AVAILABLE" to "provide a default value"
* set type of default value to "query parameter"

Values you can use as query parameter:

* route parameters like 'node' or 'group' for entity id
* Parts of the path from which the entity browser is called as number beginning form 0. E.g. path is /node/add/article:
   * use queryparameter '0' to use 'node' for contextual filter
   * use queryparameter '1' to use 'add' for contextual filter
   * use queryparameter '2' to use 'article' for contextual filter

* maybe there are more parameters availabel. To see if this ist the case check the sourcecode of the entitybrowser as IFrame display. You will find possible parameters in the queryparameters of the src attribute of the iframe.

   


