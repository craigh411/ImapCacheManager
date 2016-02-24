# ImapCacheManager

Imap cache manager provides caching support for ImapMailManager, reducing requests to the imap server
for a significant performance improvement.

**IMPORTANT: THIS PACKAGE IS CURRENTLY IN DEVELOPMENT. THE FEATURES BELOW MAY NOT YET HAVE BEEN IMPLEMENTED AND ARE SUBJECT TO CHANGE**

## Features

- Reduces requests to your imap server
- Intelligently swaps between making an imap request if the message is not cached and retrieving a message from the cache.
- Allows display of only cached messages for any folder, reducing imap calls to 0 at runtime.
- Choose to automatically remove cached messages after a specified period.

## Requirements

ImapCacheManager requires a cache server. Currently, the implemented cache server is
(Memcached)[http://memcached.org/], which you will need to install on your server. You will also need the PHP Memcache package
to be installed, see: [http://php.net/manual/en/intro.memcache.php](http://php.net/manual/en/intro.memcache.php)

### Using a different cache service

It's relatively straight forward to write your own cache handler if you want to use a different cache service (such as Redis),
simply implement the methods in `Contracts\MessageCache` and pass in to the MessageCacheService constructor.


## Why use Caching?

Calls to imap servers can be slow, especially if you have a large mailbox. In fact, each time you load
your inbox (or other folder) you need to make a request to search for the E-mails list, then request each emails headers and body,
this means that getting a list of just 25 Emails requires 51 calls to your imap mailbox.

In order to overcome this, some type of storage should be used. You can of course use a database to store messages,
however, you often won't want to store every message in your database, so instead you use ImapCacheManager to cache the message and reduce
the overhead of making requests.



