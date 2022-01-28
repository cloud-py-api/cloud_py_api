PHP Cloud_Py_API Core API
=========================

.. php:class:: OCA\Cloud_Py_API\Framework\Core

  Cloud_Py_API Framework Core API class

  .. php:method:: runBgGrpcServer(array $params = []): int

      Run non-blocking GRPC server

      :param array $params: hostname, port, userid, appname, handler,
                              modname, modpath, funcname, args
      :returns: int - background GRPC server PID or -1 on failure

  .. php:method:: createServer(array $params = []): RpcServer

      Create GRPC server

      :param array $params: hostname and port
      :returns: \\Grpc\\RpcServer

  .. php:method:: createClient(array $params = []): CloudPyApiCoreClient

      Create GRPC client

      :param array $params: hostname and port
      :returns: OCA\\Cloud_Py_API\\Proto\\CloudPyApiCoreClient

  .. php:method:: TaskInit($client): CloudPyApiCoreClient

      Send TaskInit request from given client

      :param OCA\\Cloud_Py_API\\Proto\\CloudPyApiCoreClient $client: GRPC client
      :returns: array ['response' => OCA\\Cloud_Py_API\\Proto\\TaskInitReply, 'status' => ['metadata', 'code', 'details']]

  .. php:method:: TaskStatus($client, $params = []): void

      Send TaskLog request

      :param OCA\\Cloud_Py_API\\Proto\\CloudPyApiCoreClient $params: GRPC client
      :returns: void

  .. php:method:: TaskLog($client, $params = []): void

      Send TaskLog request

      :param OCA\\Cloud_Py_API\\Proto\\CloudPyApiCoreClient $client: GRPC client
      :param array $params: ['logLvl', 'module', 'content']
      :returns: void

  .. php:method:: TaskExit($client, $params = []): void

      Send TaskExit request with passing $result to initiator callback and closing server process

      :param OCA\\Cloud_Py_API\\Proto\\CloudPyApiCoreClient $client: GRPC client
      :param array $params: ['result' => mixed]
      :returns: void
