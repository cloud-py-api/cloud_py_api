from .db_requests import get_mimetype_id

DIR = get_mimetype_id("httpd/unix-directory")
AUDIO = get_mimetype_id("audio")
IMAGE = get_mimetype_id("image")
TEXT = get_mimetype_id("text")
VIDEO = get_mimetype_id("video")
