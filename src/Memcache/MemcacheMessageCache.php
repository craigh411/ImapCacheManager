<?php
namespace Humps\ImapCacheManager\Memcache;

use Humps\ImapCacheManager\Contracts\MessageCache;
use Humps\MailManager\Collections\ImapMessageCollection;
use Humps\MailManager\Components\Contracts\Message;

class MemcacheMessageCache extends MemcacheCache implements MessageCache
{

    public function __construct(MemcacheServer $memcache)
    {
        parent::__construct($memcache, 'messageList');
    }

    /**
     * Caches the given message
     * @param Message $message
     * @param $folder
     * @param int $expire
     */
    public function cacheMessage(Message $message, $folder, $expires = 0)
    {
        $key = $this->getKey($message->getMessageNum(), $folder);
        $message = [
            'folder' => $folder,
            'message' => $message
        ];

        $this->cache($key, $message, $expires);
    }


    /**
     * Creates the key for the message
     * @param $messageNum
     * @param $folder
     * @return string
     */
    public function getKey($messageNum, $folder)
    {
        return $folder . ':' . $messageNum;
    }

    /**
     * Returns the given message from the cache
     * @param $messageNum
     * @param $folder
     * @return array|string
     */
    public function getMessage($messageNum, $folder)
    {
        return $this->getItemByKey($this->getKey($messageNum, $folder));
    }

    /**
     * Remove all messages from the cache
     */
    public function flushAllMessages()
    {
        $this->cache->flush();
    }

    /**
     * @param $messageNum
     * @param $folder
     */
    public function uncacheMessage($messageNum, $folder)
    {
        $this->uncache($this->getKey($messageNum, $folder));
    }

}