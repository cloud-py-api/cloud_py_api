<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: core.proto

namespace Cloud_Py_API;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>Cloud_Py_API.ServerCommand</code>
 */
class ServerCommand extends \Google\Protobuf\Internal\Message
{
    /**
     * Generated from protobuf field <code>.Cloud_Py_API.ServerCommand.cmd_id id = 1;</code>
     */
    protected $id = 0;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type int $id
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Core::initOnce();
        parent::__construct($data);
    }

    /**
     * Generated from protobuf field <code>.Cloud_Py_API.ServerCommand.cmd_id id = 1;</code>
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Generated from protobuf field <code>.Cloud_Py_API.ServerCommand.cmd_id id = 1;</code>
     * @param int $var
     * @return $this
     */
    public function setId($var)
    {
        GPBUtil::checkEnum($var, \Cloud_Py_API\ServerCommand\cmd_id::class);
        $this->id = $var;

        return $this;
    }

}

