import nc_py_api as nc_api
import sqlalchemy
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy import Column, Integer, String, JSON


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


def ttt():
    aaa = nc_api.CloudApi()
    aaa.log(nc_api.LogLvl.DEBUG, '111', '222...')
    bbb = aaa.db.create_engine()
    with bbb.connect() as conn:
        result = conn.execution_options(stream_results=True).execute(Task.__table__.select())
        chunk = result.fetchmany(1)
        aaa.log(nc_api.LogLvl.DEBUG, '111', str(chunk))
    exit()
    engine = sqlalchemy.create_engine("postgresql+pg8000://admin:12345@nc-ubnt-min.dnepr99/nextcloud",
                                      echo=True, future=True)
    with engine.connect() as conn:
        result = conn.execution_options(stream_results=True).execute(Task.__table__.select())
        chunk = result.fetchmany(2)

    engine = sqlalchemy.create_engine("mysql+pymysql://admin:12345@nc-deb-min.dnepr99/nextcloud",
                                      echo=True, future=True)
    with engine.connect() as conn:
        result = conn.execution_options(stream_results=True).execute(Task.__table__.select())
        chunk = result.fetchmany(2)

        # with Session(conn) as sess:
        #     aaa = sess.query(Task).limit(1)
        #     result = conn.execute(sqlalchemy.text("select 'hello world'"))
        #     print(result.all())
    exit(0)

    #  [
    #  (2, ['191'],
    #  {'user': {'mask': [], 'fileid': []}, 'admin': {'mask': [], 'fileid': []}},
    #  {'hashing_algorithm': 'dhash', 'similarity_threshold': 90, 'hash_size': 16, 'target_mtype': 0},
    #  91, 1639584865, 1639584866, '', 0),
    #  (1, ['190'],
    #  {'user': {'mask': [], 'fileid': []}, 'admin': {'mask': [], 'fileid': []}},
    #  {'hashing_algorithm': 'dhash', 'similarity_threshold': 90, 'hash_size': 16, 'target_mtype': 0},
    #  91, 1639080237, 1639080238, '', 0)
    #  ]
    #  [
    #  (11, ['5824'],
    #  {'user': {'mask': [], 'fileid': []}, 'admin': {'mask': [], 'fileid': []}},
    #  {'hash_size': 16, 'target_mtype': 0, 'hashing_algorithm': 'dhash', 'similarity_threshold': 90},
    #  74, 1639480937, 1639480937, '', 0),
    #  (12, ['5829'],
    #  {'user': {'mask': [], 'fileid': []}, 'admin': {'mask': [], 'fileid': []}},
    #  {'hash_size': 16, 'target_mtype': 0, 'hashing_algorithm': 'dhash', 'similarity_threshold': 90},
    #  91, 1639401096, 1639401096, '', 0)
    #  ]


if __name__ == '__main__':
    ttt()
