from io import BytesIO

import nc_py_api as nc_api


def func_fs_list_info():
    ca = nc_api.CloudApi()
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'listing root...')
    fs_objects = ca.fs.list()
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', f'{len(fs_objects)} objects in user root directory')
    for fs_object in fs_objects:
        fs_same_object = ca.fs.info(fs_object)
        if fs_same_object is None:
            return 'BAD'
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
    res, new_id = ca.fs.create('TEST_CREATING_DIR', is_dir=True)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating sub dir...')
    res, new_id2 = ca.fs.create('TEST_CREATING_SUBDIR', is_dir=True, parent_dir=new_id)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating file at root...')
    res, new_id3 = ca.fs.create('TEST_CREATING_NO_CONTENT.txt', is_dir=False)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'creating file at subdir...')
    res, new_id4 = ca.fs.create('TEST_CREATING_FILE_CONTENT.txt', is_dir=False, content=b'Hello world!',
                                parent_dir=new_id2)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting file at subdir...')
    if ca.fs.delete(fs_id=new_id4) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting sub dir...')
    if ca.fs.delete(fs_id=new_id2) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting file at root...')
    if ca.fs.delete(fs_id=new_id3) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log(nc_api.LogLvl.DEBUG, 'fs_example', 'deleting dir at root...')
    if ca.fs.delete(fs_id=new_id) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    return 'GOOD'


def func_fs_read_write():
    ca = nc_api.CloudApi()
    test_content1 = b'This content will be rewritten by WriteFile'
    res, new_id = ca.fs.create('rw_test.txt', False, content=test_content1)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    rw_test_content = BytesIO()
    res = ca.fs.read_file(new_id, rw_test_content)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    if rw_test_content.read() != test_content1:
        return 'BAD'
    test_content2 = BytesIO(b'01234567890')
    res = ca.fs.write_file(new_id, test_content2)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'

    def read_file_test(offset, size) -> bool:
        rw_test_content.seek(0)
        rw_test_content.truncate()
        if ca.fs.read_file(new_id, rw_test_content, offset=offset, bytes_to_read=size) != nc_api.FsResultCode.NO_ERROR:
            return False
        test_content2.seek(offset)
        if test_content2.read(size) != rw_test_content.read(size):
            return False
        return True

    if not read_file_test(0, 0):
        return 'BAD'
    for i in range(test_content2.getbuffer().nbytes):
        if not read_file_test(i, test_content2.getbuffer().nbytes - i):
            return 'BAD'
    ca.fs.delete(fs_id=new_id)
    return 'GOOD'


def func_fs_move_copy():
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    pass


def func_fs_invalid():
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    pass


def fs_complex_test():
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    pass
