<?php
namespace Humps\ImapCacheManager\Contracts;

use Humps\MailManager\Components\Contracts\Message;

interface MessageCache extends Cache
{

	/**
	 * Caches the given message
	 * @param Message $message
	 * @param $folder
	 * @param int $expires
	 */
	public function cacheMessage(Message $message, $folder, $expires = 0);


	/**
	 * Returns the message by message number and folder
	 * @param $folder
	 * @param int $from
	 * @param int $to
	 * @return array
	 */
	public function getMessage($messageNum, $folder);


	/**
	 * Remove all messages from the cache
	 */
	public function flushAllMessages();

	/**
	 * Deletes the given message from the cache
	 * @param $messageNum
	 * @param $folder
	 */
	public function uncacheMessage($messageNum, $folder);

}