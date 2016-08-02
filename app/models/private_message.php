<?php
/**
 * Tatoeba Project, free collaborative creation of multilingual corpuses project
 * Copyright (C) 2009 DEPARIS Étienne <etienne.deparis@umaneti.net>
 * Copyright (C) 2010 SIMON   Allan   <allan.simon@supinfo.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  Tatoeba
 * @author   DEPARIS Étienne <etienne.deparis@umaneti.net>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */

/**
 * Model for Private Messages.
 *
 * @category PrivateMessage
 * @package  Models
 * @author   DEPARIS Étienne <etienne.deparis@umaneti.net>
 * @author   SIMON   Allan   <allan.simon@supinfo.com>
 * @license  Affero General Public License
 * @link     http://tatoeba.org
 */
class PrivateMessage extends AppModel
{
    public $name = 'PrivateMessage';

    public $actsAs = array('Containable');

    public $belongsTo = array(
        'User',
        'Recipient' => array(
            'className' => 'User',
            'foreignKey' => 'recpt'
        ),
        'Sender' => array(
            'className' => 'User',
            'foreignKey' => 'sender'
        )
    );

    /**
     * Get private messages by folder.
     *
     * @param string $folder [Name of the folder we want the messages.]
     * @param int    $userId [Id of the user.]
     *
     * @return array
     */
    public function getMessages($folder, $userId)
    {
        return $this->find(
            'all',
            array(
                'conditions' => array(
                    'PrivateMessage.user_id' => $userId,
                    'PrivateMessage.folder' => $folder
                ),
                'order' => 'PrivateMessage.date DESC',
                'contain' => array(
                    'Sender' => array(
                        'fields' => array('username', 'image'),
                    ),
                    'Recipient' => array(
                        'fields' => array('username', 'image')
                    )
                )
            )
        );
    }

    /**
     * Get message by id.
     *
     * @param int $messageId [ID of the message to retrieve.]
     *
     * @return array
     */
    public function getMessageWithId($messageId)
    {
        return $this->find(
            'first',
            array(
                'conditions' => array('PrivateMessage.id' => $messageId),
                'contain' => array(
                    'Sender' => array(
                        'fields' => array('username', 'image')
                    )
                )
            )
        );
    }

    /**
     * Get unread message count for user.
     *
     * @param int $userId [ID for user.]
     *
     * @return int
     */
    public function numberOfUnreadMessages($userId)
    {
        return $this->find(
            'count',
            array(
                'conditions' => array(
                    'PrivateMessage.recpt' => $userId,
                    'PrivateMessage.folder' => 'Inbox',
                    'PrivateMessage.isnonread' => 1
                ),
            )
        );
    }

    /**
     * Return count of messages sent by user in the last 24 hours.
     *
     * @param  int $userId [ID for user.]
     *
     * @return int
     */
    public function todaysMessageCount($userId)
    {
        $yesterday = date_modify(new DateTime("now"), "-1 day");

        return $this->find(
            "count",
            array(
                  'conditions' => array(
                      'sender' => $userId,
                      'folder' => array('Sent', 'Trash'),
                      'date >= ' => date_format($yesterday, "Y/m/d H:i:s")
                  )
            )
        );
    }

    /**
     * Save a draft message.
     *
     * @param  int      $currentUserId  [ID for current user.]
     * @param  string   $now            [Timestamp.]
     * @param  array    $data           [Form data from controller.]
     *
     * @return array                    [Draft.]
     */
    public function saveDraft($currentUserId, $now, $data)
    {
        $draft = array(
            'user_id'       => $currentUserId,
            'sender'        => $currentUserId,
            'draft_recpts'  => $data['PrivateMessage']['recpt'],
            'date'          => $now,
            'folder'        => 'Drafts',
            'title'         => $data['PrivateMessage']['title'],
            'content'       => $data['PrivateMessage']['content'],
            'isnonread'     => 1,
            'sent'          => 0,
        );

        if ($data['PrivateMessage']['messageId']) {
            $draft['id'] = $data['PrivateMessage']['messageId'];
        }

        $this->save($draft);

        return $draft;
    }

    /**
     * Save message to recipients inbox.
     *
     * @param  array $message [Message to send.]
     * @param  int   $recptId [User id for recipient.]
     *
     * @return array
     */
    public function saveToInbox($message, $recptId)
    {
        $message = array_merge($message, array(
            'recpt' => $recptId,
            'user_id' => $recptId,
            'draft_recpts' => '',
            'sent' => 1
        ));

        $this->save($message);

        return $message;
    }

    /**
     * Save message to senders outbox.
     *
     * @param  array $messageToSave [Message to save to outbox.]
     * @param  int   $recptId       [User id for recipient.]
     * @param  int   $currentUserId [User id for current user.]
     *
     * @return array
     */
    public function saveToOutbox($messageToSave, $recptId, $currentUserId)
    {
        $message = array_merge($messageToSave, array(
            'user_id'   => $currentUserId,
            'folder'    => 'Sent',
            'isnonread' => 0,
            'recpt' => $recptId,
            'draft_recpts' => '',
            'sent' => 1,
            'id' => null
        ));

        $this->save($message);

        return $message;
    }

    /**
     * Mark a private message as read.
     *
     * @param  array $message [Private message.]
     *
     * @return array
     */
    public function markAsRead($message)
    {
        if ($message['PrivateMessage']['isnonread'] == 1) {
            $message['PrivateMessage']['isnonread'] = 0;

            $this->save($message);
        }

        return $message;
    }
}
?>
