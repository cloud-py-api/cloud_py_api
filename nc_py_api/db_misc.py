from datetime import datetime

from .config import CONFIG


class Tables:
    @property
    def storages(self):
        return CONFIG["dbtprefix"] + "storages"

    @property
    def mounts(self):
        return CONFIG["dbtprefix"] + "mounts"

    @property
    def ext_mounts(self) -> str:
        return CONFIG["dbtprefix"] + "external_mounts"

    @property
    def file_cache(self) -> str:
        return CONFIG["dbtprefix"] + "filecache"

    @property
    def mimetypes(self) -> str:
        return CONFIG["dbtprefix"] + "mimetypes"

    @property
    def settings(self) -> str:
        return CONFIG["dbtprefix"] + "cloud_py_api_settings"


TABLES = Tables()


def get_time() -> int:
    return int(datetime.now().timestamp())
