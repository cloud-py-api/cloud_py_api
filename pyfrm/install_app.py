# how_to_run: python3 pyfrm/install.py path_to_framework_app_data --check > path_to_output_install.log
# working dir: does not matter
# python3: can be local that shipped with framework or global if admin changed this for some reason(like in MediaDC)
# input: path_to_framework_app_data
#        optional flag --check, when specified install acts like check() function in MediaDC.
# output: file in JSON format, printed to standard output. '>' if need to print to file.
# exit_code: 0 - 2   --->   0 - success, 1 - error, 2 - crash.
# !!! This is not an indication of successful or failed install. !!! Always look at output.Installed if exit code != 2.
#
# output file struct:
# [Installed:True,
# Logs:[{lvl:0-4,msg:TEXT,help_code:number},]
# ]
#
# Brief algo install:
# 1. pip install grpc, gproto, nc_py_api
# 2. pip install pipdeptree
# 3. pip install numpy / scipy / pillow
# 4. success

# If python is global we must check pip and install local pip if needed.



# cd /var/www/nextcloud/data/appdata_ocs30ydgi7y8/cloud_py_api
# rm -rf local && rm -rf local python &&
# wget https://github.com/indygreg/python-build-standalone/releases/download/20211017/cpython-3.9.7-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst
# unzstd cpython-3.9.7-x86_64-unknown-linux-gnu-lto-20211017T1616.tar.zst
# tar -xvf cpython-3.9.7-x86_64-unknown-linux-gnu-lto-20211017T1616.tar
# chown -R www-data:www-data python