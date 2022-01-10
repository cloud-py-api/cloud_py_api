from io import BytesIO

import nc_py_api as nc_api


def func_fs_list_info():
    ca = nc_api.CloudApi()
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'listing root...')
    fs_objects = ca.dir_list()
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', f'{len(fs_objects)} objects in user root directory')
    for fs_object in fs_objects:
        fs_same_object = ca.file_info(fs_object)
        if fs_object.is_dir != fs_same_object.is_dir:
            return 'BAD'
        if fs_object.mimetype != fs_same_object.mimetype:
            return 'BAD'
        if fs_object.name != fs_same_object.name:
            return 'BAD'
        if fs_object.internal_path != fs_same_object.internal_path:
            return 'BAD'
        if fs_object.abs_path != fs_same_object.abs_path:
            return 'BAD'
        if fs_object.permissions != fs_same_object.permissions:
            return 'BAD'
        if fs_object.owner_name != fs_same_object.owner_name:
            return 'BAD'
        if fs_object.storage_id != fs_same_object.storage_id:
            return 'BAD'
        if fs_object.mount_id != fs_same_object.mount_id:
            return 'BAD'
        ca.log(nc_api.LogLvl.DEBUG, 'fs_example', f'\n\t{str(fs_object)}')
    return 'GOOD'


def func_fs_create_delete():
    ca = nc_api.CloudApi()
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating dir at root...')
    if ca.create_file('TEST_CREATING_DIR', is_dir=True) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating sub dir...')
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating file at root...')
    if ca.create_file('TEST_CREATING_FILE.txt', is_dir=False) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating file at subdir...')
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting file at subdir...')
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting sub dir...')
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting file at root...')
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting dir at root...')


def func_fs_read_write():
    ca = nc_api.CloudApi()
    ca.create_file('111.txt', False, content=b'123')
    # ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'writing to file 1.txt')
    # test_debug = nc_api.FsObjId(user_id='111', file_id=2)
    # test_obj = BytesIO()
    # ca.read_file(test_debug, test_obj)
    # aaa = test_obj.read()
    # return aaa.decode()
    # ca = nc_api.CloudApi()
    # ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'writing to file 1.txt')
    # test_debug = nc_api.FsObjId(user_id='111', file_id=2)
    # ca.write_file(test_debug, BytesIO(b'000012345678'))


def func_fs_move_copy():
    pass


def func_fs_invalid():
    pass


def fs_full_test():
    pass
