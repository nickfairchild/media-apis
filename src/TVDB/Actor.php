<?php

namespace Media\TVDB;

class Actor
{
    public $id;
    public $image;
    public $name;
    public $role;
    public $sortOrder;

    /**
     * Actor constructor.
     *
     * @param $data
     */
    public function __construct($data)
    {
        $this->id        = (int)$data->id;
        $this->image     = (string)$data->Image;
        $this->name      = (string)$data->Name;
        $this->role      = (string)$data->Role;
        $this->sortOrder = (int)$data->SortOrder;
    }
}