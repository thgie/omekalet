# omekalet

Not the catchiest of project titles, but who cares.

![Screenshot](/screenshot.png?raw=true "Screenshot")

## what does it do

omekalet is conceived as a browser research or read later bookmarklet with Omeka as a database in the background. Upon activation, a small overlay captures the basics of the website:

- Title
- Description
- URL

Further additionaly data can be saved, such as

- Creator
- Tags
- File attachments
- Create automatic screenshot

Last but not least, the item type can be changed to text. Now you can either save some snippet of text or try to automatically extract the article text, which works most of the time.

### screenshot

The screenshot feature relies on a screenshot service url. I used screenshotlayer for mine and the setting looks like this in my bookmarlet: `http://api.screenshotlayer.com/api/capture?access_key=XXX&viewport=1440x900&width=1440&fullpage=1&delay=5&url='+location.href`


## installation

The connecting php script depends upon two composer packages. That means you either need shell access to your server or you install it localy and then push the whole thing via ftp onto your server. If I would be a better php coder I wouldn't need the composer packages - sorry for that.

1. Checkout or copy the project to it's destination
2. composer install
3. Adjust _marklet.js_ with the domain of your omeka installation, your API key and your omekalet location

I have  omekalet on a different domain then the omeka installation because not all of them can be on https. Which again leads to errors when grabing stuff from other https domains.

### mail.cron.php

The cron job to automatically import mails into omeka is pretty sweet :) 

1. Setup an email
2. edit imap settings 
3. setup cronjob to periodically check mails

If there is an URL in the mail, the script tries to grab Title and Description from the Website. Also with an URL you have the +article and +screen commands. Just add them to your mail and let the script try to grab the article text or screenshot automatically. Screenshot needs a screenshot service. Don't forget the + in front of 'article' or 'screen'.

If there is no URL in the mail, it saves subject as title and mail body as description.

You also can add Tags #hashtag #style. And yes attachements are saved as files and added to the item.

Pretty rad.

## license

This is free and unencumbered software released into the public domain.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a compiled
binary, for any purpose, commercial or non-commercial, and by any
means.

In jurisdictions that recognize copyright laws, the author or authors
of this software dedicate any and all copyright interest in the
software to the public domain. We make this dedication for the benefit
of the public at large and to the detriment of our heirs and
successors. We intend this dedication to be an overt act of
relinquishment in perpetuity of all present and future rights to this
software under copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.

For more information, please refer to <http://unlicense.org/>