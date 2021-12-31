import sys


def print_err(*args, **kwargs):
    print(*args, file=sys.stderr, **kwargs)


def debug_msg(*args, **kwargs):
    print('DEBUG:', *args, file=sys.stderr, **kwargs)
