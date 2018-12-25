<?php
namespace app\api\view\User;

use app\models\User;

class Lists
{

    /** @var array */
    private $users;

    /**
     * @return array
     */
    public function build()
    {
        $result = [];

        foreach ($this->users as $user) {
            $user['is_active'] = $user['status'] == User::STATUS_ACTIVE;
            $user['id'] = (int) $user['id'];
            $user['internal_number'] = isset($user['internal_number']) ? (int) $user['internal_number'] : 0;
            $user['group_id'] = isset($user['group_id']) ? (int) $user['group_id'] : 0;

            unset($user['auth_key']);
            unset($user['access_token']);
            unset($user['password_hash']);
            unset($user['password_reset_token']);
            unset($user['notify']);
            unset($user['full_fillment']);
            unset($user['created_at']);
            unset($user['updated_at']);
            unset($user['status']);

            $result[] = $user;
        }

        return $result;
    }

    /**
     * @param array $users
     * @return $this
     */
    public function setUsers(array $users)
    {
        $this->users = $users;
        return $this;
    }
}