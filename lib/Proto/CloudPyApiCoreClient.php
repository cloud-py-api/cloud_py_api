<?php
// GENERATED CODE -- DO NOT EDIT!

namespace OCA\Cloud_Py_API\Proto;

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
     * @param \OCA\Cloud_Py_API\Proto\PBEmpty $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskInit(\OCA\Cloud_Py_API\Proto\PBEmpty $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskInit',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\TaskInitReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskStatus(\OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskStatus',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\CheckDataRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function AppCheck(\OCA\Cloud_Py_API\Proto\CheckDataRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/AppCheck',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskExitRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskExit(\OCA\Cloud_Py_API\Proto\TaskExitRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskExit',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskLogRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function TaskLog(\OCA\Cloud_Py_API\Proto\TaskLogRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskLog',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\PBEmpty', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\OccRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function OccCall(\OCA\Cloud_Py_API\Proto\OccRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/OccCall',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\OccReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsGetInfoRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsGetInfo(\OCA\Cloud_Py_API\Proto\FsGetInfoRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsGetInfo',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsListReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsListRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsList(\OCA\Cloud_Py_API\Proto\FsListRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsList',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsListReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsReadRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function FsRead(\OCA\Cloud_Py_API\Proto\FsReadRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsRead',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsReadReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsCreateRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsCreate(\OCA\Cloud_Py_API\Proto\FsCreateRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsCreate',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsCreateReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ClientStreamingCall
     */
    public function FsWrite($metadata = [], $options = []) {
        return $this->_clientStreamRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsWrite',
        ['\OCA\Cloud_Py_API\Proto\FsReply','decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsDeleteRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsDelete(\OCA\Cloud_Py_API\Proto\FsDeleteRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsDelete',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsMoveRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function FsMove(\OCA\Cloud_Py_API\Proto\FsMoveRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsMove',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\FsMoveReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbSelectRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbSelect(\OCA\Cloud_Py_API\Proto\DbSelectRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbSelect',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\DbSelectReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbCursorRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbCursor(\OCA\Cloud_Py_API\Proto\DbCursorRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbCursor',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\DbCursorReply', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbExecRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function DbExec(\OCA\Cloud_Py_API\Proto\DbExecRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbExec',
        $argument,
        ['\OCA\Cloud_Py_API\Proto\DbExecReply', 'decode'],
        $metadata, $options);
    }

}
