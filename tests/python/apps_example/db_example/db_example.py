import logging

import nc_py_api as nc_api
from sqlalchemy.ext.automap import automap_base
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, String, JSON, MetaData
from sqlalchemy.orm import Session


def list_tables():
    cc = nc_api.CloudApi()
    base = automap_base()
    cc.log.info('Creating engine')
    engine = cc.db.create_engine()
    base.prepare(engine, reflect=True)
    for table in base.metadata.sorted_tables:
        cc.log.info(f"Table {table.name} with columns [{' '.join(str(column.name) for column in table.columns)}]")


def list_mounts_table_orm():
    cc = nc_api.CloudApi()
    cc.log.info('Creating engine')
    engine = cc.db.create_engine()
    metadata = MetaData()
    table_name = cc.db.table_prefix + 'mounts'
    metadata.reflect(engine, only=[table_name])
    base = automap_base(metadata=metadata)
    base.prepare()
    mounts_class = base.classes[table_name]
    with Session(engine) as sess:
        mounts = sess.query(mounts_class).all()
        cc.log.info('ID storage_id root_id user_id mount_point mount_id')
        for mount in mounts:
            cc.log.info(
                f'{mount.id} {mount.storage_id} {mount.root_id} {mount.user_id} {mount.mount_point} {mount.mount_id}')


class MyDbCustomLogHandler(logging.Handler):
    this_module_logger: logging.Logger

    def __init__(self, logger):
        super().__init__()
        self.this_module_logger = logger

    def emit(self, record):
        self.this_module_logger.callHandlers(record)       # In this example we just call default CloudLogHandler


class Storages(declarative_base()):
    __tablename__ = "*PREFIX*storages"
    numeric_id = Column(Integer, primary_key=True)
    id = Column(String(length=64))
    available = Column(Integer)
    last_checked = Column(Integer)


def list_storages_with_logs():
    cc = nc_api.CloudApi()
    for logger_name in ('sqlalchemy.orm', 'sqlalchemy.engine', 'sqlalchemy.pool'):
        __logger = logging.getLogger(logger_name)
        __logger.setLevel(logging.DEBUG)
        __logger.addHandler(MyDbCustomLogHandler(logging.getLogger('db_example')))
    engine = cc.db.create_engine()
    with engine.connect() as e_connect:
        result = e_connect.execution_options(stream_results=True).execute(Storages.__table__.select())
        storages = result.fetchall()
        for numeric_id, id, available, last_checked in storages:
            cc.log.info(f'num_id={numeric_id}, id={id}, available={available}, last_checked={last_checked}')


class MyDbCustomRawLogHandler(logging.Handler):
    cloud: nc_api.CloudApi

    def __init__(self, cloud: nc_api.CloudApi):
        super().__init__()
        self.cloud = cloud

    def filter(self, record):
        self.format(record)             # Do any formatting you want, like usual.
        my_formatted_message = 'YX!-->' + record.message
        self.cloud.to_log(nc_api.cloud_api.LogLvl.INFO, "db_example", my_formatted_message)
        return False                    # We don't remove parent log handler, return False to not log twice.


class Task(declarative_base()):
    __tablename__ = "*PREFIX*mediadc_tasks"
    id = Column(Integer, primary_key=True)
    target_directory_ids = Column(JSON(none_as_null=True))
    exclude_list = Column(JSON(none_as_null=True))
    collector_settings = Column(JSON(none_as_null=True))
    files_scanned = Column(Integer)
    updated_time = Column(Integer())
    finished_time = Column(Integer())
    errors = Column(String(length=1024))
    py_pid = Column(Integer)


def mediadc_list_tasks_with_custom_log_sender():
    cc = nc_api.CloudApi()
    logging.getLogger('sqlalchemy').setLevel(logging.INFO)
    logging.getLogger('sqlalchemy').addHandler(MyDbCustomRawLogHandler(cc))
    raw_log_handler = MyDbCustomRawLogHandler(cc)
    logging.getLogger('db_example').addHandler(raw_log_handler)        # This is not recommended way, but possible.
    engine = cc.db.create_engine()
    with Session(engine) as sess:
        tasks = sess.query(Task).all()
        for task in tasks:
            cc.log.info(f'{task.id}, {task.target_directory_ids}, {task.collector_settings}')
    logging.getLogger('db_example').removeHandler(raw_log_handler)     # Remove our custom log handler.
