<?php

namespace Lion\Auth\Dto;

use stdClass;

class UserData
{
    /**
     * @var string[]
     */
    public $roles = [];
    /**
     * @var string
     */
    public $email = '';
    /**
     * @var stdClass
     */
    public $authData;
    /**
     * @var stdClass
     */
    public $profile;
    /**
     * @var string
     */
    public $photo;
}