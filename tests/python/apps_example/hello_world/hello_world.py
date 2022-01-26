import nc_py_api as nc_api


def func_hello_world():
    ca = nc_api.CloudApi()
    ca.log.info('HelloWorld!')
    return 'OK'


def func_hello_world_fixed_two_args(arg1, arg2):
    ca = nc_api.CloudApi()
    ca.log.info(f'{arg1 + arg2}')
    return arg1 + arg2


def func_hello_world_args(*arguments):
    ca = nc_api.CloudApi()
    ca.log.info(f'{arguments}')
    return f'get {str(len(arguments))} argument(s)'


def func_no_result():
    pass


def func_exception():
    raise ValueError('TEST')


def occ_call():
    ca = nc_api.CloudApi()
    _result, _data = ca.occ_call('--version')
    return f'{_result}:{_data}'


def occ_call_fail():
    ca = nc_api.CloudApi()
    _result, _data = ca.occ_call('--version', '--fail')
    return f'{_result}:{_data}'
