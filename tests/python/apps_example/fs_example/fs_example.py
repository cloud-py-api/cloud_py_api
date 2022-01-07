import nc_py_api.cloud_api as cpa


def func_fs_list_info():
    ca = cpa.CloudApi()
    ca.log(cpa.LogLvl.DEBUG, 'fs_example', 'listing root...')
    fs_objects = ca.dir_list()
    ca.log(cpa.LogLvl.DEBUG, 'fs_example', f'{len(fs_objects)} objects in user root directory')
    for fs_object in fs_objects:
        fs_same_object = ca.file_info(fs_object.id)
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
        ca.log(cpa.LogLvl.DEBUG, 'fs_example', str(fs_object))
    return 'GOOD'


def func_fs_create_delete(arg1, arg2):
    pass


def func_fs_read_write(*arguments):
    pass


def func_fs_move_copy():
    pass


def func_fs_invalid():
    pass


def fs_full_test():
    pass
