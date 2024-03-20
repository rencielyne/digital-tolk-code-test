<?php

namespace DTApi\Repository;

use DTApi\Repository\BaseRepository;

class TranslatorRepository extends BaseRepository
{
    public function __construct(Translator $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array $data
     * @return Model|null
     */
    public function createTranslator(array $data)
    {
        return $this->create($data);
    }
}