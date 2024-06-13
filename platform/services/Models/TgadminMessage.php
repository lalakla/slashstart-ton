<?php namespace Services\Models;


use CodeIgniter\Model;


class TgadminMessage extends \CodeIgniter\Model
{

	protected $table      = 'chats_tgadmin_messages';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['tg_id', 'chat_id', 'date', 'text'];

    protected $useTimestamps = false;

    protected $validationRules    = [];

    protected $validationMessages = [];
    protected $skipValidation     = false;

}

