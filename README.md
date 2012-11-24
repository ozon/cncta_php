CnCTA_php
=========
A simple PHP class to create Web Services for [C&C:TA](https://www.tiberiumalliances.com)
### How use it
```php
$cncta = TiberiumAlliances::getInstance();

// login into Game and get a sessionId
$cncta->login($user_id, $password);

// start a game session
$cncta->openGameSession();

// get some data from
$cncta->get('GetPublicPlayerInfo', array('id'=>$playerID));
```
More samples and details come soon ;)

### More useful information
* [Official statement to external scripts and tools](http://forum.alliances.commandandconquer.com/showthread.php?tid=9157)
* [Webservices API discussion with some samples](http://forum.alliances.commandandconquer.com/showthread.php?tid=9502)
