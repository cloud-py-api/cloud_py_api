import nc_py_api.cloud_api as cpa


def func_hello_world():
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.INFO, 'hello_world', 'HelloWorld')
    return 'OK'


def func_hello_world_fixed_two_args(arg1, arg2):
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.INFO, 'hello_world_fixed_two_args', f'{arg1 + arg2}')
    return arg1 + arg2


def func_hello_world_args(*arguments):
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.INFO, 'hello_world_args', f'{arguments}')
    return f'get {str(len(arguments))} argument(s)'


def func_no_result():
    pass


def func_exception():
    raise ValueError('TEST')
