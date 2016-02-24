<?php
namespace Humps\ImapCacheManager;

use Humps\ImapCacheManager\Contracts\MessageCache;
use Humps\MailManager\Collections\ImapMessageCollection;
use Humps\MailManager\Components\Contracts\Message;
use Humps\MailManager\Contracts\Imap;
use Humps\MailManager\Factories\ImapMessageCollectionFactory;
use Humps\MailManager\Factories\ImapMessageFactory;

/**
 * Service class for handling cached messages
 * @package Humps\ImapCacheManager
 */
class ImapMessageCache
{

	protected $cache;
	protected $imap;
	protected $folder;

	function __construct(MessageCache $cache, Imap $imap) {
		$this->cache = $cache;
		$this->imap = $imap;
		$this->folder = $imap->getConnection()->getMailbox()->getFolder();
	}

	/**
	 * Caches the given messages.
	 * with the new messages
	 * @param array $messageList
	 * @param bool $cacheBody
     * @param bool $recache
	 * @param int $expires
	 */
	public function cacheMessages(array $messageList, $cacheBody = true, $expires = 0, $recache = false) {

        if(!$recache){
            // Get the currently cached message numbers
            $cachedMessageNums = $this->getMessageNums($this->getAllCachedMessages());
            // Find the message numbers that are not cached
            $messageList = array_diff($messageList, $cachedMessageNums);
        }

		$messages = $this->getMessageCollection($messageList, !$cacheBody);
        if(count($messages)) {
            foreach($messages as $message) {
                $this->cache->cacheMessage($message, $this->folder, $expires);
            }
        }
	}


	/**
	 * Caches the message headers only
	 * @param array $messageList
	 */
	public function cacheMessageHeaders(array $messageList) {
		$this->cacheMessages($messageList, true);
	}

	/**
	 * Caches the given message
	 * @param $messageNum
	 * @param $expires
	 */
	public function cacheMessage($messageNum, $expires = 0) {
		$this->cacheMessages([$messageNum], true, $expires);
	}

	/**
	 * Returns all cached messages for the current folder
	 * @return ImapMessageCollection
	 */
	public function getAllCachedMessages() {
		$cache = $this->cache->getAllCached();
		$messages = new ImapMessageCollection();

		if(count($cache)) {
			foreach($cache as $message) {
				if($message['folder'] == $this->folder) {
					$messages[] = $message['message'];
				}
			}
		}

		return $messages;
	}

	/**
	 * Returns the messages from cache if they exist
	 * @param array $messageList
	 * @return ImapMessageCollection
	 */
	public function getCachedMessages(array $messageList) {
		$cachedMessages = $this->getAllCachedMessages();
        $messages = new ImapMessageCollection();
		if(count($cachedMessages)) {
			/**
			 * @var Message $message
			 */
			foreach($cachedMessages as $message) {
				if(in_array($message->getMessageNum(), $messageList)) {
					$messages[] = $message;
				}
			}
		}

		return $messages;
	}

	/**
	 * Returns all messages in the list, if they are cached, it will return the cached message
	 * otherwise it will pull them from the sever
	 * @param array $messageList
	 * @param $cacheBody
	 * @return array
	 */
	public function getMessages(array $messageList, $cacheBody = true) {
		$cachedMessages = $this->getCachedMessages($messageList);
		$cachedMessageNums = $this->getMessageNums($cachedMessages);
		$messages = [];
		if(count($messageList)) {
			foreach($messageList as $messageNum) {
				if(in_array($messageNum, $cachedMessageNums)) {
					$messages[] = $this->getCachedMessages([$messageNum]);
				} else {
					$message[] = [
						'folder'  => $this->folder,
						'message' => $this->getMessageFromServer($messageNum, ! $cacheBody)
					];
				}
			}
		}

		return $messages;
	}

	/**
	 * Returns true if the message has been cached
	 * @param $messageNum
	 * @return bool
	 */
	public function isCached($messageNum) {
		return ($this->cache->getMessage($messageNum, $this->folder)) ? true : false;
	}

	/**
	 * Returns true if the message body has been cached
	 * @param $messageNum
	 * @return bool
	 */
	public function isBodyCached($messageNum) {
		/**
		 * @var Message $message
		 */
		if($message = $this->cache->getMessage($messageNum, $this->folder)) {
			return ($message->getHtmlBody()) ? true : false;
		}

		return false;
	}


	/**
	 * Returns the given message, if it is cached it will return the cached message
	 * @param $messageNum
	 * @return ImapMessageCollection
	 */
	public function getMessage($messageNum) {
		return $this->getMessages([$messageNum], false)[0];
	}

	/**
	 * Returns the ImapMessageCollection for the given message numbers
	 * @param $messageNumbers
	 * @param bool $excludeBody
	 * @return ImapMessageCollection
	 */
	protected function getMessageCollection($messageNumbers, $excludeBody = false) {
		return ImapMessageCollectionFactory::create($messageNumbers, $this->imap, $excludeBody);
	}

	/**
	 * Gets the ImapMessage fro the given message
	 * @param $messageNum
	 * @param bool $excludeBody
	 * @return mixed
	 */
	protected function getMessageFromServer($messageNum, $excludeBody = false) {
		return ImapMessageFactory::create($messageNum, $this->imap, $excludeBody);
	}

	/**
	 * @param $cachedMessages
	 * @return array
	 */
	protected function getMessageNums(ImapMessageCollection $cachedMessages) {
		$cachedMessageNums = array_map(function (Message $message) {
			return $message->getMessageNum();
		}, $cachedMessages->toArray());

		return $cachedMessageNums;
	}
}