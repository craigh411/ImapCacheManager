<?php
/**
 * This script can be used either via an ajax call to check for new messages and/or as a cron
 * job or scheduled task. It simply caches any new messages found on the server.
 */
set_time_limit(0);

require_once '../../vendor/autoload.php';

use Humps\ImapCacheManager\Memcache\MemcacheMessageCache;
use Humps\ImapCacheManager\Memcache\MemcacheServer;
use Humps\ImapCacheManager\ImapMessageCache;
use Humps\MailManager\Factories\ImapFactory;
use Humps\MailManager\ImapMailboxService;

// The number of days the message should remain cached for (Max: 30). Use 0 for never expires
const CACHE_DURATION = 28;
// The memcache server host
const HOST = 'localhost';
// The memcache server port
const PORT = 11211;

// Pass the cacheBody parameter as false if you do not want to cache the message body.
$cacheBody = isset($_REQUEST['cacheBody']) ? $_REQUEST['cacheBody'] : true;
$folder = isset($_REQUEST['folder']) ? $_REQUEST['folder'] : 'INBOX';

// Create the MailboxService so we can access our mailbox
$imap = ImapFactory::create($folder, '../imap_config/config.php');
$mailboxService = new ImapMailboxService($imap);

// Create the cache service
$cacheServer = MemcacheServer::connect(HOST, PORT);
$cache = new MemcacheMessageCache($cacheServer);
$messageCache = new ImapMessageCache($cache, $imap);


// Get messages after the last time a message was cached (this is a timestamp, which ImapMailboxService will handle)
$lastCached = ($cacheServer->getLastCached()) ? $cacheServer->getLastCached() : time()-(60 * 60 * 24);
echo $lastCached;
$messages = $mailboxService->getMessagesAfter($lastCached);

// Convert the CACHE_DURATION to seconds.
$expires = (60 * 60 * 24) * CACHE_DURATION;
// Now cache the messages
$messageCache->cacheMessages($messages, true, $expires);
