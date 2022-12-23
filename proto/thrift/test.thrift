namespace php OCA.Cloud_Py_API.TProto
namespace py nc_py_api.TProto

enum logLvl {
    DEBUG = 0;
    INFO = 1;
    WARN = 2;
    ERROR = 3;
    FATAL = 4;
}

service TestService {
    i32 ping(1: logLvl logLvl),
    oneway void exit(1: i32 resultCode)
}
