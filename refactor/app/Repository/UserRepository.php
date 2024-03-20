<?php

namespace DTApi\Repository;

use DTApi\Repository\BaseRepository;

class UserRepository extends BaseRepository
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * @param integer $id
     * @return Model|null
     */
    public function findUser(int $user_id)
    {
        return $this->find($user_id);
    }
}