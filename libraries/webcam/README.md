This is a copy of http://code.google.com/p/jpegcam/ for use with git submodules.

This version of jpegcam implements `onActivity` hook called when camera begins to display movie.  
It's better to use `onActivity` hook instead of `onLoad`.  
The difference between `onLoad` and `onActivity` is that: 
 
*    `onLoad` is called when movie object has been loaded.
*    `onActivity` is called when user allow flash to use webcam in access dialog.

Also I made the `onError` hook to be called when user dened access to webcam.

And made movie object to be `wmode=tranparent`.
