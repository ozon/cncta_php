CnCTA_php
=========

## How use it
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
