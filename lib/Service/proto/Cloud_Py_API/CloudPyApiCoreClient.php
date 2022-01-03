<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Cloud_Py_API;

/**
 */
class CloudPyApiCoreClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Cloud_Py_API\PBEmpty $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskInit(\Cloud_Py_API\PBEmpty $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/TaskInit',
        $argument,
        ['\Cloud_Py_API\TaskInitReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\TaskSetStatusRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskStatus(\Cloud_Py_API\TaskSetStatusRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/TaskStatus',
        $argument,
        ['\Cloud_Py_API\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\TaskExitRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskExit(\Cloud_Py_API\TaskExitRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/TaskExit',
        $argument,
        ['\Cloud_Py_API\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\TaskLogRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskLog(\Cloud_Py_API\TaskLogRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/TaskLog',
        $argument,
        ['\Cloud_Py_API\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\PBEmpty $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function CmdStream(\Cloud_Py_API\PBEmpty $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/Cloud_Py_API.CloudPyApiCore/CmdStream',
        $argument,
        ['\Cloud_Py_API\ServerCommand', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsGetInfo(\Cloud_Py_API\FsListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/FsGetInfo',
        $argument,
        ['\Cloud_Py_API\FsListReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsList(\Cloud_Py_API\FsListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/FsList',
        $argument,
        ['\Cloud_Py_API\FsListReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsReadRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function FsRead(\Cloud_Py_API\FsReadRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/Cloud_Py_API.CloudPyApiCore/FsRead',
        $argument,
        ['\Cloud_Py_API\FsReadReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsCreateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsCreate(\Cloud_Py_API\FsCreateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/FsCreate',
        $argument,
        ['\Cloud_Py_API\FsReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ClientStreamingCall
     */
    public function FsWrite($metadata = [], $options = []) {
        return $this->_clientStreamRequest('/Cloud_Py_API.CloudPyApiCore/FsWrite',
        ['\Cloud_Py_API\FsReply','decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsDeleteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsDelete(\Cloud_Py_API\FsDeleteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/FsDelete',
        $argument,
        ['\Cloud_Py_API\FsReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\FsMoveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsMove(\Cloud_Py_API\FsMoveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/FsMove',
        $argument,
        ['\Cloud_Py_API\FsReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\DbSelectRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbSelect(\Cloud_Py_API\DbSelectRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/DbSelect',
        $argument,
        ['\Cloud_Py_API\DbSelectReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\DbCursorRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbCursor(\Cloud_Py_API\DbCursorRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/DbCursor',
        $argument,
        ['\Cloud_Py_API\DbCursorReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \Cloud_Py_API\DbExecRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbExec(\Cloud_Py_API\DbExecRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/Cloud_Py_API.CloudPyApiCore/DbExec',
        $argument,
        ['\Cloud_Py_API\DbExecReply', 'decode'],
        $metadata, $options);
    }

}
