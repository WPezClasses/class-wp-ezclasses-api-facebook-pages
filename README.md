# Org: WPezClasses
### Product: Class_WP_ezClasses_API_Facebook_Pages

##### The ezWay to make your own Facebook Page widget with follower images and what not, etc.

The arc of the intention here is to bypass the usual js based Facebook Page widget (which can be a page load hog), 
in order to gain greater control over display / layout, as well as use a WP transient to further reduce overhead.

=======================================================================================

#### WPezClasses: Getting Started
- https://github.com/WPezClasses/wp-ezclasses-docs-getting-started

=======================================================================================


#### Overview

Note: This is part API (for the basic page info) and part parsing an iframe widget returned by graph.facebook.com. In either case,
the result is data (read: an array) you can stash in a transient, and use in your own view / layout.

To get a feel for what data you'll have available to you:

http://graph.facebook.com/your-text-page-name-here

Also keep in mind, this class will parse the image tags so you'll also have the img src, the follower's name, as well as
the follower's URL when it's available. Each of these is stored in an array and the key(s) keeps them tied together (so 
to speak).


####Basic markup for a FB Pages' plugin widget

https://github.com/ezWebDevTools/ezFacebookPagePlugin


#### Credit / Inspiration

http://stackoverflow.com/questions/4018849/facebook-api-get-fans-of-people-who-like-a-page