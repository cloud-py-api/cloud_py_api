<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: core.proto

namespace Cloud_Py_API;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Generated from protobuf message <code>Cloud_Py_API.TaskInitReply</code>
 */
class TaskInitReply extends \Google\Protobuf\Internal\Message
{
    /**
     *name of the app(folder in frmAppData must be present with the same name).
     *
     * Generated from protobuf field <code>string appName = 1;</code>
     */
    protected $appName = '';
    /**
     *module name to use for `import_module` func.
     *
     * Generated from protobuf field <code>string modName = 2;</code>
     */
    protected $modName = '';
    /**
     *Path to module root, to be executed.
     *
     * Generated from protobuf field <code>string modPath = 3;</code>
     */
    protected $modPath = '';
    /**
     *Which function to execute.
     *
     * Generated from protobuf field <code>string funcName = 4;</code>
     */
    protected $funcName = '';
    /**
     *Optional arguments to pass to target python app's module.
     *
     * Generated from protobuf field <code>repeated string args = 5;</code>
     */
    private $args;
    /**
     * Generated from protobuf field <code>.Cloud_Py_API.TaskInitReply.cfgOptions config = 6;</code>
     */
    protected $config = null;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $appName
     *          name of the app(folder in frmAppData must be present with the same name).
     *     @type string $modName
     *          module name to use for `import_module` func.
     *     @type string $modPath
     *          Path to module root, to be executed.
     *     @type string $funcName
     *          Which function to execute.
     *     @type string[]|\Google\Protobuf\Internal\RepeatedField $args
     *          Optional arguments to pass to target python app's module.
     *     @type \Cloud_Py_API\TaskInitReply\cfgOptions $config
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Core::initOnce();
        parent::__construct($data);
    }

    /**
     *name of the app(folder in frmAppData must be present with the same name).
     *
     * Generated from protobuf field <code>string appName = 1;</code>
     * @return string
     */
    public function getAppName()
    {
        return $this->appName;
    }

    /**
     *name of the app(folder in frmAppData must be present with the same name).
     *
     * Generated from protobuf field <code>string appName = 1;</code>
     * @param string $var
     * @return $this
     */
    public function setAppName($var)
    {
        GPBUtil::checkString($var, True);
        $this->appName = $var;

        return $this;
    }

    /**
     *module name to use for `import_module` func.
     *
     * Generated from protobuf field <code>string modName = 2;</code>
     * @return string
     */
    public function getModName()
    {
        return $this->modName;
    }

    /**
     *module name to use for `import_module` func.
     *
     * Generated from protobuf field <code>string modName = 2;</code>
     * @param string $var
     * @return $this
     */
    public function setModName($var)
    {
        GPBUtil::checkString($var, True);
        $this->modName = $var;

        return $this;
    }

    /**
     *Path to module root, to be executed.
     *
     * Generated from protobuf field <code>string modPath = 3;</code>
     * @return string
     */
    public function getModPath()
    {
        return $this->modPath;
    }

    /**
     *Path to module root, to be executed.
     *
     * Generated from protobuf field <code>string modPath = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setModPath($var)
    {
        GPBUtil::checkString($var, True);
        $this->modPath = $var;

        return $this;
    }

    /**
     *Which function to execute.
     *
     * Generated from protobuf field <code>string funcName = 4;</code>
     * @return string
     */
    public function getFuncName()
    {
        return $this->funcName;
    }

    /**
     *Which function to execute.
     *
     * Generated from protobuf field <code>string funcName = 4;</code>
     * @param string $var
     * @return $this
     */
    public function setFuncName($var)
    {
        GPBUtil::checkString($var, True);
        $this->funcName = $var;

        return $this;
    }

    /**
     *Optional arguments to pass to target python app's module.
     *
     * Generated from protobuf field <code>repeated string args = 5;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     *Optional arguments to pass to target python app's module.
     *
     * Generated from protobuf field <code>repeated string args = 5;</code>
     * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setArgs($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->args = $arr;

        return $this;
    }

    /**
     * Generated from protobuf field <code>.Cloud_Py_API.TaskInitReply.cfgOptions config = 6;</code>
     * @return \Cloud_Py_API\TaskInitReply\cfgOptions|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function hasConfig()
    {
        return isset($this->config);
    }

    public function clearConfig()
    {
        unset($this->config);
    }

    /**
     * Generated from protobuf field <code>.Cloud_Py_API.TaskInitReply.cfgOptions config = 6;</code>
     * @param \Cloud_Py_API\TaskInitReply\cfgOptions $var
     * @return $this
     */
    public function setConfig($var)
    {
        GPBUtil::checkMessage($var, \Cloud_Py_API\TaskInitReply\cfgOptions::class);
        $this->config = $var;

        return $this;
    }

}

