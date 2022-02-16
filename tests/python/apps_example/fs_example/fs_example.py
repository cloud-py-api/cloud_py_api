from io import BytesIO

try:
    import pyfrm.nc_py_api as nc_api
except Exception as e:
    print(str(e))
    import nc_py_api as nc_api


def func_fs_list_info():
    ca = nc_api.CloudApi()
    ca.log.info('listing root...')
    fs_objects = ca.fs.list()
    ca.log.info(f'{len(fs_objects)} objects in user root directory')
    for fs_object in fs_objects:
        fs_same_object = ca.fs.info(fs_object['id'])
        if fs_same_object is None:
            return 'BAD'
        if fs_object != fs_same_object:
            return 'BAD'
        fs_same_object = ca.fs.info(fs_object)
        if fs_same_object is None:
            return 'BAD'
        if fs_object != fs_same_object:
            return 'BAD'
        ca.log.debug(f'\n\t{str(fs_object)}')
    return 'GOOD'


def func_fs_list_info_oop():
    ca = nc_api.CloudApi()
    ca.log.info('fs_example', 'listing root...')
    fs_objects = nc_api.FsObj().list()
    ca.log.info(f'{len(fs_objects)} objects in user root directory')
    for fs_object in fs_objects:
        ca.log.info('fs_example', f'\n\t{str(fs_object)}')
    return 'GOOD'


def func_fs_create_delete():
    ca = nc_api.CloudApi()
    ca.log.info('creating dir at root...')
    res, new_id = ca.fs.create('TEST_CREATING_DIR', is_dir=True)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('creating sub dir...')
    res, new_id2 = ca.fs.create('TEST_CREATING_SUBDIR', is_dir=True, parent_dir=new_id)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('creating file at root...')
    res, new_id3 = ca.fs.create('TEST_CREATING_NO_CONTENT.txt', is_dir=False)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('creating file at subdir...')
    res, new_id4 = ca.fs.create('TEST_CREATING_FILE_CONTENT.txt', is_dir=False, content=b'Hello world!',
                                parent_dir=new_id2)
    if res != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('deleting file at subdir...')
    if ca.fs.delete(fs_obj=new_id4) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('deleting sub dir...')
    if ca.fs.delete(fs_obj=new_id2) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('deleting file at root...')
    if ca.fs.delete(fs_obj=new_id3) != nc_api.FsResultCode.NO_ERROR:
        return 'BAD'
    ca.log.info('deleting dir at root...')
    if ca.fs.delete(fs_obj=new_id) != nc_api.FsResultCode.NO_ERROR:
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
    ca.fs.delete(fs_obj=new_id)
    return 'GOOD'


def func_fs_move_copy(user_id, file_id, target_path, copy):
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    ca = nc_api.CloudApi()
    fs_obj = nc_api.FsObj(user_id, file_id)
    code, new_id = ca.fs.move(fs_obj, target_path, bool(copy))
    return f'fs.move returned: {str(nc_api.FsResultCode(code))} - {str(new_id)}'


def func_fs_invalid():
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    ca = nc_api.CloudApi()
    _invalid_id = {'user': '', 'file': 999999}
    ca.log.info('Listing(fs.list) invalid id...')
    _list = ca.fs.list(_invalid_id)
    ca.log.info(str(_list))
    ca.log.info('Listing(FsObj.list) invalid id...')
    _list = nc_api.FsObj(_invalid_id).list()
    ca.log.info(str(_list))
    ca.log.info('Info(fs.info) invalid id...')
    _info = ca.fs.info(_invalid_id)
    ca.log.info(str(_info))
    ca.log.info('Info(FsObj.info) invalid id...')
    try:
        _info = nc_api.FsObj(_invalid_id, load=True)
        ca.log.info('ERROR!!!')
    except nc_api.FsNotFound:
        ca.log.info('GOOD')


def fs_complex_test():
    # This function will fail without cloud_py_api installed in NC instance(_srv_example doesnt emulate this)
    ca = nc_api.CloudApi()

    def print_file_info(o: nc_api.FsObj):
        ca.log.info(f'file:{o.name}[{o.internal_path}][{o.abs_path}], size={o.size}')

    def print_dir_info(o: nc_api.FsObj):
        ca.log.info(f'dir:{o.name}[{o.internal_path}][{o.abs_path}]')

    def list_dir(dir_obj: nc_api.FsObj):
        for dirs_objs in dir_obj.list():
            if dirs_objs.is_dir:
                print_dir_info(dirs_objs)
                list_dir(dirs_objs)
            else:
                print_file_info(dirs_objs)

    ca.log.info('Listing all fs')
    root_dir_objs = nc_api.FsObj().list()
    for root_obj in root_dir_objs:
        ca.log.info(str(root_obj))
        if root_obj.is_dir:
            list_dir(root_obj)
        else:
            print_file_info(root_obj)
    _dir_name = 'Lll05678'
    ca.log.info(f'Creating new folder `{_dir_name}` at root')

    ca.log.info(f'Copy all files from root to `{_dir_name}` folder')

    ca.log.info(f'Deleting `{_dir_name}` folder')
    return 'GOOD'
