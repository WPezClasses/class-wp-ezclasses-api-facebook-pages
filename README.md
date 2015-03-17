# Org: WPezClasses
### Product: Class_WP_ezClasses_API_Facebook_Pages

##### The ezWay to make your own Facebook Page widget.

The arc of the intention here is to bypass the usual js based Facebook Page widget (which can be a page load hog), 
in order to gain greater control over display / layout, as well as use a WP transient to further reduce overhead.

=======================================================================================

#### WPezClasses: Getting Started
- https://github.com/WPezClasses/wp-ezclasses-docs-getting-started

=======================================================================================


#### Overview

Note: This is part API (for the basic page info) and part parsing an iframe widget returned by graph.facebook.com. In either case,
the result is data (read: an array) you can stash in a transient, and use in your own view / layout. 


#### Credit / Inspiration

http://stackoverflow.com/questions/4018849/facebook-api-get-fans-of-people-who-like-a-page