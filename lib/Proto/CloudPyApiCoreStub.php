<?php
// GENERATED CODE -- DO NOT EDIT!

namespace OCA\Cloud_Py_API\Proto;

/**
 */
class CloudPyApiCoreStub {

    /**
     * @param \OCA\Cloud_Py_API\Proto\PBEmpty $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\TaskInitReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function TaskInit(
        \OCA\Cloud_Py_API\Proto\PBEmpty $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\TaskInitReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function TaskStatus(
        \OCA\Cloud_Py_API\Proto\TaskSetStatusRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\CheckDataRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function AppCheck(
        \OCA\Cloud_Py_API\Proto\CheckDataRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskExitRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function TaskExit(
        \OCA\Cloud_Py_API\Proto\TaskExitRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\TaskLogRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\PBEmpty for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function TaskLog(
        \OCA\Cloud_Py_API\Proto\TaskLogRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\PBEmpty {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\OccRequest $request client request
     * @param \Grpc\ServerCallWriter $writer write response data of \OCA\Cloud_Py_API\Proto\OccReply
     * @param \Grpc\ServerContext $context server request context
     * @return void
     */
    public function OccCall(
        \OCA\Cloud_Py_API\Proto\OccRequest $request,
        \Grpc\ServerCallWriter $writer,
        \Grpc\ServerContext $context
    ): void {
        $context->setStatus(\Grpc\Status::unimplemented());
        $writer->finish();
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsGetInfoRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsListReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsGetInfo(
        \OCA\Cloud_Py_API\Proto\FsGetInfoRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsListReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsListRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsListReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsList(
        \OCA\Cloud_Py_API\Proto\FsListRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsListReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsReadRequest $request client request
     * @param \Grpc\ServerCallWriter $writer write response data of \OCA\Cloud_Py_API\Proto\FsReadReply
     * @param \Grpc\ServerContext $context server request context
     * @return void
     */
    public function FsRead(
        \OCA\Cloud_Py_API\Proto\FsReadRequest $request,
        \Grpc\ServerCallWriter $writer,
        \Grpc\ServerContext $context
    ): void {
        $context->setStatus(\Grpc\Status::unimplemented());
        $writer->finish();
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsCreateRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsCreateReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsCreate(
        \OCA\Cloud_Py_API\Proto\FsCreateRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsCreateReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \Grpc\ServerCallReader $reader read client request data of \OCA\Cloud_Py_API\Proto\FsWriteRequest
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsWrite(
        \Grpc\ServerCallReader $reader,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsDeleteRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsDelete(
        \OCA\Cloud_Py_API\Proto\FsDeleteRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\FsMoveRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\FsMoveReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function FsMove(
        \OCA\Cloud_Py_API\Proto\FsMoveRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\FsMoveReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbSelectRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\DbSelectReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function DbSelect(
        \OCA\Cloud_Py_API\Proto\DbSelectRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\DbSelectReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbCursorRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\DbCursorReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function DbCursor(
        \OCA\Cloud_Py_API\Proto\DbCursorRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\DbCursorReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * @param \OCA\Cloud_Py_API\Proto\DbExecRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \OCA\Cloud_Py_API\Proto\DbExecReply for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function DbExec(
        \OCA\Cloud_Py_API\Proto\DbExecRequest $request,
        \Grpc\ServerContext $context
    ): ?\OCA\Cloud_Py_API\Proto\DbExecReply {
        $context->setStatus(\Grpc\Status::unimplemented());
        return null;
    }

    /**
     * Get the method descriptors of the service for server registration
     *
     * @return array of \Grpc\MethodDescriptor for the service methods
     */
    public final function getMethodDescriptors(): array
    {
        return [
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskInit' => new \Grpc\MethodDescriptor(
                $this,
                'TaskInit',
                '\OCA\Cloud_Py_API\Proto\PBEmpty',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskStatus' => new \Grpc\MethodDescriptor(
                $this,
                'TaskStatus',
                '\OCA\Cloud_Py_API\Proto\TaskSetStatusRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/AppCheck' => new \Grpc\MethodDescriptor(
                $this,
                'AppCheck',
                '\OCA\Cloud_Py_API\Proto\CheckDataRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskExit' => new \Grpc\MethodDescriptor(
                $this,
                'TaskExit',
                '\OCA\Cloud_Py_API\Proto\TaskExitRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/TaskLog' => new \Grpc\MethodDescriptor(
                $this,
                'TaskLog',
                '\OCA\Cloud_Py_API\Proto\TaskLogRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/OccCall' => new \Grpc\MethodDescriptor(
                $this,
                'OccCall',
                '\OCA\Cloud_Py_API\Proto\OccRequest',
                \Grpc\MethodDescriptor::SERVER_STREAMING_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsGetInfo' => new \Grpc\MethodDescriptor(
                $this,
                'FsGetInfo',
                '\OCA\Cloud_Py_API\Proto\FsGetInfoRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsList' => new \Grpc\MethodDescriptor(
                $this,
                'FsList',
                '\OCA\Cloud_Py_API\Proto\FsListRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsRead' => new \Grpc\MethodDescriptor(
                $this,
                'FsRead',
                '\OCA\Cloud_Py_API\Proto\FsReadRequest',
                \Grpc\MethodDescriptor::SERVER_STREAMING_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsCreate' => new \Grpc\MethodDescriptor(
                $this,
                'FsCreate',
                '\OCA\Cloud_Py_API\Proto\FsCreateRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsWrite' => new \Grpc\MethodDescriptor(
                $this,
                'FsWrite',
                '\OCA\Cloud_Py_API\Proto\FsWriteRequest',
                \Grpc\MethodDescriptor::CLIENT_STREAMING_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsDelete' => new \Grpc\MethodDescriptor(
                $this,
                'FsDelete',
                '\OCA\Cloud_Py_API\Proto\FsDeleteRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/FsMove' => new \Grpc\MethodDescriptor(
                $this,
                'FsMove',
                '\OCA\Cloud_Py_API\Proto\FsMoveRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbSelect' => new \Grpc\MethodDescriptor(
                $this,
                'DbSelect',
                '\OCA\Cloud_Py_API\Proto\DbSelectRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbCursor' => new \Grpc\MethodDescriptor(
                $this,
                'DbCursor',
                '\OCA\Cloud_Py_API\Proto\DbCursorRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
            '/OCA.Cloud_Py_API.Proto.CloudPyApiCore/DbExec' => new \Grpc\MethodDescriptor(
                $this,
                'DbExec',
                '\OCA\Cloud_Py_API\Proto\DbExecRequest',
                \Grpc\MethodDescriptor::UNARY_CALL
            ),
        ];
    }

}
