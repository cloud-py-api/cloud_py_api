"""
Functions related to user management.
"""
from os import environ
from typing import Optional, TypedDict

from .db_requests import get_user, get_users


class UserInfo(TypedDict):
    uid: str
    display_name: Optional[str]
    password: str
    uid_lower: str


USER_ID = environ.get("USER_ID", "")


def current_user_info() -> Optional[UserInfo]:
    raw_result = get_user(USER_ID.lower())
    return db_record_to_user_info(raw_result) if raw_result else None


def user_info(uid: str) -> Optional[UserInfo]:
    raw_result = get_user(uid.lower())
    if not raw_result:
        return None
    return db_record_to_user_info(raw_result)


def users_info() -> list[UserInfo]:
    return [db_record_to_user_info(i) for i in get_users()]


def db_record_to_user_info(user_record: dict) -> UserInfo:
    return {
        "uid": user_record["uid"],
        "display_name": user_record["displayname"],
        "password": user_record["password"],
        "uid_lower": user_record["uid_lower"],
    }
