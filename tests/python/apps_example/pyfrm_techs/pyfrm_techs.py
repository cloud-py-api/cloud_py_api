import nc_py_api as nc_api
import numpy


def get_image_difference(path_to_img1, path_to_img2):
    ca = nc_api.CloudApi()
    ca.log(nc_api.LogLvl.DEBUG, 'image_difference', f'Comparing {path_to_img1} to {path_to_img2}')
    difference = 100
    return f'Diff = {difference}'
