<?php
/**
 * This script can be used either via an ajax call to update cached folders and/or as a cron
 * job or scheduled task. It simply caches all folders returned from the server.
 */
use Humps\ImapCacheManager\Memcache\MemcacheFolderCache;
use Humps\ImapCacheManager\Memcache\MemcacheServer;
use Humps\MailManager\Factories\ImapFactory;
use Humps\MailManager\Factories\ImapFolderCollectionFactory;
use Humps\MailManager\ImapMailboxService;

set_time_limit(0);

require_once '../../vendor/autoload.php';

// The memcache server host
const HOST = 'localhost';
// The memcache server port
const PORT = 11211;

$cacheServer = MemcacheServer::connect(HOST, PORT);
$cache = new MemcacheFolderCache($cacheServer);

// Create the MailboxService so we can access our mailbox
$imap = ImapFactory::create('INBOX', '../imap_config/config.php');
$mailboxService = new ImapMailboxService($imap);

$folders = ImapFolderCollectionFactory::create($mailboxService->getAllFolders());
$cache->cacheFolders($folders);