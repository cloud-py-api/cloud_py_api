import nc_py_api.cloud_api as cpa


def func_hello_world():
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.INFO.value, 'hello_world', 'HelloWorld')
    return 'OK'


# TODO: write successful test
def func_hello_world_args(*arums):
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.INFO.value, 'hello_world_args', *arums)
    return f'logged {str(len(arums))} arguments'


# TODO: write test for no result
def func_no_result(*_arums):
    pass


def func_exception():
    raise ValueError('TEST')
